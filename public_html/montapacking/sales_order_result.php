<?php
/**
 *  Copyright (c) 2010 Netsend
 *  Released under the MIT license.
 *
 *  Process a sales order result file or sales order status file (containing shipped articles).
 *  ASR........dat and/or ASM........dat
 *
 *  Updates the docdata_stat to 'accepted', 'denied' or 'shipped' and docdata_sent to the current time.
 */
require(dirname(__FILE__) . '/include.php');
require(dirname(__FILE__) . '/config.php');

function usage()
{
  return "usage: " . basename(__FILE__) . " filename
  
filename      xml document containing docdata sales order import result structure\n";
}


if (empty($argv[1]))
{
  fwrite(STDERR, usage());
  exit(1);
}

$file = realpath($argv[1]);

if (empty($file) || !is_file($file))
{
  fwrite(STDERR, "{$argv[1]} not a valid file\n");
  exit(1);
}

$dom = new SimpleXMLElement(file_get_contents($file));

$i = 0;
foreach ($dom as $sales_order_status)
{
  $i++;

  switch ($sales_order_status->ORDER_HEADER->ORDER_STATUS)
  {
    case 'S': $status = 'shipped';  break;
    case 'A': $status = 'accepted'; break;
    case 'D': $status = 'denied';   break;
  }

  $order_nr = $sales_order_status->ORDER_HEADER->ORDER_NR;

  $query = 'UPDATE as_orders
               SET docdata_stat = "' . $status . '"
             WHERE id = "' . $link->real_escape_string($order_nr) . '"';

  $result = $link->query($query);

  foreach ($sales_order_status->ORDER_LINE as $order_line)
  {
    $sequence_nr      = $order_line->ORDER_LINE_SEQUENCE_NR;
    $quantity_shipped = $order_line->QUANTITY_SHIPPED;
    $shipping_date    = $order_line->SHIPPING_DATE;
    $tracktrace_nr    = $order_line->TRACKTRACE_NR;
    $transported_by   = $order_line->TRANSPORTED_BY;
    $product_id       = $order_line->ARTICLE_NR;
    $ean              = $order_line->EANCODE;

    $query = 'UPDATE as_order_products
                 SET docdata_quantity_shipped = "' . $link->real_escape_string($quantity_shipped) . '"
                   , docdata_shipping_date    = "' . $link->real_escape_string($shipping_date) . '"
                   , docdata_tracktrace_nr    = "' . $link->real_escape_string($tracktrace_nr) . '"
                   , docdata_transported_by   = "' . $link->real_escape_string($transported_by) . '"
               WHERE order_id                 = "' . $link->real_escape_string($order_nr) . '"
                 AND product_id               = "' . $link->real_escape_string($product_id) . '"
                 AND ean                      = "' . $link->real_escape_string($ean) . '"
                 AND docdata_sequence_nr      = "' . $link->real_escape_string($sequence_nr) . '"';

    $result = $link->query($query);
  }
}

if ($config['debug'])
{
  fwrite(STDOUT, "PROCESSED\n");
}
