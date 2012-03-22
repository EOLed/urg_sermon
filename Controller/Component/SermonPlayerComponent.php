<?php
App::uses("BaseSermonComponent", "UrgSermon.Controller/Component");
class SermonPlayerComponent extends BaseSermonComponent {
    function build_widget() {
        parent::build_widget();
        $this->set_attachments($this->sermon);
    }

    function bindModels() {
        parent::bindModels();
        $this->bind_attachments();
    }
}
