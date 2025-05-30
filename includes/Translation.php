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
        
        $baseSql = BASE_SQL;
        $sql = $baseSql . ' and hwaets.modified < ( SELECT DATE_SUB(NOW(), INTERVAL 5 DAY) )
        ORDER BY ( hwaets.skeet_count + 1 ) * RAND()
        LIMIT 1';
        
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
        
        return $post;
    }

}
?>