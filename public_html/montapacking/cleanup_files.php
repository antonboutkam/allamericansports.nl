<?php
/**
 *  Copyright (c) 2010 Netsend
 *  Released under the MIT license.
 *
 *  Delete all files in $local_dir and any copies on the ftp.
 */

require(dirname(__FILE__) . '/config.php');

$local_dir = $config['processed'];

if (!chdir(dirname(__FILE__) . '/' . $local_dir))
{
  fwrite(STDERR, "can not change local working directory to $local_dir\n");
  exit(1);
}

if (!is_writable(getcwd()))
{
  fwrite(STDERR, "local_dir: $local_dir not writable\n");
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
$ftplist = ftp_nlist($conn_id, $config['ftp_pull_dir']);

// try to change the directory to $ftp_pull_dir
if (!ftp_chdir($conn_id, $config['ftp_pull_dir']))
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

  if (is_array($ftplist))
  {
	  if (in_array($filename, $ftplist))
	  {
		if (!($config['debug'] || ftp_delete($conn_id, $filename)))
		{
		  fwrite(STDERR, "Could not delete $filename on ftp\n");
		}
	  }
  }

  if (!unlink($filename))
  {
    fwrite(STDERR, "Could not delete $filename locally\n");
  }
}
