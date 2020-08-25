    <!DOCTYPE html>

    <html xmlns="http://www.w3.org/1999/xhtml">

    <head>

    	<title>Exact Online - XML Service example page</title>

    	<script type="text/javascript" src='https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js?lang=xml&amp;skin=sons-of-obsidian'></script>

    </head>

    <body style="padding:20px;">

    <?php

    $baseurl = "https://start.exactonline.nl";

    $username = "<username>";

    $password = "<password>";

    $applicationkey = "{00000000-0000-0000-0000-000000000000}"; /* The application key with or without curly braces */

    $division = "<division code>";  /* Check the result of the first call to XMLDivisions.aspx to see all available divisions */

    $cookiefile = "cookie.txt";

    $crtbundlefile = "cacert.pem"; /* this can be downloaded from http://curl.haxx.se/docs/caextract.html */

    /* Logging in */

    $header[1] = "Cache-Control: private";

    $header[2] = "Connection: Keep-Alive";

    /* Init, don't terminate until you're completely done with this session */

    $ch = curl_init();

    /* Set all options */

    curl_setopt($ch, CURLOPT_POST, 1);

    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);

    curl_setopt($ch, CURLOPT_CAINFO, $crtbundlefile);

    curl_setopt($ch, CURLOPT_POSTFIELDS, array("_UserName_"=>"$username", "_Password_"=>"$password"));

    echo "<ol>";

    echo "<li><h3>Set the active division to ".$division." using ClearSession.aspx</h3></li>";

    /* Set the active division */

    $url = "$baseurl/docs/ClearSession.aspx?Division=$division&Remember=3";

    curl_setopt($ch, CURLOPT_URL, $url);

    curl_exec($ch);

    /* Get the list of available divisions (also known as administrations). The administration with attribute Current="True" is the active division. */

    $url = "$baseurl/docs/XMLDivisions.aspx";

    curl_setopt($ch, CURLOPT_URL, $url);

    $result = curl_exec($ch);

    echo "<li><h3>Check if the current division is ".$division." in the response from XMLDivisions.aspx</h3>";

    echo "<p>Response:</p>";

    echo "<pre class='prettyprint lang-xml linenums'>".htmlentities(replaceBOM($result))."</pre>";

    echo "</li>";

    /* Upload an xml file */

    $topic = "Invoices";

    $url = "$baseurl/docs/XMLUpload.aspx?Topic=$topic&ApplicationKey=$applicationkey";

    $filename = "Invoices.xml";

    	

    if (file_exists($filename)) {

    	curl_setopt($ch, CURLOPT_URL, $url);

    	/* Send the xml along with the request */

    	$fp = fopen($filename, "r");

    	$xml = fread($fp, filesize($filename));

    	curl_setopt($ch, CURLOPT_POSTFIELDS, utf8_encode($xml));

    	$result = curl_exec($ch);

    	

    } else {

    	$result = $filename." file is not found!";

    }

    echo "<li><h3>Upload Invoices.Xml via XMLUpload.aspx then check the response.</h3>";

    echo "<p>Response:</p>";

    echo "<pre class='prettyprint lang-xml linenums'>".htmlentities(replaceBOM($result))."</pre><br>";

    echo "</li>";

    echo "</ol>";

    /* Finally close as we're finished with this session */

    curl_close($ch);

    /* remove ï»¿(Byte Order Mark) from the response */

    function replaceBOM($value){

    	return preg_replace('/\x{EF}\x{BB}\x{BF}/','',$value);

    }

    ?>

    </body>

    </html>
