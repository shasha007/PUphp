<?php

class UploadAttachWidget extends Widget {

    public function render($data) {
        $content = $this->renderFile("UploadAttach", $data);
        return $content;
    }

}

?>