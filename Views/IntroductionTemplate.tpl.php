<?php
global $tplData;

// pripojim objekt pro vypis hlavicky a paticky HTML
require(DIRECTORY_VIEWS ."/TemplateBasics.class.php");
$tplHeaders = new TemplateBasics();
require_once(DIRECTORY_MODELS."/MyDatabase.class.php");
$myDB = new MyDatabase();
?>
<!-- ------------------------------------------------------------------------------------------------------- -->

<!-- Vypis obsahu sablony -->
<?php
//odesílání clanku recenzentum
if(isset($_POST['odeslat'])){
    if(isset($_POST['prvni']) && isset($_POST['druhy']) && isset($_POST['treti']) && isset($_POST['recenze'])){
        $prvni = htmlspecialchars($_POST['prvni'], ENT_QUOTES, 'UTF-8');
        $druhy = htmlspecialchars($_POST['druhy'], ENT_QUOTES, 'UTF-8');
        $treti = htmlspecialchars($_POST['treti'], ENT_QUOTES, 'UTF-8');
        $id_post = htmlspecialchars($_POST['recenze'], ENT_QUOTES, 'UTF-8');
        $allusers = $myDB->getAllUsers();
        foreach ($allusers as $u){
            if($u[1]==$prvni || $u[1]==$druhy || $u[1]==$treti){
                if($u[4]>=2){
                    $myDB->sendToRate($u[0], $id_post);
                }
            }
        }
    }
}
//mazani clanku
if(isset($_POST['delete']) && isset($_POST['clanek'])) {
    $post = htmlspecialchars($_POST['clanek'], ENT_QUOTES, 'UTF-8');
    if($myDB->deletePost($post)==1)echo "Post byl smazán.";
    else echo "Smazání se nepodařilo";
    header("Refresh:0");
}
// nacteni hlavicky
$tplHeaders->getHTMLHeader($tplData['title']);

// vypis prispevku cekajici na recenze
$res = "";
if(array_key_exists('stories', $tplData)) {
    foreach ($tplData['stories'] as $d) {
        if ($myDB->getLoggedUserData()[4] == 3 && $d['vis'] == 0 && $d['pending'] == 0) {
            $res .= "<h2>$d[title]</h2>";
            $res .= "<b>Autor:</b> $d[author] ($d[rating])<br><br>";
            $res .= "<div style='text-align:justify;'> $d[text]</div>";
            if($d['file']!=null) {
                $res .= "<a href='./uploads/$d[file]'>FILE</a>";
            }
            $res .= "<br>";
            $res .= "Zadejte jména recenzentů: <form action='' method='POST'>";
            $res .= "<input type='text' name='prvni'> <input type='text' name='druhy'><input type='text' name='treti' class=><br><br>";
            $res .= "<input type='hidden' name='recenze' value='$d[0]'><input type='submit' name='odeslat' value='Odeslat recenzentům' class='btn btn-warning mb-2'><br>";
            $res .= "<input type='hidden' name='clanek' value='$d[0]'><input type='submit' name='delete' value='Smazat' class='btn btn-warning mb-2'></form><hr>";
        }
    }

//vypis ostatnich clanku
    foreach ($tplData['stories'] as $d) {
        if($d['vis']==1 || $myDB->getLoggedUserData()[4]==3 || ($d['vis']==0 && $d['id_aut']==$myDB->getLoggedUserData()[0])) {
            $res .= "<h2>$d[title]</h2>";
            $res .= "<b>Autor:</b> $d[author] ($d[rating])<br><br>";
            $res .= "<div style='text-align:justify;'> $d[text]</div>";
            if($d['file']!=null) {
                $res .= "<a href='./uploads/$d[file]' target='_blank'>FILE</a>";
            }
            if($d['vis']==0 && $d['id_aut']==$myDB->getLoggedUserData()[0]){
                $res .= "<br><h5>- Tanto příspěvek vidíte pouze vy a čeká na schválení a zveřejnění</h5>";
            }
            $res .= "<hr>";
        }
    }


}
else {
    $res .= "Nemám žádné příspěvky";
}
echo $res;

// paticka
$tplHeaders->getHTMLFooter()

?>