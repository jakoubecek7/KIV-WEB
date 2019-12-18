<?php

global $tplData;

// pripojim objekt pro vypis hlavicky a paticky HTML
require(DIRECTORY_VIEWS ."/TemplateBasics.class.php");
$tplHeaders = new TemplateBasics();
require_once(DIRECTORY_MODELS."/MyDatabase.class.php");
$myDB = new MyDatabase();
// hlavicka
$tplHeaders->getHTMLHeader($tplData['title']);

// zpracovani odeslanych formularu
if(isset($_POST['action'])){
    // prihlaseni
    if($_POST['action'] == 'login' && isset($_POST['login']) && isset($_POST['heslo'])){
        // pokusim se prihlasit uzivatele
        $res = $myDB->userLogin(htmlspecialchars($_POST['login'], ENT_QUOTES, 'UTF-8'), htmlspecialchars($_POST['heslo'], ENT_QUOTES, 'UTF-8'));
        if($res){
            echo "OK: Uživatel byl přihlášen.";
        } else {
            echo "ERROR: Přihlášení uživatele se nezdařilo.";
        }
    }
    // odhlaseni
    else if($_POST['action'] == 'logout'){
        // odhlasim uzivatele
        $myDB->userLogout();
        echo "OK: Uživatel byl odhlášen.";
    }
    // neznama akce
    else {
        echo "WARNING: Neznámá akce.";
    }
    echo "<br>";
}

// pokud je uzivatel prihlasen, tak ziskam jeho data
if($myDB->isUserLogged()){
    // ziskam data prihlasenoho uzivatele
    $user = $myDB->getLoggedUserData();
}

// pokud uzivatel neni prihlasen nebo nebyla ziskana jeho data, tak vypisu prihlasovaci formular
if(!$myDB->isUserLogged()){
    ?>
    <h2>Přihlášení uživatele</h2>

    <form action="" method="POST">
        <table>
            <tr><td>Login:</td><td><input type="text" name="login" class="form-control"><br></td></tr>
            <tr><td>Heslo:</td><td><input type="password" name="heslo" class="form-control"><br></td></tr>
        </table>
        <input type="hidden" name="action" value="login">
        <input type="submit" name="potvrzeni" value="Přihlásit" class="btn btn-warning mb-2">
    </form>
    <?php
}
//uzivatel je prihlaseny
else {
    // ziskam nazev prava uzivatele, abych ho mohl vypsat
    $pravo = $myDB->selectFromTable(TABLE_PRAVO, "id_pravo=$user[id_pravo]");
    // ziskal jsem dane pravo
    if(empty($pravo)){
        // neziskal - pouziju jen Nezname
        $pravo = "*Neznámé*";
    } else {
        // ziskal - vytahnu jeho nazev
        $pravo = $pravo[0]['nazev'];
    }
    ?>
    <h2>Přihlášený uživatel</h2>

    Login: <?php echo $user['login'] ; ?><br>
    Jméno: <?php echo $user['jmeno'] ; ?><br>
    <br>

    Odhlášení uživatele:
    <form action="" method="POST">
        <input type="hidden" name="action" value="logout">
        <input type="submit" name="potvrzeni" value="Odhlásit" class="btn btn-warning mb-2">
    </form>
    <?php
}
// paticka
$tplHeaders->getHTMLFooter()

?>