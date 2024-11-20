<?php

include( "definitions.php" );
include( "Database.php" );
include( "BlueskyApi.php" );

class Translation {

    public function getFullName( $translation, $sortByLastName = false ) {
        $firstName = trim( $translation[ 'first_name' ] );
        $middleName = trim( $translation[ "middle_name" ] );
        $lastName = trim( $translation[ "last_name" ] );
        $additionalNameConnector = $sortByLastName ? ", and " : " and ";
        $additonalName = trim( $translation[ "additional_name" ] );
        $fullName = "";
        
        if ( $sortByLastName ) {
            $fullName = $lastName;

            if ( strlen( $firstName ) ) {
                $fullName .= ", " . $firstName;

                if ( strlen( $middleName ) ) {
                    $fullName .= " " . $middleName;
                }
            }
        } else {
            $fullName = $firstName;

            if ( strlen( $middleName ) ) {
                $fullName .= " " . $middleName;
            }

            if ( strlen( $lastName ) ) {
                $fullName .= " " . $lastName;
            }
        }

        if ( strlen( $additonalName ) ) {
            $fullName .= $additionalNameConnector . $additonalName;
        }

        return $fullName;
    }

    private function completeTranslation( $translation ) {
    
        $translation[ "cssStyle" ] = strtolower( str_replace( " ", "_",  $translation[ "languageInEnglish" ] ) );
        $translation[ "fullName" ] = $this->getFullName( $translation );
        $translation[ "fullNameByLast" ] = $this->getFullName( $translation, true );
        if ( $translation[ "not_translated" ] ) {
            $translation[ "translation" ] = "—";
        }

        return $translation;
    }

    public function getData() {
        $db = new Database();
        $conn = $db->openConnection();
        
        $baseSql = BASE_SQL;

        $sql = "$baseSql WHERE (translation IS NOT NULL and translation != '') OR not_translated = 1";
        
        $result = $conn->query( $sql );
        
        if ( $result->num_rows > 0 ) {
          $resultArray = $result->fetch_all( MYSQLI_ASSOC );
        }
        
        $conn->close();
    
        $translations = array_map( [ $this, "completeTranslation" ], $resultArray );
        
        return $translations;
    }

    function getRow( $id ) {
        $db = new Database();

        $conn = $db->openConnection();
    
        $baseSql = BASE_SQL;
        $sql = "$baseSql WHERE translations.id = " . $id;
        
        $result = $conn->query( $sql );
        
        if ( $result->num_rows > 0 ) {
          $resultArray = $result->fetch_array( MYSQLI_ASSOC );
        }
        
        $conn->close();
    
        return $resultArray;
    }

    function getRandomRow() {
        $db = new Database();
        
        $conn = $db->openConnection();
            
        $settingsSql = "SELECT name, properties FROM settings WHERE name = 'single'";
        
        $settingsResult = $conn->query( $settingsSql );
        
        if ( $settingsResult->num_rows > 0 ) {
          $resultArray = $settingsResult->fetch_array( MYSQLI_ASSOC );
          $properties = json_decode( $resultArray[ "properties" ], true );
        }
        
        $recentlyUsed = implode( ",", $properties[ "recentlyUsed" ] );
        
        $baseSql = BASE_SQL;
        $sql = "$baseSql WHERE translations.translation IS NOT NULL AND translations.translation <> '' AND translations.id NOT IN ( $recentlyUsed ) order by RAND() limit 1";
        
        $result = $conn->query( $sql );
        
        if ( $result->num_rows > 0 ) {
          $resultArray = $result->fetch_array( MYSQLI_ASSOC );
        }
        
        if ( count( $properties[ "recentlyUsed" ] ) >= RECENTLY_USED_COUNT ) {
            array_splice( $properties[ "recentlyUsed" ], 0, 1 );
        }
        
        array_push( $properties[ "recentlyUsed" ], (int)$resultArray[ "translationId" ] );
        
        $jsonProperties = json_encode( $properties );
        $updateSql = "UPDATE settings SET properties = '$jsonProperties' WHERE name = 'single'";
        
        $conn->query( $updateSql );
        
        $conn->close();
    
        return $resultArray;
    }
    
    public function postToBlueSky( $options ) {
        $bluesky = new BlueskyApi();
    
        try {
            $bluesky->auth( BS_HANDLE, BS_PASSWORD );
        } catch ( Exception $e ) {
            // TODO: Handle the exception however you want
        }
        
        $options[ "repo" ] = $bluesky->getAccountDid();
    
        $args = $this->generateBlueskyPost( $options );
        
        $data = $bluesky->request( 'POST', 'com.atproto.repo.createRecord', $args );
        
        return $data;
    }
    
    public function generateBlueskyPost( $translation ) {
        $translate = new Translation();
        $fullName = $translate->getFullName( $translation );
        
        $postText = '"' . $translation[ "translation" ] . '"';
        
        if ( strlen( trim( $translation[ "translation_eng" ] ) ) ) {
            $postText .= " (" . trim( $translation[ "translation_eng" ] ) . ")";
        }
        
        $postText .= PHP_EOL;
        
        $postText .= trim( $translation[ "title" ] ) . ', ' . $translation[ 'year' ];
        
        $postText .= PHP_EOL;
        
        $postText .= $fullName;
    
        if ( strlen ( trim( $translation[ "countryEmoji" ] ) ) ) {
            $postText .= " " . $translation[ "countryEmoji" ];
        }
        
        $languages = [ "en" ];
        
        if ( strlen( trim( $translation[ "code" ] ) ) && $translation[ "code" ] !== "en" ) {
            array_push( $languages, $translation[ "code" ] );
        }
        
        $options = [ 
            "postText" => $postText,
            "languages" => $languages,
            "url" => trim( $translation[ "url" ] )
        ];
    
        $post = [
            "collection" => "app.bsky.feed.post",
            "repo" => $translation[ "repo" ],
            "record" => [
                "text" => $options[ "postText" ],
                "langs" => $options[ "languages" ],
                "createdAt" => date( "c" ),
                '$type' => "app.bsky.feed.post",
            ]
        ];
        
        $url = $options[ "url" ];
        if ( $this->remoteFileExists( $url ) ) {
            
            $doc = new DOMDocument(); // Create a new DOMDocument
            libxml_use_internal_errors( true ); // Suppress errors for invalid HTML, if needed
            $doc->loadHTMLFile( $url ); // Load the HTML from the URL
            libxml_use_internal_errors( false ); // Restore error handling
     
            $xpath = new DOMXPath( $doc ); // Create a new DOMXPath object for querying the document
    
            // $html = $this->file_get_contents_curl( $url );
    
            // //parsing begins here:
            // $doc = new DOMDocument();
            // @$doc->loadHTML($html);
            // $nodes = $doc->getElementsByTagName('title');
            
            // //get and display what you need:
            // $title = $nodes->item(0)->nodeValue;
            
            // $metas = $doc->getElementsByTagName('meta');
    
            // $metaData = [];
            // foreach( $metas as $meta ) { 
            //     var_dump( $meta );
            //     if ( $meta->getAttribute('name') ) {
                    
            //   array_push( $metaData, array(
            //         'name' => $meta->getAttribute('name'),
            //         'content' => $meta->getAttribute('content')
            //     ) );
                    
            //     } else if ( $meta->getAttribute('property') ) {
            //   array_push( $metaData, array(
            //         'name' => $meta->getAttribute('property'),
            //         'content' => $meta->getAttribute('content')
            //     ) );
            //     }
            // }
    
            $metaData = get_meta_tags( $url );
    
            $title = $this->findMetaTag( "title", $metaData );
            
            $options[ "title" ] = $title;
            $options[ "description" ] = $this->findMetaTag( "description", $metaData );
            // $options[ "image" ] = findMetaTag( "image", $metaData );
        
            $external = [
                "uri" => $options[ "url" ],
                "title" => $options[ "title" ],
                "description" => $options[ "description" ]
                // "image" => $options[ "image" ]
            ];
    
            $post[ "embed" ] = [
                '$type' => "app.bsky.embed.external",
                "external" => $external
            ];
        }
        
        return $post;
    }

    private function remoteFileExists( $url ) {
        if ( !strlen( trim( $url ) ) ) {
            return false;   
        }
    
        $curl = curl_init( $url );
    
        //don't fetch the actual page, you only want to check the connection is ok
        curl_setopt( $curl, CURLOPT_NOBODY, true );
    
        //do request
        $result = curl_exec( $curl );
    
        $ret = false;
    
        //if request did not fail
        if ( $result !== false ) {
            //if request was ok, check response code
            $statusCode = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
    
            if ( $statusCode == 200 ) {
                $ret = true;   
            }
        }
    
        curl_close( $curl );
    
        return $ret;
    }

    private function findMetaTag( $tag, $array ) {
        $allSearchTerms = [
            "title" => [ "title", "name" ],
            "description" => [ "description" ],
            "image" => [ "image" ]
        ];
        
        $searchTerms = $allSearchTerms[ $tag ];
            $tagValue = "";
    
        foreach ( $array as $key=>$value ) {
            foreach( $searchTerms as $term ) {
                if ( strpos( $key, $term ) !== false) {
                    $tagValue = $value;
                }
    
                if ( $tagValue !== "" ) {
                    break;
                }
            }
        }
        
        return $tagValue;
    }

    private function file_get_contents_curl( $url ) {
        $ch = curl_init();
    
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
    
        $data = curl_exec( $ch );
        curl_close( $ch );
    
        return $data;
    }
}
?>