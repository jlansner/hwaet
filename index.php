<!DOCTYPE html>
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include( "includes/Translation.php" );

$translate = new Translation();

$translations = $translate->getData();
$translationsCount = count( $translations );

for( $i = 0; $i < $translationsCount; $i++ ) {
    $translations[ $i ][ "cssStyle" ] = strtolower( str_replace( " ", "_",  $translations[ $i ][ "languageInEnglish" ] ) );
    $translations[ $i ][ "fullName" ] = $translate->getFullName( $translations[ $i ] );
    $translations[ $i ][ "fullNameByLast" ] = $translate->getFullName( $translations[ $i ], true );
    if ( $translations[ $i ][ "not_translated" ] ) {
        $translations[ $i ][ "translation" ] = "—";
    }
}

 $translationJS = json_encode( $translations );
?>
<html>
    <head>
        <title>Hwaet</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js" integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script type="text/javascript">
            const translations = <?php echo $translationJS; ?>;
        </script>
        <script type="text/javascript" src="js/main.js"></script>
        <link rel="stylesheet" href="css/main.css" />
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" />
        <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css" />
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-5NFXLT8B4P"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            
            gtag('js', new Date());
            gtag('config', 'G-5NFXLT8B4P');
        </script>
        
        <meta property="og:url" content="https://hwaet.info" />
        <meta property="og:title" content="Hwaet!" />
        <meta property="og:description" content="We Gardena in geardagum" />
        <meta property="og:image" content="https://hwaet.info/images/hwaet.png" />
    </head>
    <body>
        <div class="pageWrapper" id="pageWrapper">
            <?php foreach( $translations as $translation ) {
                if ( trim( $translation[ "translation" ] ) ) { ?>
                <div class="textWrapper">
                <h1 class="<?php echo $translation[ "cssStyle" ]; ?>">
                    <?php 
                        echo $translation[ "translation" ]; 
                        if ( strlen( trim( $translation[ "translation_eng" ] ) ) ) { ?>
                            <br /><span class="englishTranslation">(<?php echo $translation[ "translation_eng" ] ?>)</span>
                        <?php
                        }
                    ?>
                
                </h1>
            <h2><?php 
            echo $translation[ "fullName" ];
            if ( $translation[ "year" ] ) {
                echo ", " . $translation[ "year" ];
            } ?></h2>
        </div>
            
            <?php 
            }
            } ?>
        </div>
        
    
        <footer>
            <div>i</div>
        </footer>
        <div class="creditOverlay">
            <div class="closeOverlay">X</div>
            <h2>All Translations</h2>
            
            <div class="fullList" id="fullList">
            <table id="fullListTable" class="stripe row-border">
                <thead>
                    <th>Author</th>
                    <th>Title</th>
                    <th>Year</th>
                    <th>Format</th>
                    <th>Language</th>
                    <th>Translation</th>
                    <th>English Translation</th>
                </thead>
                <tbody>
                    <?php foreach( $translations as $translation ) { ?>
                    <tr>
                        <td><?php echo $translation[ "fullNameByLast" ]; ?></td>
                        <td><?php 
                            if ( $translation[ "url" ] ) { ?>
                                <a href="<?php echo $translation[ "url" ] ?>" target="_blank"><?php echo $translation[ "title" ] ?></a>
                            <?php } else {
                                echo $translation[ "title" ]; 
                            }
                        ?></td>
                        <td><?php echo $translation[ "year" ]; ?></td>
                        <td><?php echo $translation[ "format" ]; ?></td>
                        <td><?php echo $translation[ "language" ]; ?></td>
                        <td><?php echo $translation[ "translation" ]; ?></td>
                        <td><?php echo $translation[ "translation_eng" ]; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        
            <p>
                Created by <a href="https://lansner.com" target="_blank">Jesse Lansner</a>.<br />
            Additional translations provided by <a href="https://twitter.com/AlisonKillilea" target="_blank">Alison Killilea</a>
            </p>
        </div>
    </body>
</html>