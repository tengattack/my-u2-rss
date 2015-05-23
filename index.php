<?php

define('IN_MYU2RSS', 1);

require_once('./config.inc.php');
require_once('./common.php');

function getTorrentInfo($torrenthtml)
{
  if (!preg_match('/<table class="torrentname".*?>([\s\S]*?)<\/table>/', $torrenthtml, $mat)) {
    return;
  }

  if (!preg_match('/<td class="rowfollow nowrap"><span title="(.*?)"/i', $torrenthtml, $mat2)) {
    return;
  }

  $torrentname = $mat[1];
  if (!preg_match('/<a .*?href="details\.php\?id=(\d+).*?".*?>(.*?)<\/a>/i', $torrentname, $matches)) {
    return;
  }

  $timestamp = strtotime($mat2[1]);

  $torrent = [
    'id' => $matches[1],
    'title' => $matches[2],
    'pubDate' => date('r', $timestamp)
  ];

  if (preg_match_all('/<td class=".*?overflow-control">([\s\S]*?)<\/td>/', $torrentname, $matches2)) {
    if (count($matches2[1]) === 2) {
      // 置顶
      if (strpos($matches2[1][0], '<img class="sticky"') !== false) {
        $torrent['sticky'] = true;
      }
      // 优惠信息
      if (preg_match('/<img .*?class="(.+?)".*?/', $matches2[1][1], $matches3)) {
        $pro = $matches3[1];
        if (strpos($pro, 'pro_') === 0) {
          $pro = substr($pro, 4, strlen($pro) - 4);
        }
        $promo = [
          'up' => 1,
          'down' => 1,
          'type' => $pro
        ];

        if ($pro === 'custom') {
          if (preg_match_all('/<img class="arrow(up|down)".*?<b>(.+?)X<\/b>/', $torrentname, $matches4)) {
            for ($i = 0; $i < count($matches4[0]); $i++) {
              //$promo[$matches4[1][$i]] = floatval($matches4[2][$i]);
              if ($matches4[1][$i] === 'up') {
                $promo['up'] = floatval($matches4[2][$i]);
              } else if ($matches4[1][$i] === 'down') {
                $promo['down'] = floatval($matches4[2][$i]);
              }
            }
          }
        } else {
          if (strpos($pro, '2up') !== false) {
            $promo['up'] = 2;
          }
          if (strpos($pro, 'free') !== false) {
            $promo['down'] = 0;
          }
          if (strpos($pro, '30pctdown') !== false) {
            $promo['down'] = 0.3;
          }
          if (strpos($pro, '50pctdown') !== false) {
            $promo['down'] = 0.5;
          }
        }

        $torrent['promotion'] = $promo;
      }
    }
  }

  return $torrent;
}

function getTorrentList()
{
  $html = httpGet('https://u2.dmhy.org/torrents.php');
  if (!$html) {
    return [];
  }

  $torrents = [];
  preg_match_all('/<tr>([\s\S]*?<a href="viewsnatches\.php.*?<\/td>.*?)<\/tr>/', $html, $mat);

  for ($i = 0; $i < count($mat[0]); $i++) {
    //$mat[0][$i] = $mat[1][$i];
    $t = getTorrentInfo($mat[1][$i]);
    if ($t) {
      $torrents[] = $t;
    }
  }

  return $torrents;
}

function applyFilters($torrents)
{
  global $config;

  $f = isset($_GET['filters']) ? $_GET['filters'] : 'normal';
  if (!isset($config['filters'][$f])) {
    return [];
  }

  $filters = $config['filters'][$f];

  $ts = [];

  foreach ($torrents as $t) {
    if (isset($filters['sticky'])) {
      $t_sticky = isset($t['sticky']) ? $t['sticky'] : false;
      if ($t_sticky != $filters['sticky']) {
        continue;
      }
    }
    if (isset($filters['promotion'])) {
      $promo = $filters['promotion'];
      $cats = [ 'up', 'down' ];
      $r = false;

      for ($i = 0; $i < count($cats); $i++) {
        if (isset($promo[$cats[$i]])) {
          $curpro = $promo[$cats[$i]];
          if (preg_match('/[0-9\.]+/', $curpro, $matches, PREG_OFFSET_CAPTURE)) {

            $op = trim(substr($curpro, 0, $matches[0][1]));
            $val = floatval($matches[0][0]);
            $t_val = isset($t['promotion']) ? $t['promotion'][$cats[$i]] : 1;
            $r = false;

            switch ($op) {
            case '>':
              $r = ($t_val > $val);
              break;
            case '>=':
              $r = ($t_val >= $val);
              break;
            case '<':
              $r = ($t_val < $val);
              break;
            case '<=':
              $r = ($t_val <= $val);
              break;
            case '==':
              $r = ($t_val == $val);
              break;
            case '!=':
              $r = ($t_val != $val);
              break;
            }

            //echo $cats[$i] . ' ' . $t_val . ' ' . $op . ' ' . $val . ' ' . $r . "\n";

            if (!$r) {
              break;
            }
          }
        }
      }

      if (!$r) {
        continue;
      }
    }
    $ts[] = $t;
  }

  return $ts;
}

$torrents = applyFilters(getTorrentList());

header('Content-Type: text/xml; charset=utf-8'); //application/rss+xml
include('rss.templ.xml')

?>
