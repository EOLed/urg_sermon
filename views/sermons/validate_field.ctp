<?php 
    if ($error != null) {
        $model = strtolower($model);
        echo __(($model != "sermon" ? "sermons." : "") . $error, true);
    }
?>
