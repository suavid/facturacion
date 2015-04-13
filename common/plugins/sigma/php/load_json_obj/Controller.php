<?php

include_once("../ConnectionManager.php");
header('Content-type:text/javascript;charset=UTF-8');
$conManager = new ConManager();
$conManager->getConnection();
$sql = "select * from orders";
$handle = mysql_query($sql);

$retArray = array();
while ($row = mysql_fetch_object($handle)) {
    $retArray[] = $row;
}
$data = json_encode($retArray);
$ret = "{data:" . $data . ",\n";
$ret .= "recordType : 'object'}";
echo $ret;
?>