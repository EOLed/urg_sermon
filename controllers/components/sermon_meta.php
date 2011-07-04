<?php
App::import("Component", "UrgSermon.BaseSermon");
class SermonMetaComponent extends BaseSermonComponent {
    function build($widget_id) {
        parent::build($widget_id);
        $this->set_attachments($this->sermon);
    }

    function bindModels() {
        parent::bindModels();
        $this->bind_attachments();
    }
}

