<?php
global $tplData;
global $hodnoceno;
// pripojim objekt pro vypis hlavicky a paticky HTML
require(DIRECTORY_VIEWS ."/TemplateBasics.class.php");
$tplHeaders = new TemplateBasics();
require_once(DIRECTORY_MODELS."/MyDatabase.class.php");
$myDB = new MyDatabase();

// hlavicka
$tplHeaders->getHTMLHeader($tplData['title']);
//Presmerovani na prihlaseni, pokud neni prihlaseny. Pokud nema prava, je odeslan na uvodni stranku
if(!$myDB->isUserLogged()){
    echo ("<script LANGUAGE='JavaScript'>
    window.alert('Nejste přihlášení. Hodnotit články můžou pouze přihlášení uživatelé, kteří byli zvoleni za recenzenty!');
    window.location.href='http://localhost/SP/index.php?page=login';
    </script>");
}

if($myDB->isUserLogged() && $myDB->getLoggedUserData()[4]<2){
    echo "<script LANGUAGE='JavaScript'>
window.alert('Pro hodnocení příspěvků nemáte dostatečná oprávnění. Přihlašte se jako váš kamarád, který tato oprávnění určitě má.');
    window.location.href='http://localhost/SP/index.php?page=uvod';
    </script>";
}
else {
    //hodnoceni prispevku - prumerovani hodnot a ukladani do db (mazani zaznamu o prirazeni clanku, nebo starych hodnoceni)
    if(isset($_POST['rate'])) {
        $val1 = htmlspecialchars($_POST['napad'], ENT_QUOTES, 'UTF-8');
        $val2 = htmlspecialchars($_POST['provedeni'], ENT_QUOTES, 'UTF-8');
        $val3 = htmlspecialchars($_POST['zpusob'], ENT_QUOTES, 'UTF-8');
        $val = ($val1 + $val2 + $val3)/3;
        $post = htmlspecialchars($_POST['recenze'], ENT_QUOTES, 'UTF-8');
        //$hodnoceno = $_POST['recenze2'];
        $userId = $myDB->getLoggedUserData()[0];
        if($hodnoceno != -1){
            $myDB->removeRating($userId, $post);
        }
        else $myDB->removeRating($userId, $post);

        $myDB->sendRating($userId, $post, $val);
        header("Refresh:0");
    }
    //mazani clanku
    if(isset($_POST['delete'])) {
        $val = htmlspecialchars($_POST['del'], ENT_QUOTES, 'UTF-8');
        if(!$myDB->deletePost($val))echo"Chyba při mazání";
        header("Refresh:0");
    }
    //odhaleni prispevku vsem na uvodni strance
    if(isset($_POST['reveal'])) {
        $val = htmlspecialchars($_POST['rev'], ENT_QUOTES, 'UTF-8');
        $myDB->updateInTable(TABLE_POSTS, "vis=1","id_post=$val");
        header("Refresh:0");
    }
    //vypis clanku k ohodnoceni (nejdrive zatim nehodnocene clanky)
    $res = "";
    //pro admina vypis clanky, ktere jsou ohodnocene a cekaji uz jen na zverejneni
    if (array_key_exists('stories', $tplData) && $myDB->getLoggedUserData()[4]>=3) {
        foreach ($tplData['stories'] as $d) {
            if ($d['vis'] == 0 && $d['pending'] == 1) {
                $res .= "<h2>$d[title]</h2>";
                $res .= "<b>Autor:</b> $d[author]<br><br>";
                $res .= "<div style='text-align:justify;'> $d[text]</div>";
                $res .= "Hodnocení: $d[rating]<br><form action='' method='post'>";
                $res .= "<input type='hidden' name='del' value='$d[0]'>";
                $res .= "<input type='submit' name='delete' value='Smazat'  class='btn btn-warning mb-2'>";
                $res .= "<input type='hidden' name='rev' value='$d[0]'>";
                $res .= "<input type='submit' name='reveal' value='Zveřejnit'  class='btn btn-warning mb-2'></form><hr>";
            }
        }
    } else {
        //nic nevypisuj
    }
    if (array_key_exists('stories', $tplData)) {
        $posts = $myDB->getAllPostsForMe($myDB->getLoggedUserData()[0]);

        foreach ($tplData['stories'] as $d) {
            if ($d['vis'] == 0) {
                foreach ($posts as $post) {
                    if ($post[2] == $d['id_post'] && $post[3]==-1) {

                        $res .= "<h2>$d[title]</h2>";
                        $res .= "<b>Autor:</b> $d[author]<br><br>";
                        $res .= "<div style='text-align:justify;'> $d[text]</div>";
                        $res .= "Hodnocení: <br><form action='' method='POST'>";
                        $res .= "Nápad: 1*<input type='radio' name='napad' value='1' checked='checked'>  2*<input type='radio' name='napad' value='2'> 3*<input type='radio' name='napad' value='3'> 4*<input type='radio' name='napad' value='4'> 5*<input type='radio' name='napad' value='5'><br>";
                        $res .= "Provedení: 1*<input type='radio' name='provedeni' value='1' checked='checked'>  2*<input type='radio' name='provedeni' value='2'> 3*<input type='radio' name='provedeni' value='3'> 4*<input type='radio' name='provedeni' value='4'> 5*<input type='radio' name='provedeni' value='5'><br>";
                        $res .= "Způsob: 1*<input type='radio' name='zpusob' value='1' checked='checked'>  2*<input type='radio' name='zpusob' value='2'> 3*<input type='radio' name='zpusob' value='3'> 4*<input type='radio' name='zpusob' value='4'> 5*<input type='radio' name='zpusob' value='5'><br>";


                        $res .= "<input type='hidden' name='recenze' value='$d[0]'>";
                        //$res .= "<input type='hidden' name='recenze2' value='$post[3]'>";
                        $hodnoceno=$post[3];
                        $res .= "<input class='btn btn-warning mb-2' type='submit' name='rate' value='Ohodnoť'></form><hr>";
                    }
                }
            }
        }
        //nyni jiz ohodnocene clanky pro zmenu sveho stareho hodnoceni
        foreach ($tplData['stories'] as $d) {
            if ($d['vis'] == 0) {
                foreach ($posts as $post) {
                    if ($post[2] == $d['id_post'] && $post[3] != -1) {

                        $res .= "<h2>$d[title]</h2>";
                        $res .= "(Tvé předchozí hodnocení: $post[3])<br>";
                        $res .= "<b>Autor:</b> $d[author]<br><br>";
                        $res .= "<div style='text-align:justify;'> $d[text]</div>";
                        $res .= "Hodnocení: <br><form action='' method='POST'>";
                        $res .= "Nápad: 1*<input type='radio' name='napad' value='1' checked='checked'>  2*<input type='radio' name='napad' value='2'> 3*<input type='radio' name='napad' value='3'> 4*<input type='radio' name='napad' value='4'> 5*<input type='radio' name='napad' value='5'><br>";
                        $res .= "Provedení: 1*<input type='radio' name='provedeni' value='1' checked='checked'>  2*<input type='radio' name='provedeni' value='2'> 3*<input type='radio' name='provedeni' value='3'> 4*<input type='radio' name='provedeni' value='4'> 5*<input type='radio' name='provedeni' value='5'><br>";
                        $res .= "Způsob: 1*<input type='radio' name='zpusob' value='1' checked='checked'>  2*<input type='radio' name='zpusob' value='2'> 3*<input type='radio' name='zpusob' value='3'> 4*<input type='radio' name='zpusob' value='4'> 5*<input type='radio' name='zpusob' value='5'><br>";

                        $res .= "<input type='hidden' name='recenze' value='$d[0]'>";
                        $hodnoceno=$post[3];
                        $res .= "<input class='btn btn-warning mb-2' type='submit' name='rate' value='Ohodnoť'></form><hr>";
                    }
                }
            }
        }

    } else {
        //nic nevypisuj
    }


    echo $res;
}
// paticka
$tplHeaders->getHTMLFooter()

?>