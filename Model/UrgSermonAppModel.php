<?php
App::import("Component", "Urg.Urg");
class UrgSermonAppModel extends AppModel {
    function log($msg, $type = LOG_ERROR) {
        $trace = debug_backtrace();
        parent::log("[" . $this->toString() . "::" . $trace[1]["function"] . "()] $msg", $type);
    }
}
