<?php
$GLOBALS = array(
    'id_post' => 0
);
/**
 * Vlastni trida spravujici databazi.
 */
class MyDatabase {


    /** @var PDO $pdo  PDO objekt pro praci s databazi. */
    private $pdo;

    /** @var MySession $mySession  Vlastni objekt pro spravu session. */
    private $mySession;
    /** @var string $userSessionKey  Klicem pro data uzivatele, ktera jsou ulozena v session. */
    private $userSessionKey = "current_user_id";


    /**
     * MyDatabase constructor.
     * Inicializace pripojeni k databazi a pokud ma byt spravovano prihlaseni uzivatele,
     * tak i vlastni objekt pro spravu session.
     * Pozn.: v samostatne praci by sprava prihlaseni uzivatele mela byt v samostatne tride.
     * Pozn.2: take je mozne do samostatne tridy vytahnout konkretni funkce pro praci s databazi.
     */
    public function __construct(){
        // inicialilzuju pripojeni k databazi - informace beru ze settings
        $this->pdo = new PDO("mysql:host=".DB_SERVER.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $this->pdo->exec("set names utf8");
        // inicializuju objekt pro praci se session - pouzito pro spravu prihlaseni uzivatele
        // pozn.: v samostatne praci vytvorte pro spravu prihlaseni uzivatele samostatnou tridu.
        require_once(DIRECTORY_MODELS."/MySessions.class.php");
        $this->mySession = new MySession();
    }


    ///////////////////  Obecne funkce  ////////////////////////////////////////////

    /**
     *  Provede dotaz a bud vrati ziskana data, nebo pri chybe ji vypise a vrati null.
     *
     *  @param string $dotaz        SQL dotaz.
     *  @return PDOStatement|null    Vysledek dotazu.
     */
    private function executeQuery(string $dotaz){
        // vykonam dotaz
        $res = $this->pdo->query($dotaz);
        /*$statement = $this -> pdo -> prepare("SELECT * FROM ".TABLE_USER." WHERE id = ?");
        $statement -> execute([1]);*/
        // pokud neni false, tak vratim vysledek, jinak null
        if ($res) {
            // neni false
            return $res;
        } else {
            // je false - vypisu prislusnou chybu a vratim null
            $error = $this->pdo->errorInfo();
            echo $error[2];
            return null;
        }
    }

    /**
     * Jednoduche cteni z prislusne DB tabulky.
     *
     * @param string $tableName         Nazev tabulky.
     * @param string $whereStatement    Pripadne omezeni na ziskani radek tabulky. Default "".
     * @param string $orderByStatement  Pripadne razeni ziskanych radek tabulky. Default "".
     * @return array                    Vraci pole ziskanych radek tabulky.
     */

    /*public function selectFromTable(string $tableName, string $whereStatement = "", string $orderByStatement = ""):array {
        // slozim dotaz
        $q = "SELECT * FROM ".$tableName
            .(($whereStatement == "") ? "" : " WHERE $whereStatement")
            .(($orderByStatement == "") ? "" : " ORDER BY $orderByStatement");

        // provedu ho a vratim vysledek
        $obj = $this->executeQuery($q);
        // pokud je null, tak vratim prazdne pole
        if($obj == null){
            return [];
        }
        return $obj->fetchAll();
    }*/

    public function selectFromTable(string $tableName, array $whereStatement, string $orderByStatement = ""):array {
        //rozdeleni prichozich hodnot na stringy a promenne
        $i = 0;
        $j = 0;
        $str = "";
        $var = "";
        $vals = ":val1";
        foreach ($whereStatement[0] as $s){
            $str.="$s";
            $i++;
        }
        $j=1;
        foreach ($whereStatement[1] as $v){
            $var.="$v";
            if($j!=1)$vals.=", :val$j";
            $j++;
        }
        echo $str." | ".$vals." | ".$var."<br>";
        /*$stmt = $conn->prepare("INSERT INTO MyGuests (firstname, lastname, email) VALUES (:firstname, :lastname, :email)");
         $stmt->bindParam(':firstname', $firstname); //POZOR: navážu proměnnou!
         $stmt->bindParam(':lastname', $lastname);
         $stmt->bindParam(':email', $email);*/
        // slozim dotaz a osetrim proti sql injection
        $q = $this->pdo->prepare("SELECT * FROM ".$tableName." WHERE ($str) VALUES ($vals)".(($orderByStatement == "") ? "" : " ORDER BY $orderByStatement"));
        $query="SELECT * FROM ".$tableName." WHERE ($str) VALUES ($vals)";
        echo $query;
        for($k = 1; $k<$j; $k++){
            $x = $whereStatement[1][$k-1];
            echo "<br>bindParam(\":val$k\",$x)";
            $q->bindParam(":val$k",$x);
        }
        $q->execute();
        $obj = $q->fetchAll();
        return $obj;
    }

    /**
     * Jednoduchy zapis do prislusne tabulky.
     *
     * @param string $tableName         Nazev tabulky.
     * @param string $insertStatement   Text s nazvy sloupcu pro insert.
     * @param string $insertValues      Text s hodnotami pro prislusne sloupce.
     * @return bool                     Vlozeno v poradku?
     */
    public function insertIntoTable(string $tableName, string $insertStatement, string $insertValues):bool {
        // slozim dotaz
        $q = "INSERT INTO $tableName($insertStatement) VALUES ($insertValues)";
        //echo $q;
        // provedu ho a vratim uspesnost vlozeni
        $obj = $this->executeQuery($q);
        if($obj == null){
            return false;
        } else {
            return true;
        }
    }

    /**
     * Jednoducha uprava radku databazove tabulky.
     *
     * @param string $tableName                     Nazev tabulky.
     * @param string $updateStatementWithValues     Cela cast updatu s hodnotami.
     * @param string $whereStatement                Cela cast pro WHERE.
     * @return bool                                 Upraveno v poradku?
     */
    public function updateInTable(string $tableName, string $updateStatementWithValues, string $whereStatement):bool {
        // slozim dotaz
        $q = "UPDATE $tableName SET $updateStatementWithValues WHERE $whereStatement";
        // provedu ho a vratim vysledek
        $obj = $this->executeQuery($q);
        if($obj == null){
            return false;
        } else {
            return true;
        }
    }

    /**
     * Dle zadane podminky maze radky v prislusne tabulce.
     *
     * @param string $tableName         Nazev tabulky.
     * @param string $whereStatement    Podminka mazani.
     */
    public function deleteFromTable(string $tableName, string $whereStatement){
        // slozim dotaz
        $q = "DELETE FROM $tableName WHERE $whereStatement";
        // provedu ho a vratim vysledek
        $obj = $this->executeQuery($q);
        if($obj == null){
            return false;
        } else {
            return true;
        }
    }

    ///////////////////  KONEC: Obecne funkce  ////////////////////////////////////////////

    ///////////////////  Konkretni funkce  ////////////////////////////////////////////

    /**
     * Ziskani zaznamu vsech uzivatelu aplikace.
     *
     * @return array    Pole se vsemi uzivateli.
     */
    public function getAllUsers(){
        // ziskam vsechny uzivatele z DB razene dle ID a vratim je
        $users = $this->selectFromTable(TABLE_UZIVATEL, "", "id_uzivatel");
        return $users;
    }

    /**
     * Ziskani zaznamu vsech prav aplikace.
     *
     * @return array    Pole se vsemi pravy.
     */
    public function getAllRights(){
        // ziskam vsechny uzivatele z DB razene dle ID a vratim je
        $users = $this->selectFromTable(TABLE_PRAVO, "", "id_pravo ASC, nazev ASC");
        return $users;
    }

    /**
     * Vytvoreni noveho uzivatele v databazi.
     *
     * @param string $login     Login.
     * @param string $jmeno     Jmeno.
     * @param string $email     E-mail.
     * @param int $idPravo      Je cizim klicem do tabulky s pravy.
     * @return bool             Vlozen v poradku?
     */
    public function addNewUser(string $login, string $heslo, string $jmeno, int $idPravo = 1){
        // hlavicka pro vlozeni do tabulky uzivatelu
        $insertStatement = "login, heslo, jmeno, id_pravo";
        // hodnoty pro vlozeni do tabulky uzivatelu
        $insertValues = "'$login', '$heslo', '$jmeno', $idPravo";
        // provedu dotaz a vratim jeho vysledek
        return $this->insertIntoTable(TABLE_UZIVATEL, $insertStatement, $insertValues);
    }

    /**
     * Uprava konkretniho uzivatele v databazi.
     *
     * @param int $idUzivatel   ID upravovaneho uzivatele.
     * @param string $login     Login.
     * @param string $heslo     Heslo.
     * @param string $jmeno     Jmeno.
     * @param int $idPravo      ID prava.
     * @return bool             Bylo upraveno?
     */
    public function updateUser(int $idUzivatel, string $login, string $heslo, string $jmeno, int $idPravo){
        // slozim cast s hodnotami
        $updateStatementWithValues = "login='$login', heslo='$heslo', jmeno='$jmeno', id_pravo='$idPravo'";
        // podminka
        $whereStatement = "id_uzivatel=$idUzivatel";
        // provedu update
        return $this->updateInTable(TABLE_UZIVATEL, $updateStatementWithValues, $whereStatement);
    }

    ///////////////////  KONEC: Konkretni funkce  ////////////////////////////////////////////

    ///////////////////  Sprava prihlaseni uzivatele  ////////////////////////////////////////

    /**
     * Overi, zda muse byt uzivatel prihlasen a pripadne ho prihlasi.
     *
     * @param string $login     Login uzivatele.
     * @param string $heslo     Heslo uzivatele.
     * @return bool             Byl prihlasen?
     */
    public function userLogin(string $login, string $heslo){
        // ziskam uzivatele z DB - primo overuju login i heslo
        //$where = "login='$login' AND heslo='$heslo'";
        $strings[0]="login";
        $strings[1]=", heslo";
        $var[0]=$login;
        $var[1]=$heslo;
        $where[0]=$strings;
        $where[1]=$var;
        $user = $this->selectFromTable(TABLE_UZIVATEL, $where);
        var_dump($user);
        // ziskal jsem uzivatele
        if(count($user)){
            // ziskal - ulozim ho do session
            $_SESSION[$this->userSessionKey] = $user[0]['id_uzivatel']; // beru prvniho nalezeneho a ukladam jen jeho ID
            return true;
        } else {
            // neziskal jsem uzivatele
            return false;
        }
    }

    /**
     * Odhlasi soucasneho uzivatele.
     */
    public function userLogout(){
        unset($_SESSION[$this->userSessionKey]);
    }

    /**
     * Test, zda je nyni uzivatel prihlasen.
     *
     * @return bool     Je prihlasen?
     */
    public function isUserLogged(){
        return isset($_SESSION[$this->userSessionKey]);
    }

    /**
     * Pokud je uzivatel prihlasen, tak vrati jeho data,
     * ale pokud nebyla v session nalezena, tak vypisu chybu.
     *
     * @return mixed|null   Data uzivatele nebo null.
     */
    public function getLoggedUserData(){
        if($this->isUserLogged()){
            // ziskam data uzivatele ze session
            $userId = $_SESSION[$this->userSessionKey];
            // pokud nemam data uzivatele, tak vypisu chybu a vynutim odhlaseni uzivatele
            if($userId == null) {
                // nemam data uzivatele ze session - vypisu jen chybu, uzivatele odhlasim a vratim null
                echo "SEVER ERROR: Data přihlášeného uživatele nebyla nalezena, a proto byl uživatel odhlášen.";
                $this->userLogout();
                // vracim null
                return null;
            } else {
                // nactu data uzivatele z databaze
                $string[0]="id_user";
                $var[0]="$userId";
                $where[0]=$string;
                $where[1]=$var;
                $userData = $this->selectFromTable(TABLE_UZIVATEL, $where);
                // mam data uzivatele?
                if(empty($userData)){
                    // nemam - vypisu jen chybu, uzivatele odhlasim a vratim null
                    echo "ERROR: Data přihlášeného uživatele se nenachází v databázi (mohl být smazán), a proto byl uživatel odhlášen.";
                    $this->userLogout();
                    return null;
                } else {
                    // protoze DB vraci pole uzivatelu, tak vyjmu jeho prvni polozku a vratim ziskana data uzivatele
                    return $userData[0];
                }
            }
        } else {
            // uzivatel neni prihlasen - vracim null
            return null;
        }
    }

//pridej prispevek
    public function addText(int $idUzivatel, string $aut, string $title, string $text, string $file=""){
        if($file==""){
            $insertStatement = "id_aut, author, title, text";
            $insertValues = "$idUzivatel,'$aut','$title','$text'";
        }
        else {
            $insertStatement = "id_aut, author, title, text, file";
            $insertValues = "$idUzivatel,'$aut','$title','$text', '$file'";
        }
        return $this->insertIntoTable(TABLE_POSTS, $insertStatement, $insertValues);
    }

//odesli prispevek pres id uzivateli s id
    public function sendToRate(int $id_uzivatel, int $id_post){
        $insertStatement = "id_uzivatel, id_post, rating";
        $insertValues = "$id_uzivatel, $id_post, -1";
        return $this->insertIntoTable(TABLE_RATING, $insertStatement, $insertValues);

    }
//najdi a vrat uzivatele podle id
    public function findUser(int $id_uzivatel){
        $string[0]="id_user";
        $var[0]="$id_uzivatel";
        $where[0]=$string;
        $where[1]=$var;
        $userData = $this->selectFromTable(TABLE_UZIVATEL, $where);
        return $userData[0];
    }
//smaz uzivatele podle id
    public function deleteUser(int $id_uzivatel){
        if($this->deleteFromTable(TABLE_USER, "id_uzivatel=$id_uzivatel"))return 1;
        else return 0;
    }
//najdi vsechny prispevky, ktere mam hodnotit
    public function getAllPostsForMe(int $id_uzivatel){
        $string[0]="id_user";
        $var[0]="$id_uzivatel";
        $where[0]=$string;
        $where[1]=$var;
        $rates = $this->selectFromTable(TABLE_RATING, $where);
        return $rates;
    }
//odesli vysledek hodnoceni
    public function sendRating(int $id_uzivatel, int $id_post, float $rating){
        $insertStatement = "id_uzivatel, id_post, rating";
        $insertValues = "$id_uzivatel, $id_post, $rating";
        if($this->insertIntoTable(TABLE_RATING, $insertStatement, $insertValues)){
            $this->checkVis($id_post);
            $this->checkRating($id_post);
            return 1;
        }

        else return 0;
    }
//podle uzivatele a id prispevku, tento prispevek odstran
    public function removeRating(int $id_uzivatel, int $id_post){
        if($this->deleteFromTable(TABLE_RATING, "id_uzivatel=$id_uzivatel && id_post=$id_post"))return 1;
        else return 0;
    }
//zkontroluj jestli uz muze byt prispevek zverejnen
    public function checkVis(int $id_post){
        $counter = 0;
        $string[0]="rating";
        $string[1]=", id_post";
        $var[0]=-1;
        $var[1]=$id_post;
        $where[0]=$string;
        $where[1]=$var;
        foreach ($this->selectFromTable(TABLE_RATING, $where) as $p){
            $counter=$counter+1;
            if($counter>2){
                $string[0]="vis";
                $string[1]=", id_post";
                $var[0]=0;
                $var[1]=$id_post;
                $where[0]=$string;
                $where[1]=$var;
                $post = $this->selectFromTable(TABLE_POSTS, $where);
                $updateStatementWithValues = "pending=1";
                $whereStatement = "id_post=$id_post";
                return $this->updateInTable(TABLE_POSTS, $updateStatementWithValues, $whereStatement);
            }
        }
    }
//prepocitej rating pro prispevek
    public function checkRating($id_post){
        $counter = 0;
        $val = 0;
        $string[0]="id_post";
        $var[0]=$id_post;
        $where[0]=$string;
        $where[1]=$var;
        $array = $this->selectFromTable(TABLE_RATING, $where);
        foreach ($array as $r){
            if($r['rating']>0){
            $val+=$r['rating'];
            $counter++;
            }
        }
        $val/=$counter;
        $update = "rating=$val";

        $string[0]="id_post";
        $var[0]=$id_post;
        $where[0]=$string;
        $where[1]=$var;
        $this->updateInTable(TABLE_POSTS,$update,$where);
    }
//odstran prispevek podle id
    public function deletePost(int $id_post){
        if($this->deleteFromTable(TABLE_POSTS, "id_post=$id_post"))return 1;
        else return 0;
    }

}
?>