<?php
App::uses("BaseSermonComponent", "UrgSermon.Controller/Component");
class SermonSeriesComponent extends BaseSermonComponent {
    function build_widget() {
        parent::build_widget();
        $this->set_sermon_series($this->sermon);
    }
}

