<?php
App::import("Component", "UrgSermon.BaseSermonComponent");
class SermonSeriesComponent extends BaseSermonComponent {
    function build($widget_id) {
        parent::build($widget_id);
        $this->set_sermon_series($this->sermon);
    }
}

