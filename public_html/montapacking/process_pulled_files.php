<?php
/**
 *  Copyright (c) 2010 Netsend
 *  Released under the MIT license.
 *
 *  Checks the inbox and for each file found calls the right script to process it.
 *  If the script exits with status code 0, move the file to processed
 *  Else move the file to the error direcotry.
 */
#mail('nuicarterrors@gmail.com','process_pulled_files.php','');
require(dirname(__FILE__) . '/config.php');

$action = array('ASR' => 'sales_order_result.php',
                'ASM' => 'sales_order_result.php',
                'ASE' => 'article_info_result.php',
                'ASK' => 'article_info_stock.php',
               );
			   
$local_dir = $config['loc_pull_dir'];

///
// first check if processed and inbox directories have right permissions
 
// check inbox permissions
if (!chdir(dirname(__FILE__) . '/' . $local_dir))
{
 #mail('nuicarterrors@gmail.com',"can not change local working directory to $local_dir\n","can not change local working directory to $local_dir\n");
  fwrite(STDERR, "can not change local working directory to $local_dir\n");
  exit(1);
}

if (!is_writable(getcwd()))
{
#mail('nuicarterrors@gmail.com', "local_dir: $local_dir not writable\n", "local_dir: $local_dir not writable\n");
  fwrite(STDERR, "local_dir: $local_dir not writable\n");
  exit(1);
}

if (!is_readable(getcwd()))
{
#mail('nuicarterrors@gmail.com',  "local_dir: $local_dir not readable\n",  "local_dir: $local_dir not readable\n");
  fwrite(STDERR, "local_dir: $local_dir not readable\n");
  exit(1);
}

$processed = dirname(__FILE__) . '/' . $config['processed'];

// check processed permissions
if (!is_writable($processed))
{
  fwrite(STDERR, "processed: {$config['processed']} not writable\n");
  exit(1);
}

if (!is_executable($processed))
{
  fwrite(STDERR, "processed: {$config['processed']} not executable\n");
  exit(1);
}

$error_dir = dirname(__FILE__) . '/' . $config['error_dir'];

// check error_dir permissions
if (!is_writable($error_dir))
{
  fwrite(STDERR, "error_dir: {$config['error_dir']} not writable\n");
  exit(1);
}

if (!is_executable($error_dir))
{
  fwrite(STDERR, "error_dir: {$config['error_dir']} not executable\n");
  exit(1);
}

$d = dir(getcwd());
while (false !== ($filename = $d->read()))
{
  if (!is_file($filename))
  {
    continue;
  }

  $key = substr($filename, 0, 3);

  if (array_key_exists($key, $action))
  {
    $script = $action[substr($filename, 0, 3)];
  }
  else
  {
    fwrite(STDERR, "No action/script found for $filename\n");
    continue;
  }

  $ret = 1;
  $out = array();
#mail('nuicarterrors@gmail.com',  "exec php " . dirname(__FILE__) ." ".$script.' '.escapeshellarg($filename),"exec php " . dirname(__FILE__) . " " .$script);

  exec("php " . dirname(__FILE__) . "/$script " . escapeshellarg($filename), $out, $ret);

  if ($ret === 0)
  {
    rename($filename, "../{$config['processed']}/$filename");
  }
  else
  {
    rename($filename, "../{$config['error_dir']}/$filename");
  }
}

// mail any customers about shipped orders
exec("php " . dirname(__FILE__) . "/notify_customer.php", $out, $ret);
