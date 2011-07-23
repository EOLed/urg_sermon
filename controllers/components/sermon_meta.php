<?php
App::import("Component", "UrgSermon.BaseSermon");
App::import("Component", "UrgSermon.BaseSermonComponent");
class SermonMetaComponent extends BaseSermonComponent {
    function build_widget() {
        parent::build_widget();
        $this->set_attachments($this->sermon);
    }

    function bindModels() {
        parent::bindModels();
        $this->bind_attachments();
    }
}

