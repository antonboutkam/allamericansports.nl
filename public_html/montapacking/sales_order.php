<?php
/**
 *  Copyright (c) 2010 Netsend
 *  Released under the MIT license.
 *
 *  Generate a sales order xml-file of orders that have a docdata_stat value of unknown.
 *
 *  Updates the docdata_stat to 'waiting' and docdata_sent to the current time.
 */
require(dirname(__FILE__) . '/include.php');
require(dirname(__FILE__) . '/config.php');

function usage()
{
  return "usage: " . basename(__FILE__) . " [-f]
  
-f      write output to file ASO........dat instead of stdout\n";
}

$to_file = false;

if (!empty($argv[1]))
{
  if ($argv[1] === '-f')
  {
    $to_file = true;
  }
  else
  {
    fwrite(STDERR, usage());
    exit(1);
  }
}


/**
 * Find all orders that are not in sync with docdata 
 * New orders, with docdata_stat unknown and payment received
 * Changed orders, with docdata_stat waiting or accepted and status canceled, refunded or error
 */
/*$query = 'SELECT *
            FROM as_orders o
           WHERE o.docdata_stat = "unknown" AND (o.status="payment_received" OR o.status="canceled")
           ORDER BY o.id';*/
		   
$query = 'SELECT *
            FROM as_orders o
           WHERE (o.docdata_stat = "unknown" AND o.status="payment_received")
		    OR (o.docdata_stat = "unknown" AND o.docdata_action="delete")
           ORDER BY o.id';

$result = $link->query($query);

/**
 * Create XML string and XML document using the DOM 
 */
$dom = new DomDocument('1.0'); 
$dom->formatOutput = true;
$orders = $dom->appendChild($dom->createElement('ORDERS')); 

$order_ids = array();

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

  //add root - <order> 
  $sales_order  = $orders->appendChild($dom->createElement('SALES_ORDER')); 
  $order_header = $sales_order->appendChild($dom->createElement('ORDER_HEADER')); 

  $order_header->appendChild($dom->createElement('ORDER_NR'))->appendChild($dom->createTextNode($row['id']));
  $order_header->appendChild($dom->createElement('ORDER_DATE'))->appendChild($dom->createTextNode(dd_date($row['created_at'])));

  switch ($row['docdata_action'])
  {
    case 'new':    $status = 'N'; break;
    case 'change': $status = 'C'; break;
    case 'delete': $status = 'D'; break;
    default:       $status = 'N'; break;
  }

  $order_header->appendChild($dom->createElement('ORDER_STAT'))->appendChild($dom->createTextNode($status));
  //$order_header->appendChild($dom->createElement('ORDER_TYPE'))->appendChild($dom->createTextNode('?'));
  $order_header->appendChild($dom->createElement('ORDER_TYPE'))->appendChild($dom->createTextNode('WEB'));
  $order_header->appendChild($dom->createElement('SPLIT_ORDER'))->appendChild($dom->createTextNode('N'));
  $order_header->appendChild($dom->createElement('CUST_ID'))->appendChild($dom->createTextNode('AS'));

  $cust_information = $order_header->appendChild($dom->createElement('CUST_INFORMATION'));

  if (!empty($row['organization']))
  {
    $cust_information->appendChild($dom->createElement('DELIVERY_COMPANY_NAME'))->appendChild($dom->createTextNode($row['organization']));
  }

  $cust_information->appendChild($dom->createElement('DELIVERY_NAME'))->appendChild($dom->createTextNode($row['shipping_firstname'] . ' ' . $row['shipping_familyname']));
  $cust_information->appendChild($dom->createElement('DELIVERY_ADDRESS_1'))->appendChild($dom->createTextNode($row['shipping_address']));
  $cust_information->appendChild($dom->createElement('DELIVERY_HOUSENR_1'))->appendChild($dom->createTextNode($row['shipping_housenr']));
  $cust_information->appendChild($dom->createElement('DELIVERY_ZIP'))->appendChild($dom->createTextNode($row['shipping_postal_code']));
  $cust_information->appendChild($dom->createElement('DELIVERY_CITY'))->appendChild($dom->createTextNode($row['shipping_city']));
  $cust_information->appendChild($dom->createElement('DELIVERY_COUNTRY'))->appendChild($dom->createTextNode($row['shipping_country']));
  $cust_information->appendChild($dom->createElement('DELIVERY_PHONE'))->appendChild($dom->createTextNode($row['phonenumber']));

  if (!empty($row['vat_number']))
  {
    $cust_information->appendChild($dom->createElement('DELIVERY_VAT_NR'))->appendChild($dom->createTextNode($row['vat_number']));
  }

  $cust_information->appendChild($dom->createElement('DELIVERY_EMAIl'))->appendChild($dom->createTextNode($row['emailaddress']));

  if (!empty($row['organization']))
  {
    $cust_information->appendChild($dom->createElement('INVOICE_COMPANY_NAME'))->appendChild($dom->createTextNode($row['organization']));
  }

  $cust_information->appendChild($dom->createElement('INVOICE_NAME'))->appendChild($dom->createTextNode($row['firstname'] . ' ' . $row['familyname']));
  $cust_information->appendChild($dom->createElement('INVOICE_ADDRESS_1'))->appendChild($dom->createTextNode($row['billing_address']));
  $cust_information->appendChild($dom->createElement('INVOICE_HOUSENR_1'))->appendChild($dom->createTextNode($row['billing_housenr']));
  $cust_information->appendChild($dom->createElement('INVOICE_ZIP'))->appendChild($dom->createTextNode($row['billing_postal_code']));
  $cust_information->appendChild($dom->createElement('INVOICE_CITY'))->appendChild($dom->createTextNode($row['billing_city']));
  $cust_information->appendChild($dom->createElement('INVOICE_COUNTRY'))->appendChild($dom->createTextNode($row['billing_country']));

  if (!empty($row['vat_number']))
  {
    $cust_information->appendChild($dom->createElement('INVOICE_VAT_NR'))->appendChild($dom->createTextNode($row['vat_number']));
  }

  $cust_information->appendChild($dom->createElement('PREFF_LANGUAGE'))->appendChild($dom->createTextNode($row['language']));

  $shipping_info = $order_header->appendChild($dom->createElement('SHIPPING_INFO'));

  $shipping_info->appendChild($dom->createElement('EXPRESS_YN'))->appendChild($dom->createTextNode('N'));
  //$shipping_info->appendChild($dom->createElement('SHIPMENT_CODE'))->appendChild($dom->createTextNode('?'));
  $shipping_info->appendChild($dom->createElement('SHIPMENT_CODE'))->appendChild($dom->createTextNode('UPS'));
  $shipping_info->appendChild($dom->createElement('SHIPPING_COST_INC_VAT'))->appendChild($dom->createTextNode($row['shipping_method_costs']));

  $price_info = $order_header->appendChild($dom->createElement('PRICE_INFO'));
  $price_info->appendChild($dom->createElement('VAT_PERCENTAGE_HIGH'))->appendChild($dom->createTextNode('19.00'));
  $price_info->appendChild($dom->createElement('CURRENCY'))->appendChild($dom->createTextNode('EUR'));

  $discount = floatval($row['discount_credits']) + floatval($row['discount_action_code']);

  if (!empty($discount))
  {
    $price_info->appendChild($dom->createElement('ORDER_DISCOUNT_INC_VAT'))->appendChild($dom->createTextNode($discount));
  }

  if (!empty($row['used_action_code']))
  {
    $price_info->appendChild($dom->createElement('ORDER_DISCOUNT_DESCRIPTION'))->appendChild($dom->createTextNode($row['used_action_code']));
  }

  $query = 'SELECT *
              FROM as_order_products aop
             WHERE aop.order_id = "' . $link->real_escape_string($order_id) . '"
             ORDER BY aop.id';

  $products = $link->query($query);

  $j = 0;
  while ($product_row = $products->fetch_assoc())
  {
    $j++;

    // encode everyting in utf8
    foreach ($product_row as $col_name => $col_val)
    {
      $product_row[$col_name] = utf8_encode($col_val); 
    }

    $order_line = $order_header->appendChild($dom->createElement('ORDER_LINE'));

    $seq = str_pad($j, 3, '0', STR_PAD_LEFT);
    $order_line->appendChild($dom->createElement('ORDER_LINE_SEQUENCE_NR'))->appendChild($dom->createTextNode($seq));
    $order_line->appendChild($dom->createElement('QUANTITY'))->appendChild($dom->createTextNode($product_row['count']));

    $article_info = $order_line->appendChild($dom->createElement('ARTICLE_INFO'));
    $article_info->appendChild($dom->createElement('ARTICLE_NR'))->appendChild($dom->createTextNode($product_row['product_id']));
    $article_info->appendChild($dom->createElement('ARTICLE_DESCRIPTION'))->appendChild($dom->createTextNode($product_row['description']));

    $article_price_info = $order_line->appendChild($dom->createElement('ARTICLE_PRICE_INFO'));
    $article_price_info->appendChild($dom->createElement('UNIT_PRICE_INC_VAT'))->appendChild($dom->createTextNode($product_row['price']));

    $query = 'UPDATE as_order_products
                 SET docdata_sequence_nr = "' . $j . '"
               WHERE id = "' . $link->real_escape_string($product_row['id']) . '"';
    $link->query($query);
  }
}

// Clean up
$result->close();

// update database, set orders docdata_sent date to current timestamp and docdata_stat to waiting
if (!empty($order_ids))
{
  $xml = $dom->saveXML();

  // save XML as string or file 
  if (!$to_file)
  {
    header('Content-Type: application/xml');
    echo $xml;
  }
  else
  {
    // check inbox permissions
    if (!chdir(dirname(__FILE__) . '/' . $config['loc_push_dir']))
    {
      fwrite(STDERR, "can not change local working directory to {$config['loc_push_dir']}\n");
      exit(1);
    }

    if (!is_writable(getcwd()))
    {
      fwrite(STDERR, "local_dir: {$config['loc_push_dir']} not writable\n");
      exit(1);
    }

    $seq = substr(time(), 3, 9);

    $filename = "ASO$seq.dat";

    if (file_put_contents($filename, $xml))
    {
      fwrite(STDOUT, "$filename written in {$config['loc_push_dir']}\n");
    }
    else
    {
      fwrite(STDERR, "Could not write xml file\n");
      exit(1);
    }
  }

  $query = 'UPDATE as_orders
               SET docdata_sent = NOW()
                 , docdata_stat = "waiting"
             WHERE id IN (' . implode(',', $order_ids) . ')';

  $result = $link->query($query);
}
