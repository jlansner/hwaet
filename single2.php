<html>
<head>
    <meta charset="UTF-8">
</heaad>
<body>
    <pre>
<?php

ini_set( "display_errors", 1 );
ini_set( "display_startup_errors", 1 );
error_reporting( E_ALL );

include( "includes/Translation.php" );
$translate = new Translation();

if ( isset( $_GET[ "id"] ) ) {
    $translation = $translate->getRow ( $_GET[ "id" ] );
    $translations = [ $translation ];
}
else {
    $translations = $translate->getData();
}

foreach ( $translations as $translation ) {
    if ( $translation ) {
        $translation[ "repo" ] = "[repo]";
        $args = $translate->generateBlueskyPost( $translation );
        var_dump( $args );
        echo PHP_EOL . "------------" . PHP_EOL . PHP_EOL;
        // $post = postToBlueSky( $translation );
    };
}
?>
</pre>
</body>
</html>