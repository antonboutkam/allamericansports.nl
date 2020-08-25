<?php
/**
 *  Copyright (c) 2010 Netsend
 *  Released under the MIT license.
 *
 *  Process an article info stock file.
 *  ASK........dat
 *
 *  Updates as_products.docdata_stat to 'accepted' and stock to the given absolute quantity.
 */
require(dirname(__FILE__) . '/include.php');
require(dirname(__FILE__) . '/config.php');
require(dirname(__FILE__) . '/../config/site.php');

function usage()
{
  return "usage: " . basename(__FILE__) . " filename
  
filename      xml document containing docdata stock structure\n";
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

$unknown_products = array();

$i = 0;
foreach ($dom as $stock_info)
{
  foreach ($stock_info->ARTICLE_INFO as $article_info)
  {
    $i++;

    $ean      = $article_info->ARTICLE_NR;
    $quantity = $article_info->QUANTITY;

    // first check if we have the product in the database
    $query = 'SELECT *
                FROM as_products
               WHERE ean = "' . $link->real_escape_string($ean) . '"';

    $result = $link->query($query);
	
	
    if ($result->num_rows < 1)
    {
      $unknown_products[] = array('ean' => $ean, 'quantity' => $quantity);
    }
    else
    {
      $prod = $result->fetch_object();
	  
	  $query = 'UPDATE as_products
                   SET stock        = "' . $link->real_escape_string($quantity) . '"
                     , docdata_stat = "accepted"
                 WHERE ean          = "' . $link->real_escape_string($ean) . '"';

      $result = $link->query($query);
	  
	  /** Update cache, by ean number
	  *
	  */
	  fopen('http://'.$_SERVER['HTTP_HOST'].'/cachecontrol/docdata/'.$prod->id,'r')
    }
  }
}

if (!empty($unknown_products))
{
  $email = '';

  foreach ($unknown_products as $product)
  {
    $email .= "EAN:      {$product['ean']}\n";
    $email .= "QUANTITY: {$product['quantity']}\n";
    $email .= "\n";
  }

  if ($config['debug'])
  {
    fwrite(STDOUT, var_export($email, true));
  }
  else
  {
    mail($config['email'], 'Nieuw bij DocData', $email);
  }
}

if ($config['debug'])
{
  fwrite(STDOUT, "PROCESSED $i articles\n");
}
