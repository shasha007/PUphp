<?php
function getGrowCategory($id){
    if(intval($id) == 0){
        return '';
    }
    $cats = D('GrowCategroy')->__getCategory();
    $result = $cats[$id]['title'];
    return $result;
}
?>
