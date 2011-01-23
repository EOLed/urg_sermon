<?php 
    if ($error != null)
        echo __("errors." . strtolower($model) . ".$field.$error", true);
?>
