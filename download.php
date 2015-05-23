<?php

define('IN_MYU2RSS', 1);

require_once('./config.inc.php');
require_once('./common.php');

if (!isset($_GET['id'])) {
  exit();
}

$id = intval($_GET['id']);
$url = 'https://u2.dmhy.org/download.php?id=' . $id;

header('Content-Type: application/x-bittorrent;');
header('Content-Disposition: attachment; filename="[U2].' . $id . '.torrent"; charset=utf-8');

echo httpGet($url);

?>
