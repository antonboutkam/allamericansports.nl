<?php
/**
 *  Copyright (c) 2010 Netsend
 *  Released under the MIT license.
 *
 *  Download (pull) all files in $config['ftp_pull_dir'] to $config['loc_pull_dir'].
 *  Delete each succesfully downloaded file from ftp.
 */

require(dirname(__FILE__) . '/config.php');

$local_dir = $config['loc_pull_dir'];


if (!chdir(dirname(__FILE__) . '/' . $local_dir))
{
  fwrite(STDERR, "can not change local working directory to $local_dir\n");
  exit(1);
}
#mail('nuicarterrors@gmail.com',"CHDIR ".dirname(__FILE__) . '/' . $local_dir,print_r($_SERVER,true));

if (!is_writable(getcwd()))
{
  fwrite(STDERR, "local_dir: $local_dir not writable\n");
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
$contents = ftp_nlist($conn_id, $config['ftp_pull_dir']);
#mail('nuicarterrors@gmail.com',"Contents of remote ftp dir ".$config['ftp_pull_dir'],print_r($contents,true));

#$contents = ftp_rawlist($conn_id, '/'.$config['ftp_pull_dir'].'/');
# fwrite(STDOUT, print_r($contents,true));

// try to change the directory to $ftp_pull_dir
if (!ftp_chdir($conn_id, $config['ftp_pull_dir']))
{
	#mail('nuicarterrors@gmail.com',"Couldn't change directory",print_r($_SERVER,true));
	fwrite(STDERR, "Couldn't change directory\n");
	exit(1);
}

if (empty($contents))
{
  if ($config['debug'])
  {
	#mail('nuicarterrors@gmail.com',"No new files on ftp",print_r($_SERVER,true));
    fwrite(STDOUT, "No new files on ftp\n");
  }
  exit;
}

foreach ($contents as $filename)
{
  $filename = basename($filename);

  // try to download $server_file and save to $local_file
  if (ftp_get($conn_id, $filename, $filename, FTP_BINARY))
  {
    fwrite(STDOUT, "$filename downloaded\n");
	#mail('nuicarterrors@gmail.com',"$filename downloaded success!",print_r($_SERVER,true));
    ftp_delete($conn_id, $filename);
    mail('allamericansportserrors@gmail.com','ftp download',file_get_contents($filename));
  }
  else
  {
    #mail('nuicarterrors@gmail.com','allamericansports.nl -> montapacking',print_r($_SERVER,true));
    #fwrite(STDERR, "Error downloading $filename\n");
  }
}
