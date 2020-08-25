<?php
/**
 *  Copyright (c) 2010 Netsend
 *  Released under the MIT license.
 *
 *  Generate an article info xml-file of products that have a docdata_stat value of unknown.
 *
 *  Updates the docdata_stat to 'waiting' and docdata_sent to the current time.
 */
require(dirname(__FILE__) . '/include.php');
require(dirname(__FILE__) . '/config.php');
#mail('nuicarterrors@gmail.com',"article_info.php",'');
function usage()
{
  return "usage: " . basename(__FILE__) . " [-f]
  
-f      write output to file ASA........dat instead of stdout\n";
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
 * Find all products that are not in sync with docdata
 */
$query = 'SELECT *
            FROM as_products p
           WHERE p.docdata_stat = "unknown"
           ORDER BY p.id';
#mail('nuicarterrors@gmail.com',"FIND PRODUCTS NOT NI SYNC",$query);
$result = $link->query($query);

/**
 * Create XML string and XML document using the DOM 
 */
$dom = new DomDocument('1.0'); 
$dom->formatOutput = true;
$article_import = $dom->appendChild($dom->createElement('ARTICLE_IMPORT'));

$article_ids = array();

$i = 0;
while ($row = $result->fetch_assoc())
{
  $i++;

  $article_id = intval($row['id']);
  $article_ids[] = $article_id;
  // encode everyting in utf8
  foreach ($row as $col_name => $col_val)
  {
      $row[$col_name] = utf8_encode($col_val); 
  }

  // add root
  $article_info = $article_import->appendChild($dom->createElement('ARTICLE_INFO')); 

  $article_info->appendchild($dom->createElement('ARTICLE_NR'))->appendChild($dom->createTextNode($row['id']));
  $barcode_info = $article_info->appendChild($dom->createElement('BARCODE_INFO'));

  $barcode_info->appendChild($dom->createElement('EANCODE'))->appendChild($dom->createTextNode($row['ean']));

  $article_info->appendChild($dom->createElement('ARTICLE_DESCRIPTION'))->appendChild($dom->createTextNode($row['name_nl']));

  switch ($row['docdata_action'])
  {
    case 'new':    $status = 'N'; break;
    case 'change': $status = 'C'; break;
    case 'delete': $status = 'D'; break;
    default:       $status = 'N'; break;
  }

  $article_info->appendChild($dom->createElement('STATUS'))->appendChild($dom->createTextNode($status));

  $article_info->appendChild($dom->createElement('COUNTRY_OF_ORIGIN'))->appendChild($dom->createTextNode('NL'));
}

// Clean up
$result->close();

// update database, set products docdata_sent date to current timestamp and docdata_stat to waiting
if (!empty($article_ids))
{
  $query = 'UPDATE as_products
               SET docdata_sent = NOW()
                 , docdata_stat = "waiting"
             WHERE id IN (' . implode(',', $article_ids) . ')';

  #mail('nuicarterrors@gmail.com',"PRODUCT QUERY FOR XML TO DOCDATA",$query);
  
  $result = $link->query($query);

  $xml = $dom->saveXML();
  #mail('nuicarterrors@gmail.com','docdata_sent',$xml);  
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

    $filename = "ASA$seq.dat";
	#mail('nuicarterrors@gmail.com',"SENDING PRODUCTXML TO DOCDATA ($filename)",$xml);	
    if (file_put_contents($filename, $xml))
    {
      fwrite(STDOUT, "$filename written in {$config['loc_push_dir']}\n");
      exit(0);
    }
    else
    {
      fwrite(STDERR, "Could not write xml file\n");
      exit(1);
    }
  }
}
