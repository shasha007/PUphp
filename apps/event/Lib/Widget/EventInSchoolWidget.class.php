<?php

class EventInSchoolWidget extends Widget {

    public function render($id=0) {
        is_array($id)? $province = $id['province']:intval($id) ;
        !empty($id['id'])?$id=$id['id']:'' ;
    	$data['provs'] = M('province')->order('short asc')->findAll();
    	if (!empty($province)) {
    		$data['check_province'] = $id['province'] ;
    		$data['citys'] = M('citys')->where('pid='.$id['province'])->select() ;
    	}
        if ($id > 0 ) {
            $data['areas'] = D('EventSchool2','event')->editbarSchool($id);
        }
        $content = $this->renderFile("EventInSchool", $data);
        return $content;
    }

}

?>