<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" /> 
<script type="text/javascript" src="teletext-editor.js"></script>
<script type="text/javascript" src="teletext-viewer2.js"></script>
<script type="text/javascript">
function init_frames() {

	// Create a new viewer:
	var viewer = new Viewer();

	// Make it the active viewer so it receives keypresses:
	active_viewer = viewer;

	// Initialise the viewer, placing it in the canvas with HTML
	// ID 'frame'.
	viewer.init_frame("frame");
	viewer._viewer_settimeformat(32, 'h:m/s');
	viewer._viewer_loadpages()
}

</script>
<title>view-tf</title>
<style type="text/css">
/* Colour palette, dark to light: #222, #343, #797, #9b9 */


/* layout ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ */

html { 
  	-webkit-text-size-adjust: 100%;
	    -ms-text-size-adjust: 100%;
}
body { 
	margin: 0;
	background-color: #000;
}
div#a {
	display: table;
	position: fixed;
	top: 0; left: 0;
	height: 100%;
	width: 100%;
	margin: 0;
	padding: 0;
	border-collapse: collapse;
}
div.b { 
	display: table-cell;
	vertical-align: middle;
	text-align: center;
	padding: 0;
	margin: 0;
	position: relative;
}

/* teletext canvas ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~  */

#canvasframe {
	display: inline-block;
	-moz-border-radius: 10px;
	    border-radius: 10px;
	background-color: #000;
	padding: 7px 10px;
}
#canvasframe canvas {
	/* The canvas should have no padding */
	background-color: #000;
	margin: 0;
	cursor: none;
	position: relative;
	z-index: 1;
}
</style>
<link rel="apple-touch-icon" sizes="180x180" href="favicons/apple-touch-icon.png">
<link rel="icon" type="image/png" href="favicons/favicon-32x32.png" sizes="32x32">
<link rel="icon" type="image/png" href="favicons/favicon-16x16.png" sizes="16x16">
<link rel="manifest" href="favicons/manifest.json">
<link rel="mask-icon" href="favicons/safari-pinned-tab.svg" color="#2d89ef">
<meta name="theme-color" content="#000000">
</head>
<body onload="init_frames();">

<div id="a">
	<!--<image src="lines.png" style="position: absolute; left: 0px; top; 0px; z-index: 2; width: 100%; height: 100%;" />-->
<div class="b">
<div id="canvasframe" style="position: relative">
<canvas id="frame" width="960" height="1000" style="left: 0px; top: 0px; width: 576px; height: 500px;"></canvas>
</div></div></div>
<div style="display: none">
<?php
/*
	Backend server for view-tf 
	Written by Nathan J Dane, 2019
*/
	
	$dir="/home/pi/ceefax/";
	if (!is_dir($dir)) return;
	$dh=opendir($dir);

	while (($file = readdir($dh)) !== false)
	{
		// Filter the tti files
		if (endsWith($file,".tti") || endsWith($file,".TTI"))
		{
			$data=loadtti("/home/pi/ceefax/$file");
			
			echo '<a data-red="'."$data[2]".'"
		data-green="'."$data[3]".'"
		data-blue="'."$data[4]".'"
		data-yellow="'."$data[5]".'"
		href="#1:'."$data[0]".'">'."$data[1]".'</a>'."\r\n";
		}
	}
	
	function loadtti($file)
	{
		$tti=file($file);
		$data=getData($tti);
		$tti=conv2raw($tti);
		$tti=conv8bit($tti);
		//file_put_contents("test.raw",$tti);
		return array_merge(array(conv2hash($tti)),$data);
	}
	function conv2hash($raw)
	{
		$binary='';
		$base64='';
		foreach($raw as $line)
		{
			$line=rtrim($line);
			$line=str_pad($line,40);
			$line=substr($line,0,40);
			//echo "\r\n$line\r\n";
			$line=str_split($line);
			foreach($line as $char)
			{
				$out=decbin(ord($char));
				if(strlen($out)>7)
					$out=substr($out,1);
				$out=str_pad($out,7,'0',STR_PAD_LEFT);
				//echo $out." ";
				$binary.=$out;
			}
		}
		$binary=str_split($binary,8);
		foreach($binary as $char)
		{
			$base64.=chr(bindec($char));
		}
		
		$default = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
		$custom  = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_";
		$encoded = strtr(base64_encode($base64), $default, $custom);
		$encoded=rtrim($encoded,'=');
		return $encoded;
	}
	function conv2raw($tti)
	{
		$page=array();
		$page=array_fill(0,25,"                                        \r\n");
		foreach ($tti as $line)
		{
			if(!strncmp($line,"OL,",3))
			{
				$line2=substr($line,3);
				$ln=strstr($line2,',',true);
				$line=substr($line,(strpos($line,",",3)+1));
				$page[$ln]=$line;
			}
		}
		ksort($page);	// Make sure the page is in order!
		return $page;
	}
	function conv8bit($tti)
	{
		$tti=str_replace("A","�",$tti);	// Alpha Red
		$tti=str_replace("B","�",$tti);	// Alpha Green
		$tti=str_replace("C","�",$tti);	// Alpha Yellow
		$tti=str_replace("D","�",$tti);	// Alpha Blue
		$tti=str_replace("E","�",$tti);	// Alpha Magenta
		$tti=str_replace("F","�",$tti);	// Alpha Cyan
		$tti=str_replace("G","�",$tti);	// Alpha White
		$tti=str_replace("H","�",$tti);	// Alpha Black
		$tti=str_replace("I","�",$tti);	// 
		$tti=str_replace("J","�",$tti);	// 
		$tti=str_replace("K","�",$tti);	// 
		$tti=str_replace("L","�",$tti);	//
		$tti=str_replace("M","�",$tti);	// 
		$tti=str_replace("N","�",$tti);	// 
		$tti=str_replace("O","�",$tti);	// 
		$tti=str_replace("P","�",$tti);	// 
		$tti=str_replace("Q","�",$tti);	// Graphics Red
		$tti=str_replace("R","�",$tti);	// Graphics Green
		$tti=str_replace("S","�",$tti);	// Graphics Yellow
		$tti=str_replace("T","�",$tti);	// Graphics Blue
		$tti=str_replace("U","�",$tti);	// Graphics Magenta
		$tti=str_replace("V","�",$tti);	// Graphics Cyan
		$tti=str_replace("W","�",$tti);	// Graphics White
		$tti=str_replace("X","�",$tti);	// Graphics Black
		$tti=str_replace("Y","�",$tti);	// 
		$tti=str_replace("Z","�",$tti);	// Separated Graphics
		$tti=str_replace("[","�",$tti);	// 
		$tti=str_replace('\\',"�",$tti);	// 
		$tti=str_replace("]","�",$tti);	// 
		$tti=str_replace("^","�",$tti);	// 
		$tti=str_replace("_","�",$tti);	// 
		$tti=str_replace("`","�",$tti);	// 
		$tti=str_replace(" ","",$tti);	// 
		return $tti;
	}
	function endsWith($haystack, $needle)
	{
		return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
	}
	function getData($tti)
	{
		foreach ($tti as $line)
		{
			if(!strncmp($line,"FL",2))
			{
				$FL=str_getcsv($line);
				array_shift($FL);
			}
			if(!strncmp($line,"PN",2))
			{
				$PN=str_getcsv($line);
				array_shift($PN);
				$PN=substr($PN[0],0,3);
			}
		}
		return array_merge(array($PN),$FL);
	}
?>
</div>
</body>
</html>
