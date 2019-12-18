<?php
global $tplData;

// pripojim objekt pro vypis hlavicky a paticky HTML
require(DIRECTORY_VIEWS ."/TemplateBasics.class.php");
$tplHeaders = new TemplateBasics();
//pripojim sve funkce
require_once(DIRECTORY_MODELS."/MyDatabase.class.php");
$myDB = new MyDatabase();

// hlavicka
$tplHeaders->getHTMLHeader($tplData['title']);

// zpracovani odeslanych formularu
if(isset($_POST['potvrzeni'])){
    // mam vsechny pozadovane hodnoty?
    if(isset($_POST['login']) && isset($_POST['heslo']) && isset($_POST['heslo2'])
        && isset($_POST['jmeno'])
        && htmlspecialchars($_POST['heslo'], ENT_QUOTES, 'UTF-8') == htmlspecialchars($_POST['heslo2'], ENT_QUOTES, 'UTF-8')
        && htmlspecialchars($_POST['login'], ENT_QUOTES, 'UTF-8') != "" && htmlspecialchars($_POST['heslo'], ENT_QUOTES, 'UTF-8') != "" && htmlspecialchars($_POST['jmeno'], ENT_QUOTES, 'UTF-8') != ""){
        // mam vsechny atributy - ulozim uzivatele do DB
        $res = $myDB->addNewUser(htmlspecialchars($_POST['login'], ENT_QUOTES, 'UTF-8'), htmlspecialchars($_POST['heslo'], ENT_QUOTES, 'UTF-8') , htmlspecialchars($_POST['jmeno'], ENT_QUOTES, 'UTF-8'), 1);
        // byl ulozen?
        if($res){
            echo "OK: Uživatel byl přidán do databáze.";
        } else {
            echo "ERROR: Uložení uživatele se nezdařilo.";
        }
    } else {
        // nemam vsechny atributy
        echo "ERROR: Nebyly přijaty požadované atributy uživatele.";
    }
    echo "<br><br>";
}

//pokud uzivatel neni prihlaseny, dej mu formular pro registraci
if(!$myDB->isUserLogged()){
    ?>
    <h2>Registrační formulář</h2>
    <form action="" method="POST" oninput="x.value=(pas1.value==pas2.value)?'OK':'Nestejná hesla'">
        <table>
            <tr><td>Login:</td><td><input type="text" name="login" required></td></tr>
            <tr><td>Heslo 1:</td><td><input type="password" name="heslo" id="pas1" required></td></tr>
            <tr><td>Heslo 2:</td><td><input type="password" name="heslo2" id="pas2" required></td></tr>
            <tr><td>Ověření hesla:</td><td><output name="x" for="pas1 pas2"></output></td></tr>
            <tr><td>Jméno:</td><td><input type="text" name="jmeno" required></td></tr>
        </table>

        <input type="submit" name="potvrzeni" value="Registrovat" class='btn btn-warning mb-2'>
    </form>
    <?php
} else {
    //je prihlasen a nema tu co delat - odeslani na login, kde se muze odhlasit
    echo ("<script LANGUAGE='JavaScript'>
        window.alert('Jste přihlášeni. Pro možnost registrace se musíte nejprve odhlásit!');
        window.location.href='http://localhost/SP/index.php?page=login';
        </script>");

}
//echo $res;

// paticka
$tplHeaders->getHTMLFooter()

?>