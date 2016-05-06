<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
    //查找同一个BLOCK里面的所有list数据
    class PtxListModel extends Model {
        public function getPtxList($block_id,$order='ordernum ASC'){
            if(empty($block_id)){
                return 'false';
            }
            
            $list = $this->where('block_id='.$block_id)->order($order)->select();
            return json_encode($list);
        }
       //获得某一条list数据 
        public function getOneList($id){
            if(empty($id)){
                return 'false';
            }
            $list=$this->where('id='.$id)->find();
            return json_encode($list);
        }
    }
?>
