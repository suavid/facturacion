<?php

include_once("../ConnectionManager.php");
header('Content-type:text/xml;charset=UTF-8');
$conManager = new ConManager();
$conManager->getConnection();
$sql = "select * from orders";
$handle = mysql_query($sql);

$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><root/>");

while ($row = mysql_fetch_object($handle)) {
    $xmlrec = $xml->addChild("data");
    // loop through the record.
    foreach ($row as $key => $value) {
        $value = htmlentities($value, ENT_QUOTES, 'UTF-8');
        $xmlrec->addChild($key, $value);
    }
}

echo $xml->asXML();
?>