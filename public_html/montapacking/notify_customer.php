<?php
/**
 *  Copyright (c) 2010 Netsend
 *  Released under the MIT license.
 *
 *  Sent out email to customer for all as_orders.docdata_stat = shipped.
 *
 *  Updates the docdata_stat to 'notified'.
 */
require(dirname(__FILE__) . '/include.php');
require(dirname(__FILE__) . '/config.php');
define('BASEPATH', dirname(__FILE__));
require(dirname(__FILE__) . '/../system/libraries/Smarty_parser.php');
require(dirname(__FILE__) . '/../config/site.php');

function usage()
{
  return "usage: " . basename(__FILE__) . "\n";
}

if (!empty($argv[1]))
{
  fwrite(STDERR, usage());
  exit(1);
}


/**
 * Find all orders that are shipped.
 */
$query = 'SELECT *
            FROM as_orders
           WHERE docdata_stat = "shipped"
           ORDER BY id';

$result = $link->query($query);

/**
 * Create an email with order details.
 */

$i = 0;
while ($row = $result->fetch_assoc())
{
  $i++;

  $order_id = intval($row['id']);
  $order_ids[] = $order_id;

  // encode everyting in utf8
  foreach ($row as $col_name => $col_val)
  {
      $row[$col_name] = utf8_encode($col_val); 
  }

  $smarty = new Smarty;

  $smarty->compile_dir = dirname(__FILE__) . '/../system/cache';

  $subject = 'Bestelling verzonden ' . $row['order_number'];

  $smarty->assign('firstname', $row['firstname']);
  $smarty->assign('familyname', $row['familyname']);
  $smarty->assign('shipping_date', date("d-m-Y"));
  $smarty->assign('order_number', $row['order_number']);
  $smarty->assign('subject', $subject);
  $smarty->assign('site_name', $config['site_name']);
  $smarty->assign('site_url', $config['site_url']);

  $template = dirname(__FILE__) . '/../views/checkout/order_shipped_mail.php';

  $email = $smarty->fetch($template);

  if ($config['debug'])
  {
    fwrite(STDOUT, var_export($email, true));
  }
  else
  {
    // To send HTML mail, the Content-type header must be set
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

    // Additional headers
    $headers .= "From: {$config['site_name']} <{$config['site_email']}>\r\n";
    $headers .= "Bcc: <{$config['site_email']}>\r\n";

    mail($row['emailaddress'], $subject, $email, $headers);
  }

  $query = 'UPDATE as_orders
               SET docdata_stat = "notified"
             WHERE id IN (' . implode(',', $order_ids) . ')';

  $link->query($query);
}

if ($config['debug'])
{
  fwrite(STDOUT, "MAILED about $i shipped orders\n");
}
