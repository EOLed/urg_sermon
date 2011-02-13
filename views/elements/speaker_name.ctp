<?php
/**
 * Returns the speaker name. Expects a variable called $sermon
 * containing a Sermon key and a Pastor key.
 */
$speaker = isset($sermon["Pastor"]) ? $sermon["Pastor"]["name"] : $sermon["Sermon"]["speaker_name"];
echo $speaker;
?>


