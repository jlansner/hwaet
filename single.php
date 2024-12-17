<?php

ini_set( "display_errors", 1 );
ini_set( "display_startup_errors", 1 );
error_reporting( E_ALL );

include( "includes/Translation.php" );
include( "includes/Utils.php" );

$translate = new Translation();
$utils = new Utils();

$translation = $translate->getRandomRow();

if ( $translation ) {
    $post = $translate->postToBlueSky( $translation );
};

?>