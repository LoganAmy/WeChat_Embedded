<?php
header("Content-type:text/html;charset=utf-8");
$channel = new SaeChannel();
$url = $channel->createChannel('bananapi',3600);
echo $url;
?>