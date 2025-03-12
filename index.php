<!DOCTYPE html>
<?php

ini_set( "display_errors", 1 );
ini_set( "display_startup_errors", 1 );
error_reporting( E_ALL );

include( "includes/Translation.php" );
include( "includes/Utils.php" );

$translate = new Translation();
$utils = new Utils();

$translations = $translate->getData();

$translationsCount = count( $translations );

$languageCount = $utils->getCount( $translations, "languageInEnglish" );

$translationJS = json_encode( $translations );
?>
<html>
    <head>
        <title>Hwaet</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.14.1/jquery-ui.min.js"></script>
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js" integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script type="text/javascript">
            const translations = <?php echo $translationJS; ?>;
        </script>
        <script type="text/javascript" src="js/main.js"></script>
        <link rel="stylesheet" href="css/main.css" />
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" />
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.14.1/themes/smoothness/jquery-ui.css">
        
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
            <?php foreach( $translations as $translation ) { ?>
                <div class="textWrapper">
                <h1 class="<?php echo $translation[ "cssStyle" ]; ?>">
                    <?php 
                        echo $translation[ "translation" ]; 
                        if ( $utils->isStringWithContent( $translation[ "translationTransliteration" ] ) ) { ?>
                            <br /><span class="transliteration">[<?php echo $translation[ "translationTransliteration" ] ?>]</span>
                        <?php
                        }
                        if ( $utils->isStringWithContent( $translation[ "translationInEnglish" ] ) ) { ?>
                            <br /><span class="englishTranslation">(<?php echo $translation[ "translationInEnglish" ] ?>)</span>
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
            
            <?php } ?>
        </div>
        
    
        <footer>
            <div>i</div>
        </footer>
        <div id="menu" class="creditOverlay">
            <div class="closeOverlay">X</div>
            <ul>
                <li><a href="#allData">All Translations</a></li>    
                <li><a href="#about">About this Site</a></li>
            </ul>
            <div id="allData">
                <h2>All Translations</h2>
                
                <div class="fullList" id="fullList">
                    <table id="fullListTable" class="stripe row-border">
                        <thead>
                            <th>Author</th>
                            <th>Title</th>
                            <th>Year</th>
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
                                <td><?php echo $translation[ "languageInEnglish" ]; ?></td>
                                <td><?php echo $translation[ "translation" ]; 
                                if ( $utils->isStringWithContent( $translation[ "translationTransliteration" ] ) ) { ?>
                                    <span class="transliteration">[<?php echo $translation[ "translationTransliteration" ] ?>]</span>
                                <?php
                                }?></td>
                                <td><?php echo $translation[ "translationInEnglish" ]; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="about">
                <h2>About This Site</h2>
                <p>
                    The idea for this site started when I read Maria Dahvana Headley's amazing novel <a href="https://bookshop.org/p/books/the-mere-wife-maria-dahvana-headley/8988400" target="_blank"><em>The Mere Wife</em></a>, a retelling of <em>Beowulf</em> set in the modern day. In that book she opened each chapter with a possible translation of <em>hwaet</em> – say, listen, so, what, attend, hark, tell, behold, ah, lo, yes, sing, and now. I got the idea of creating a crossword puzzle using those words, but got nowhere with it until I realized it would make more sense as a <a href="https://jklcrosswords.com/index.php/2021/08/11/puzzle-32-crypt-epic/" target="_blank">cryptic puzzle</a>.
                </p>

                <p>
                    I created this site as an accompaniment to the puzzle. Originally it had about two dozen translations, all in English. Now it's up to <?php echo $translationsCount; ?> entries in <?php echo $languageCount ?> languages. Because I am only concerned about translations of <em>hwaet</em>, this list includes a number of adaptations that do not even pretend to be translations, such as Zach Weinersmith's wonderful graphic novel <a href="https://www.smbc-comics.com/bea/" target="_blank"><em>Bea Wolf</em></a>, in which the 5-year-old heroine must defend a tree house from the evil Mr. Grindle. It also only includes versions of <em>Beowulf</em> that treat <em>hwaet</em> as in interjection. George Walkden, of the University of Manchester, <a href="http://walkden.space/Walkden_2013_hwaet.pdf" target="_blank">argued in 2011</a> that <em>hwaet</em> should be read simply as part of the opening sentence. Whatever the linguistic merit of that argument, I find it boring on a literary level. An epic poem deserves an epic beginning.
                </p>
                    
                <p>
                    There are many more translations of <em>Beowulf</em> out there that I know of but haven't been able to check – and surely even more than I don't know about – so as I'm able to track them down I'll add them to the list. Most of the translations and transliterations from other languages are based on Google Translate, so if you have a better suggestion for any of them, please send me an <a href="mailto:info@hwaet.info">email</a>. Also email me if you know of any translations that I'm missing.
                </p>

                <h3>A Note on Punctuation</h3>

                <p>
                    The only surviving manuscript of <em>Beowulf</em> does not have any punctuation after the word <em>hwaet</em>. This is not too surprising, as most of the punctuation marks we use today weren't created until centuries later. Most translators place a comma after their translation of <em>hwaet</em>. For the sake of clarity, I have left those out, and only included less common marks (period, exclamation point, etc.).
                </p>

                <h3>Selected Sources</h3>

                <ul>
                    <li>Allan, Syd, <a href="https://www.paddletrips.net/beowulf/index.html" target="_blank">BeowulfTranslations.net</a></li>
                    <li>Mize, Britt, <a href="http://beowulf.dh.tamu.edu" target=_blank"><em>Beowulf’s Afterlives Bibliographic Database</em>, 2018-present</a></li>
                    <li>Osborn, Marijane, <em>Annotated List of Beowulf Translations</em>, 2003</li>
                    <li>Sauer, Hans  with Julia Hartmann, Michael Riedl, Tatsiana Saniuk, and Elisabeth Kubaschewski, <em>205 Years of Beowulf Translations and Adaptations (1805–2010): A Bibliography</em>, 2011</li>
                </ul>



                <h3>Special thanks to</h3>
                <ul>
                    <li><a href="https://bsky.app/profile/laurabrarian.bsky.social" target="_blank">Laura Braunstein</a></li>
                    <li><a href="https://twitter.com/AlisonKillilea" target="_blank">Alison Killilea</a></li>
                    <li><a href="https://bsky.app/profile/guan.dk" target="_blank">Guan Yang</a></li>
                </ul>

                <p>
                    Created by <a href="https://lansner.com" target="_blank">Jesse Lansner</a>.<br />
                    &copy; 2021-<?php echo date("Y"); ?>
                </p>
            </div>
        </div>
    </body>
</html>