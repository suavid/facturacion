<?php

include_once("../ConnectionManager.php");
header('Content-type:text/xml;charset=UTF-8');

$json = json_decode(stripslashes($_POST["_gt_json"]));
$pageNo = $json->{'pageInfo'}->{'pageNum'};
$pageSize = 10; //10 rows per page

$conManager = new ConManager();
$conManager->getConnection();

//to get how many records totally.
$sql = "select count(*) as cnt from orders";
$handle = mysql_query($sql);
$row = mysql_fetch_object($handle);
$totalRec = $row->cnt;

//make sure pageNo is inbound
if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))) {
    $pageNo = 1;
}

//pageno starts with 1 instead of 0
$sql = "select * from orders limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;

echo $sql . "-" . $pageNo . "-" . $pageSize;

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

$xml->addChild("cnt", $totalRec);
echo $xml->asXML();
?>