<?php
if (!defined('IN_MYU2RSS')) {
  exit();
}

define('USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2398.0 Safari/537.36');

$config = [
  'url' => 'http://example.com/my-u2-rss/',
  'key' => 's8aEhJMQmVlV5GjE',
  'cookies' => 'nexusphp_u2=[your_cookies];',
  'passkey' => '[your_passkey]',
  'filters' => [
    'normal' => [
      'sticky' => false,
      'promotion' => [ 'up' => '>=2' ] //'up' => '', 'down' => ''
    ]
  ]
];

 ?>
