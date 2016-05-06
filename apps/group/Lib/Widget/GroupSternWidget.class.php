<?php

/**
 * 社团星级
 *
 * @author daniel <desheng.young@gmail.com>
 */
class GroupSternWidget extends Widget {

    public function render($data) {
        $content = $this->renderFile("GroupStern", $data);
        return $content;
    }

}