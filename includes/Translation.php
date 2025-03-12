<?php

include( "definitions.php" );
include( "Database.php" );
include( "BlueskyApi.php" );

if ( class_exists( "Utils" ) ) {
    include( "Utils.php" );
}

class Translation {

    public function getFullName( $translation, $sortByLastName = false ) {
        $firstName = trim( $translation[ 'authorFirstName' ] );
        $middleName = trim( $translation[ "authorMiddleName" ] );
        $lastName = trim( $translation[ "authorLastName" ] );
        $transliteratedName = trim( $translation[ "authorTransliteration" ] );
        $additonalName = trim( $translation[ "authorAdditionalName" ] );
        $lastNameFirst = $translation[ "lastname_first" ] === "1";
        $additionalNameConnector = $sortByLastName && !$lastNameFirst ? ", and " : " and ";
        $fullName = "";
        
        if ( $lastNameFirst ) {
            $fullName = $lastName;

            if ( strlen( $firstName ) ) {
                $fullName .= " " . $firstName;

                if ( strlen( $middleName ) ) {
                    $fullName .= " " . $middleName;
                }
            }
        } else {
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
        }

        if ( strlen( $additonalName ) ) {
            $fullName .= $additionalNameConnector . $additonalName;
        }

        if ( $transliteratedName ) {
            $fullName .= " [" . $transliteratedName . "]";
        }

        return $fullName;
    }

    private function completeTranslation( $translation ) {
        $utils = new Utils();
    
        $translation[ "cssStyle" ] = strtolower( str_replace( " ", "_",  $translation[ "languageInEnglish" ] ) );
        $translation[ "fullName" ] = $this->getFullName( $translation );
        $translation[ "fullNameByLast" ] = $this->getFullName( $translation, true );
        
        if ( $utils->isStringWithContent( $translation[ "titleTransliteration" ] ) ) {
            $translation[ "title" ] .= " [" . trim( $translation[ "titleTransliteration" ] ) . "]";
        }

        return $translation;
    }

    public function getData() {
        $db = new Database();
        $conn = $db->openConnection();
                
        $result = $conn->query( BASE_SQL );
        
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
        $sql = "$baseSql AND hwaets.id = " . $id;
        
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
        $sql = "$baseSql and hwaet.modified < ( SELECT DATE_SUB(NOW(), INTERVAL 5 DAY) )";
        
        $result = $conn->query( $sql );
        
        if ( $result->num_rows > 0 ) {
          $resultArray = $result->fetch_array( MYSQLI_ASSOC );
        }
        
        $newSkeetCount = $resultArray[ "skeet_count" ] + 1;
        $translationId = $resultArray[ "translationId" ];
        
        $updateSql = "UPDATE hwaets SET skeet_count = $newSkeetCount WHERE id = $translationId" ;
        
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
        $utils = new Utils();
        $translate = new Translation();
        $fullName = $translate->getFullName( $translation );
        $title = trim( $translation[ "title" ] );
        if ( $utils->isStringWithContent( $translation[ "titleTransliteration" ] ) ) {
            $title .= " [" . trim( $translation[ "titleTransliteration" ] ) . "]";
        }
        
        $postText = '"' . $translation[ "translation" ] . '"';

        if ( $utils->isStringWithContent( $translation[ "translationTransliteration" ] ) ) {
            $postText .= " [" . trim( $translation[ "translationTransliteration" ] ) . "]";
        }

        if ( $utils->isStringWithContent( $translation[ "translationInEnglish" ] ) ) {
            $postText .= " (" . trim( $translation[ "translationInEnglish" ] ) . ")";
        }
        
        $postText .= PHP_EOL;
        
        $postText .=  $title . ', ' . $translation[ 'year' ];
        
        $postText .= PHP_EOL;
        
        $postText .= $fullName;
    
        if ( $utils->isStringWithContent( $translation[ "countryEmoji" ] ) ) {
            $postText .= " " . $translation[ "countryEmoji" ];
        }
        
        $languages = [ "en" ];
        
        if ( $utils->isStringWithContent( $translation[ "code" ] ) && $translation[ "code" ] !== "en" ) {
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
        $utils = new Utils();

        if ( !$utils->isStringWithContent( $url ) ) {
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