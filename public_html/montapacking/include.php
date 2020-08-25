<?php
require(dirname(__FILE__) . '/../config/ace/config.php');

/**
 * format date to docdata date format, YYYYMMDD
 */
function dd_date($date)
{
  $date = strtotime($date);
  return strftime('%Y%m%d', $date);
}

$link = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($link->connect_error)
{
  header('HTTP/1.0 500 Internal Server Error');
  die('Unable to connect or select database!');
}

if (!$link->set_charset('utf8'))
{
  die('Error loading character set utf8');
}
