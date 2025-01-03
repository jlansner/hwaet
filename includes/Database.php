<?php

class MyDB extends mysqli {
  // (You could set defaults for the params here if you want
  //  i.e. $host = 'myserver', $dbname = 'myappsdb' etc.)
  public function __construct($host = NULL, $username = NULL, $dbname = NULL, $port = NULL, $socket = NULL) {
    parent::__construct( $host, $username, $dbname, $port, $socket );
    $this->set_charset( "utf8mb4" );
  } 
} 

class Database {
  public function openConnection() {
      // Create connection
      mysqli_report( MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );
      $conn = new MyDB( SERVERNAME, USERNAME, PASSWORD, DBNAME );
      
      // Check connection
      if ( $conn->connect_error ) {
        die( "Connection failed: " . $conn->connect_error );
      }
      
      return $conn;
  }

  public function getAdminFields() {
      $conn = $this->openConnection();
      
      $formatSql = "SELECT id, name FROM format";
      
      $formatResult = $conn->query( $formatSql );
      
      if ( $formatResult->num_rows > 0 ) {
        $formats = $formatResult->fetch_all( MYSQLI_ASSOC );
      }
      
      $languageSql = "SELECT id, name, name_eng FROM language";
      
      $languageResult = $conn->query( $languageSql );
      
      if ( $languageResult->num_rows > 0 ) {
        $languages = $languageResult->fetch_all( MYSQLI_ASSOC );
      }
      
      $countrySql = "SELECT id, name, name_eng FROM country";
      
      $countryResult = $conn->query( $countrySql );
      
      if ( $countryResult->num_rows > 0 ) {
        $counrties = $countryResult->fetch_all( MYSQLI_ASSOC );
      }

      $typeSql = "SELECT id, name FROM type";
      
      $typeResult = $conn->query( $typeSql );
      
      if ( $typeResult->num_rows > 0 ) {
        $types = $typeResult->fetch_all( MYSQLI_ASSOC );
      }

      
      $conn->close();

      return array(
          "languages" => $languages,
          "formats" => $formats,
          "countries" => $counrties,
          "types" => $types
      );
  }

  public function saveRecord( $rawPost ) {
      $post = new stdClass();

      foreach ( $rawPost as $key => $val ) {
        $post->$key = trim( $val );
      }
      
      if ( $post->password !== ADMIN_PASS ) {
          return false;
      }
      $conn = $this->openConnection();
      
      $sql = "INSERT INTO `author` ( `first_name`, `middle_name`, `last_name`, `additional_name`, `name_transliteration` ) VALUES ( '$post->first_name', '$post->middle_name', '$post->last_name', '$post->additional_name', '$post->name_transliteration' );" . PHP_EOL;
      $sql .= "INSERT INTO `book` ( `title`, `title_transliteration`, `year`, `publisher`, `language_id`, `url`, `format_id`, `country_id`, `author_id` ) VALUES ( '$post->title', `$post->title_transliteration', $post->year, '$post->publisher', $post->language, '$post->url', $post->format, $post->country, LAST_INSERT_ID() );" . PHP_EOL;
      $sql .= "INSERT INTO `translations` ( `translation`, `translation_eng`, `transliteration`, `not_translated`, `book_id` ) VALUES ( '$post->translation', '$post->translation_eng', '$post->transliteration', '$post->not_translated', LAST_INSERT_ID() );" . PHP_EOL;
      
      $result = $conn->multi_query( $sql );
      
      $conn->close();
      
      return $result;
  }
}
?>