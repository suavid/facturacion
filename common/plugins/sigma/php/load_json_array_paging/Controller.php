<?php

include_once("../ConnectionManager.php");
header('Content-type:text/javascript;charset=UTF-8');

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


//page index starts with 1 instead of 0
$sql = "select * from orders limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
$handle = mysql_query($sql);
$retArray = array();
while ($row = mysql_fetch_row($handle)) {
    $retArray[] = $row;
}


$data = json_encode($retArray);
$ret = "{data:" . $data . ",\n";
$ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
$ret .= "recordType : 'array'}";
echo $ret;
?>