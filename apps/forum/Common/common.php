<?php

function getSchoolTitle($sid) {
    return M('school')->getField('title', 'id='.$sid);
}

?>
