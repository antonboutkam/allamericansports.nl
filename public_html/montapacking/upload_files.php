<?php
/**
 *  Copyright (c) 2010 Netsend
 *  Released under the MIT license.
 *
 *  Upload (push) all files in $config['loc_push_dir'] to $config['ftp_push_dir'].
 *
 *  Moves files from $config['loc_push_dir'] to either $config['error_dir'] or
 *  $config['processed'] depending on ftp transfer exit status.
 */

require(dirname(__FILE__) . '/config.php');

$local_dir = $config['loc_push_dir'];

if (!chdir(dirname(__FILE__) . '/' . $local_dir))
{
  fwrite(STDERR, "can not change local working directory to $local_dir\n");
  exit(1);
}

if (!is_readable(getcwd()))
{
  fwrite(STDERR, "local_dir: $local_dir not readable\n");
  exit(1);
}


// set up basic connection
$conn_id = ftp_connect($config['ftp_server']);

// login with username and password
$login_result = ftp_login($conn_id, $config['ftp_name'], $config['ftp_pass']);

// check connection
if ((!$conn_id) || (!$login_result))
{
  fwrite(STDERR, "FTP connection to " . $config['ftp_server'] . " has failed\n");
  exit(1);
}

// turn passive mode on
ftp_pasv($conn_id, true);

// get contents of the current directory
$contents = ftp_nlist($conn_id, $config['ftp_push_dir']);

// try to change the directory to $ftp_push_dir
if (!ftp_chdir($conn_id, $config['ftp_push_dir']))
{
  fwrite(STDERR, "Couldn't change directory\n");
  exit(1);
}

$d = dir(getcwd());
while (false !== ($filename = $d->read()))
{
  if (!is_file($filename))
  {
    continue;
  }

  // try to upload $filename to the server
  if (ftp_put($conn_id, $filename, $filename, FTP_BINARY))
  {      
    rename($filename, "../{$config['processed']}/$filename");
    mail('allamericansportserrors@gmail.com','ftp upload',file_get_contents("../{$config['processed']}/$filename"));
  }
  else
  {
    rename($filename, "../{$config['error_dir']}/$filename");
    mail('allamericansportserrors@gmail.com','ftp upload error',file_get_contents("../{$config['error_dir']}/$filename"));
  }

  if (!empty($config['debug']))
  {
    fwrite(STDOUT, "$filename\n");
  }
}
