<?php

class TravelAreaModel extends Model {
    public function getAreaName($id){
        return $this->getField('name','id='.$id);
    }
}
?>
