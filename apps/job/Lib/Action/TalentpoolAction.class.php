<?php

/*
 * 人才库
 * 
 */
class TalentpoolAction extends Action{
    
public function index(){
    $m = new model();
    $m->query('alter table ts_job_publish_posts change jobdescripition jobdescripition longtext; ');
}
}
?>
