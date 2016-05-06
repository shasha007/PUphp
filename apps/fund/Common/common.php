<?php

function getRange($id){
    $arr = array(1=>'校级','院级','团支部','其他');
    if(isset($id)){
     return $arr[$id];   
    }else{
        return $arr;
    }
}
?>
