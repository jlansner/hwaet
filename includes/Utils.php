<?php

class Utils {
    public function getOptionFields( $options ) {
        $values = $options[ "values" ];
        $idField = isset( $options[ "id" ] ) ? $options[ "id" ] : "id";
        $nameField = isset( $options[ "name" ] ) ? $options[ "name" ] : "name";
        $optionsFields = "";

        foreach( $values as $value ) {
            $id = $value[ $idField ];
            $name = $value[ $nameField ];
            $optionsFields .= '<option value="' . $id . '">' . $name . "</option>" . PHP_EOL;
        }

        return $optionsFields;
    }

    public function isStringWithContent( $string ) {
        $type = gettype( $string );
        $numberTypes = array( "integer", "double" );
    
        if ( $type === "string" ) {
          return strlen( trim( $string ) ) > 0;
        }
    
        if ( in_array( $type, $numberTypes ) ) {
          return true;
        }
    
      return false;
    }

    public function getCount( $array, $prop ) {
    
      $mappedArray = array();
      
      foreach( $array as $obj ) {
        array_push( $mappedArray, $obj[ $prop ] );
      }

      $uniqueArray = array_unique( $mappedArray);

      return count( $uniqueArray );
    }
}
?>