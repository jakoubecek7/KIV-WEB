<?php

global $tplData;

// pripojim objekt pro vypis hlavicky a paticky HTML
require(DIRECTORY_VIEWS ."/TemplateBasics.class.php");
$tplHeaders = new TemplateBasics();

?>
    <!-- ------------------------------------------------------------------------------------------------------- -->

    <!-- Vypis obsahu sablony -->
<?php
// muze se hodit: strtotime($d['date'])
require_once(DIRECTORY_MODELS."/MyDatabase.class.php");
$myDB = new MyDatabase();
// hlavicka
$tplHeaders->getHTMLHeader($tplData['title']);

// zpracovani odeslanych formularu
if(isset($_POST['potvrzeni'])){
    if(isset($_POST['tit']) && isset($_POST['tex']) && isset($_FILES['fileToUpload'])){
        $id_aut = $myDB->getLoggedUserData()[0];
        $aut = $myDB->getLoggedUserData()[1];
            $target_dir = "./uploads/";
            $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
// neexistuje?
            if (file_exists($target_file)) {
                echo "Sorry, file already exists.<br>";
                $uploadOk = 0;
            }
// neni to podezřele veliké?
            if ($_FILES["fileToUpload"]["size"] > 500000) {
                echo "Sorry, your file is too large.<br>";
                $uploadOk = 0;
            }
// je to pdf?
            if ($imageFileType != "pdf") {
                echo "Sorry, only PDF files are allowed.<br>";
                $uploadOk = 0;

            }
//neco se nepovedlo
            if ($uploadOk == 0) {
                echo "Sorry, your file was not uploaded.<br>";
                $res = "";
//zkus upload
            } else {
                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                    echo "Soubor " . basename($_FILES["fileToUpload"]["name"]) . " se podařilo nahrát.<br>";
                    $res = $myDB->addText($id_aut,$aut, htmlspecialchars($_POST['tit'], ENT_QUOTES, 'UTF-8'), htmlspecialchars($_POST['tex'], ENT_QUOTES, 'UTF-8'), $_FILES["fileToUpload"]["name"]);
                } else {
                    echo "Sorry, there was an error uploading your file.<br>";
                    $res .= "";
                }
            }
        if($res){
            echo "OK: Odesláno.";
        } else {
            echo "ERROR: Odeslání se nezdařilo.";
        }
    }
    echo "<br>";
}
//odeslani bez pdf
else if(isset($_POST['potvrzeni2'])) {
    // prihlaseni
    if (isset($_POST['tit']) && isset($_POST['tex'])) {

        $id_aut = $myDB->getLoggedUserData()[0];
        $aut = $myDB->getLoggedUserData()[1];
        $res = $myDB->addText($id_aut, $aut, htmlspecialchars($_POST['tit'], ENT_QUOTES, 'UTF-8'), htmlspecialchars($_POST['tex'], ENT_QUOTES, 'UTF-8'));
        if ($res) {
            echo "OK: Odesláno.";
        } else {
            echo "ERROR: Odeslání se nezdařilo.";
        }
    }
}

// pokud je uzivatel prihlasen, tak ziskam jeho data
if($myDB->isUserLogged()){
    // ziskam data prihlasenoho uzivatele
    $user = $myDB->getLoggedUserData();
}

// pokud uzivatel neni prihlasen nebo nebyla ziskana jeho data, tak je presmerovan na prihlaseni
if(!$myDB->isUserLogged()){
    echo ("<script LANGUAGE='JavaScript'>
    window.alert('Přidávat příspěvky můžou jen přihlášení uživatelé, pro přidávání příspěvků se prosím přihlašte, nebo zaregisterujte.');
    window.location.href='http://localhost/SP/index.php?page=login';
    </script>");
    echo "Přidávat příspěvky můžou jen přihlášení uživatelé, pro přidávání příspěvků se prosím přihlašte.";
}
//formular pro pridavani prispevku
else{
    ?>
    <form action="" method="POST" id="posting" enctype="multipart/form-data">
        <table>
            <tr><td>Titul:<br> <input type="text" name="tit" class="form-control"></td></tr>
            <tr><td>Text:<br> <textarea form="posting" maxlength="3000" name="tex" class="form-control" style="min-width: 80vw; min-height: 15vw;"></textarea></td></tr>
            <tr><td><input type="file" name="fileToUpload" id="fileToUpload" form="posting" value=""><br><br></tr></td>
            <tr><td><input type="submit" name="potvrzeni" value="Odeslat s pdf" class='btn btn-warning mb-2'>
            <input type="submit" name="potvrzeni2" value="Odeslat bez pdf" class='btn btn-warning mb-2'></tr></td>
    </table>
    </form>
    <?php
}

// paticka
$tplHeaders->getHTMLFooter()

?>