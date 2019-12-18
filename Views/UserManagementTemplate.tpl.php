<?php

//// vypis sablony
// urceni globalnich promennych, se kterymi sablona pracuje
global $tplData;

// pripojim objekt pro vypis hlavicky a paticky HTML
require(DIRECTORY_VIEWS ."/TemplateBasics.class.php");
require_once(DIRECTORY_MODELS."/MyDatabase.class.php");
$tplHeaders = new TemplateBasics();

// hlavicka
$tplHeaders->getHTMLHeader($tplData['title']);
$myDB = new MyDatabase();
//presmerovani pokud tu nema uzivatel co delat
if($myDB->getLoggedUserData()[4]!=3)
{
    echo ("<script LANGUAGE='JavaScript'>
    window.alert('K takto citlivým informacím a silným pravomocem má přístup pouze pár vyvolených. Přijď až získáš administrátorská práva.');
    window.location.href='http://localhost/SP/index.php?page=login';
    </script>");
}
else {
// nemuzu smazat sebe, jinak smaz vybraneho uzivatele
    if (isset($tplData['delete']) && $myDB->getLoggedUserData()[0]!=htmlspecialchars($_POST['id_user'], ENT_QUOTES, 'UTF-8')) {
        if($myDB->deleteUser(htmlspecialchars($_POST['id_user'], ENT_QUOTES, 'UTF-8'))==1)echo "Uživatel byl smazán.";
        else echo "Smazání se nepodařilo";
        header("Refresh:0");

    }
// zmena prav uzivatele - nelze odebrat sva prava
    if (isset($_POST['change']) && $myDB->getLoggedUserData()[0]!=htmlspecialchars($_POST['id_user'], ENT_QUOTES, 'UTF-8')){
        $myUser = $myDB ->findUser(htmlspecialchars($_POST['id_user'], ENT_QUOTES, 'UTF-8'));
        $id = $myUser[0];
        $jmeno = $myUser[1];
        $login = $myUser[2];
        $heslo = $myUser[3];
        $pravo = htmlspecialchars($_POST['pravo'], ENT_QUOTES, 'UTF-8');
        $myDB->updateUser($id, $login, $heslo, $jmeno, $pravo);
        header("Refresh:0");
    }


    $res = "<table border><tr><th>ID</th><th>Jméno</th><th>Login</th><th>Práva</th><th>Smazání</th><th>Právo:</th></tr>";
// projdu data a vypisu radky tabulky
    foreach ($tplData['users'] as $u) {
        $res .= "<tr><td>$u[id_uzivatel]</td><td>$u[jmeno]</td><td>$u[login]</td><td>$u[id_pravo]</td>"
            . "<td><form method='post'>"
            . "<input type='hidden' name='id_user' value='$u[id_uzivatel]'>"
            . "<button type='submit' name='action' value='delete'>Smazat</button>";
        $res .= "<td><select name='pravo'>";
        // ziskam vsechna prava
        $rights = $myDB->getAllRights();
        // projdu je a vypisu
        foreach ($rights as $r) {
            $res .= "<option value='$r[0]'>$r[1] = $r[0]</option>";
        }
        $res .= "</select></td><td><button type='submit' name='change' value='$r[0]'>Změna</button></td>";
        $res .= "</form></td></tr>";
    }

    $res .= "</table>";
    echo $res;
}
// paticka
$tplHeaders->getHTMLFooter()

?>