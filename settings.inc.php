<?php
//////////////////////////////////////////////////////////////////
/////////////////  Globalni nastaveni aplikace ///////////////////
//////////////////////////////////////////////////////////////////

//// Pripojeni k databazi ////

/** Adresa serveru. */
define("DB_SERVER","localhost");
/** Nazev databaze. */
define("DB_NAME","sp");
/** Uzivatel databaze. */
define("DB_USER","root");
/** Heslo uzivatele databaze */
define("DB_PASS","");


//// Nazvy tabulek v DB ////

/** Tabulka s prispevky. */
define("TABLE_POSTS", "schenkj_posts");
/** Tabulka s uzivateli. */
define("TABLE_USER", "schenkj_uzivatel");
/** Tabulka s uzivateli. */
define("TABLE_UZIVATEL","schenkj_uzivatel");
/** Tabulka s pravomocemi. */
define("TABLE_PRAVO","schenkj_rights");
/** Tabulka s recenzemi. */
define("TABLE_RATING","schenkj_recenze");


//// Dostupne stranky webu ////

/** Adresar kontroleru. */
const DIRECTORY_CONTROLLERS = "Controllers";
/** Adresar modelu. */
const DIRECTORY_MODELS = "Models";
/** Adresar sablon */
const DIRECTORY_VIEWS = "Views";

/** Dostupne webove stranky. */
const WEB_PAGES = array(
    // uvodni stranka
    "uvod" => array("file_name" => "IntroductionController.class.php",
                    "class_name" => "IntroductionController",
                    "title" => "Hlavní&nbsp;stránka"),

    //login
    "login" => array("file_name" => "LoginController.class.php",
        "class_name" => "LoginController",
        "title" => "Přihlášení"),
    //registrace
    "register" => array("file_name" => "RegisterController.class.php",
        "class_name" => "RegisterController",
        "title" => "Registrace"),

    //pridavani prispevku
    "write" => array("file_name" => "WriteController.class.php",
        "class_name" => "WriteController",
        "title" => "Přidat&nbsp;příspěvek"),

    //hodnoceni
    "rate" => array("file_name" => "RateController.class.php",
        "class_name" => "RateController",
        "title" => "Hodnocení"),

    // sprava uzivatelu
    "sprava" => array("file_name" => "UserManagementController.class.php",
        "class_name" => "UserManagementController",
        "title" => "Správa&nbsp;uživatelů"),
);

/** Klic defaultni webove stranky. */
const DEFAULT_WEB_PAGE_KEY = "uvod";

?>