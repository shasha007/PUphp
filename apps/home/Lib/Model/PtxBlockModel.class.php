<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//查询block表里面的数据
    class PtxBlockModel extends Model {
        public function getPtxBlock($map=array('isdel'=>0), $limit = 10, $page = 1,$order='id DESC'){
            $offset = ($page - 1) * $limit;
            $list = $this->where($map)->order($order)->limit("$offset,$limit")->select();
            return json_encode($list);
        }
        
        public function delBlock(){
            
        }
    }
?>
