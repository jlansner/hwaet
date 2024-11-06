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
}
?>