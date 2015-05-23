<?php

if (!defined('IN_MYU2RSS')) {
  exit();
}

date_default_timezone_set('Asia/Shanghai');

function httpGet($url)
{
  global $config;

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

  curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
  curl_setopt($ch, CURLOPT_COOKIE, $config['cookies']);

  $output = curl_exec($ch);

  // 检查是否有错误发生
  if (curl_errno($ch)) {
    header("HTTP/1.1 500 Internal Server Error");
    echo 'cURL ERROR: ' . curl_error($ch);
    exit();
  }

  curl_close($ch);

  return $output;
}

if (!isset($_GET['key']) || $_GET['key'] !== $config['key']) {
  header("HTTP/1.1 403 Forbidden");
  exit();
}

?>
