<!DOCTYPE html>
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include( "../includes/Translation.php" );
include( "../includes/Utils.php" );

$translate = new Translation();
$utils = new Utils();
$db = new Database();

$fields = $db->getAdminFields();

?>
<html>
<head>
    <title>Hwaet Admin</title>
    <script tyype=text/javascript">
        if (window.location.protocol == 'http:') {
            window.location.href = window.location.href.replace( 'http:', 'https:' );
        }
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js" integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="../css/main.css" />
</head>
<body>
    <?php 

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $save = $db->saveRecord( $_POST );
        
        if ( $save ) {
            echo "<p>Record added</p>";
        }
        else {
            echo "<p>Error adding record</p>";
            var_export( $save );
        }
    }
    ?>
    <form method="post">
        <p><label for="password">Password</label>
            <input type="password" id="password" name="password" />
        </p>
<p><label for="first_name">First Name</label>
<input type="text" id="first_name" name="first_name"></p>
<p><label for="middle_name">Middle Name</label>
<input type="text" id="middle_name" name="middle_name"></p>
<p><label for="last_name">Last Name</label>
<input type="text" id="last_name" name="last_name"></p>

<p><label for="additional_name">Additional Name</label>
<input type="text" id="additional_name" name="additional_name"></p>

<p><label for="title">Title</label>
<input type="text" id="title" name="title"></p>
<p><label for="year">Year</label>
<input type="text" id="year" name="year"></p>
<p><label for="publisher">Publisher</label>
<input type="text" id="publisher" name="publisher"></p>
<p><label for="language">Language</label>
<select id="language" name="language">
<?php
        echo $utils->getOptionFields( [ 
            "values" => $fields[ "languages" ] ,
            "name" => "name_eng"
            ] );
    ?>
    </select>
</p>
<p><label for="country">Country</label>
<select id="country" name="country">
<?php
        echo $utils->getOptionFields( [ "values" => $fields[ "countries" ] ] );
    ?>
</select>
</p>
<p><label for="url">URL</label>
<input type="text" id="url" name="url"></p>
<p><label for="format">Format</label>
<select id="format" name="format">
    <?php
        echo $utils->getOptionFields( [ "values" => $fields[ "formats" ] ] );
    ?>
</select>
</p>
<p><label for="translation">Translation</label>
<input type="text" id="translation" name="translation"></p>
<p><label for="translation_eng">Translation in English</label>
<input type="text" id="translation_eng" name="translation_eng"></p>

<p><label for="not_translated">No translation</label>
<input type="hidden" name="not_translated" value="0">
<input type="checkbox" id="not_translated" name="not_translated" value="1"></p>

<input type="submit" name="Submit" id="submitEntry" />
    </form>

</body>
</html>