<?php
$dbname = '643_db1';

if (!mysql_connect('wntapps20.weeversweb.nl', '643_u1', 'base933h;hy')) {
    echo 'Could not connect to mysql';
    exit;
}

$sql = "SHOW TABLES FROM $dbname";
$result = mysql_query($sql);

if (!$result) {
    echo "DB Error, could not list tables\n";
    echo 'MySQL Error: ' . mysql_error();
    exit;
}

while ($row = mysql_fetch_row($result)) {
    echo "Table: {$row[0]}\n";
	echo "<br/>";
}

mysql_free_result($result);
?>