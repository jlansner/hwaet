<?php

class Database {
  public function openConnection() {
      // Create connection
      $mysqli = new mysqli( SERVERNAME, USERNAME, PASSWORD, DBNAME );
      
      // Check connection
      if ($mysqli->connect_error) {
        die("Connection failed: " . $conn->connect_error);
      }
      
      return $mysqli;
  }

  public function getAdminFields() {
      $mysqli = $this->openConnection();
      
      $formatSql = "SELECT id, name FROM format";
      
      $formatResult = $mysqli->query( $formatSql );
      
      if ( $formatResult->num_rows > 0 ) {
        $formats = $formatResult->fetch_all( MYSQLI_ASSOC );
      }
      
      $languageSql = "SELECT id, name, name_eng FROM language";
      
      $languageResult = $mysqli->query( $languageSql );
      
      if ( $languageResult->num_rows > 0 ) {
        $languages = $languageResult->fetch_all( MYSQLI_ASSOC );
      }
      
      $countrySql = "SELECT id, name FROM country";
      
      $countryResult = $mysqli->query( $countrySql );
      
      if ( $countryResult->num_rows > 0 ) {
        $counrties = $countryResult->fetch_all( MYSQLI_ASSOC );
      }

      
      $mysqli->close();

      return array(
          "languages" => $languages,
          "formats" => $formats,
          "countries" => $counrties
      );
  }

  public function saveRecord( $rawPost ) {
      $post = (object) $rawPost;
      
      if ( $post->password !== "biteme" ) {
          return false;
      }
      $mysqli = $this->openConnection();
      
      $sql = "INSERT INTO `author` ( `first_name`, `middle_name`, `last_name`, `additional_name` ) VALUES ( '$post->first_name', '$post->middle_name', '$post->last_name', '$post->additional_name' );" . PHP_EOL;
      $sql .= "INSERT INTO `book` ( `title`, `year`, `publisher`, `language_id`, `url`, `format_id`, `country_id`, `author_id` ) VALUES ( '$post->title', $post->year, '$post->publisher', $post->language, '$post->url', $post->format, $post->country, LAST_INSERT_ID() );" . PHP_EOL;
      $sql .= "INSERT INTO `translations` ( `translation`, `translation_eng`, `not_translated`, `book_id` ) VALUES ( '$post->translation', '$post->translation_eng', '$post->not_translated', LAST_INSERT_ID() );" . PHP_EOL;
      
      $result = $mysqli->multi_query( $sql );
      
      $mysqli->close();
      
      return $result;
  }
}
?>