<?php
/**
 *  Copyright (c) 2010 Netsend
 *  Released under the MIT license.
 *
 *  Process an article info import result file. ASE........dat
 *
 *  Updates the docdata_stat to 'accepted' or 'denied' in case of a new or an updated article.
 *  Deletes an article if the delete is accepted by DocData.
 */
require(dirname(__FILE__) . '/include.php');
require(dirname(__FILE__) . '/config.php');

function usage()
{
  return "usage: " . basename(__FILE__) . " filename
  
filename      xml document containing docdata article info import result structure\n";
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
#mail('nuicarterrors@gmail.com',"PRODUCT XML",file_get_contents($file));
$dom = new SimpleXMLElement(file_get_contents($file));
#mail('nuicarterrors@gmail.com',"PRODUCT XML DOM",print_r($dom,true));
$i = 0;
foreach ($dom as $article_import_result)
{
  $i++;
  // Montapacking
  switch ($article_import_result->ORDER_STATUS)
  {
    case 'A': $status = 'accepted'; break;
    case 'D': $status = 'denied'; break;
  }
  // Docdata
  switch ($article_import_result->STATUS)
  {
    case 'A': $status = 'accepted'; break;
    case 'D': $status = 'denied'; break;
  }

  $id = $article_import_result->ARTICLE_NR;
  
  $message = '';
  if ($status=='denied')
  {
    $message = $article_import_result->MESSAGE;
  }

  /**
   * Determine what action is accepted or denied.
   */
  $query = 'SELECT docdata_action
              FROM as_products
             WHERE id = "' . $link->real_escape_string($id) . '"
               AND docdata_stat = "waiting"';

  $result = $link->query($query);

  $row = $result->fetch_assoc();

  if ($status === 'accepted' && $row['docdata_action'] === 'delete')
  {
    //disabled, because we do not want products to be deleted by docdata
	//$query = 'DELETE FROM as_products
     //               WHERE id = "' . $link->real_escape_string($id) . '"';
  }
  else
  {
    $query = 'UPDATE as_products
                 SET docdata_stat = "' . $status . '",
				 docdata_message = "' . $message . '"
               WHERE id = "' . $link->real_escape_string($id) . '"';
	#mail('nuicarterrors@gmail.com',"DOCDATA UPDATE STATUS",$query);			   
  }

  $result = $link->query($query);

   /**
  * Update cache for this product
  */
  
  fopen('http://'.$_SERVER['HTTP_HOST'].'/cachecontrol/docdata/'.$id,'r');

  
 
  
  
}
