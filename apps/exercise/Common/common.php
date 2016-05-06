<?php

function getAlphnum($char,$array) {
    $char = strtolower($char);
    $index = array_search($char, $array);
    if($index === false){
        return $char;
    }
    return $index;
}
function douhao($str){
    return trim(str_replace('douhao', ',', $str));
}

?>
