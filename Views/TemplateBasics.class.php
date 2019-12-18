<?php

/**
 * Trida vypisujici HTML hlavicku a paticku stranky.
 */
class TemplateBasics {

    /**
     *  Vrati vrsek stranky az po oblast, ve ktere se vypisuje obsah stranky.
     *  @param string $pageTitle    Nazev stranky.
     */
    public function getHTMLHeader(string $pageTitle) {
        ?>

        <!doctype html>
        <html>
            <head>
                <meta charset='utf-8'>
                <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
                <title><?php echo $pageTitle; ?></title>
                <link rel="stylesheet" href="styles.css">
                <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
            </head>
            <body>


                <nav><h1><?php echo $pageTitle; ?></h1><br>
                    <?php
                        // vypis menu (celeho, pokud by se uzivatel dostal kam nema, bude presmerovan)
                        foreach(WEB_PAGES as $key => $pInfo){
                                echo "<div style='width: 10vw;display: inline'; class='nav'>
                                            <a href='index.php?page=$key'>$pInfo[title]</a></div>";
                        }
                        ?>
                </nav>
                <br><br><br><br><br>
        <?php
    }

    /**
     *  Vrati paticku stranky.
     */
    public function getHTMLFooter(){
        ?>
                <br><br>
                <footer>WEB - Samostatná práce (konferenční systém)</footer>
            <body>
        </html>

        <?php
    }
        
}

?>