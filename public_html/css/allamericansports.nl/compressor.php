<?php
	function compress($buffer) {
		/* remove comments */
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
		/* remove tabs, spaces, newlines, etc. */
		$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
		return $buffer;
	}
	
	header('Content-type: text/css');
	$compressedFileName = 'compressed.css';
  
	$files = array('reset.css','style.css','../fancybox.css','cms.css');
	$regenerate = false;	
	if(!file_exists($compressedFileName)){
		$regenerate = true;	
		touch($compressedFileName);
	}
	
	foreach($files as $fileName)
		if(filemtime($compressedFileName)<=filemtime($fileName))
			$regenerate=true;

$regenerate=true;
	if($regenerate){
		$out .= '/* Generated on '.date("Y-m-d H:i:s").' */'.PHP_EOL;	
		foreach($files as $fileName){
		
			$out .= PHP_EOL.PHP_EOL.'/* '.$fileName.' */'.PHP_EOL;
			$out .= compress(file_get_contents($fileName));
		}	
		file_put_contents($compressedFileName,$out);
	}
	
	
	echo readfile($compressedFileName);
?>
