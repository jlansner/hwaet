<?php

ini_set( "display_errors", 1 );
ini_set( "display_startup_errors", 1 );
error_reporting( E_ALL );

include( "includes/Translation.php" );
$translate = new Translation();

$translation = $translate->getRandomRow();

if ( $translation ) {
    $post = $translate->postToBlueSky( $translation );
};

?>