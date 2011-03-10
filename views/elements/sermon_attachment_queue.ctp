<?php
$contents = "";
if (isset($attachments)) {
    $counter = 0;
    foreach ($attachments as $attachment) {
        $filename = $attachment["Attachment"]["filename"];
        $link = $this->Html->link($filename, "/urg_sermon/" . 
                $this->requestAction("/urg_sermon/sermons/get_webroot_folder/$filename") . 
                "/$sermon_id/$filename", array("id"=>"ExistingAttachmentQueueLink"));
        $link .= $this->Html->link(
                $this->Html->image("/img/icons/x.png", array("style"=>"height: 16px; float: right")),
                $this->Html->url("/urg_sermon/sermons/delete_attachment/" . 
                        $attachment["Attachment"]["id"]),
                array("escape" => false)
        );
        $contents .= $this->Html->tag("li", $link, 
                array("id"=>"ExistingAttachmentQueueListItem" . $counter));

        $counter++;
    }
}

echo $this->Html->div("", $this->Html->tag("ul", $contents, array("id"=>"attachment-queue")));