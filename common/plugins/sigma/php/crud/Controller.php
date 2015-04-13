<?php

include_once("../ConnectionManager.php");
header('Content-type:text/javascript;charset=UTF-8');

$json = json_decode(stripslashes($_POST["_gt_json"]));
//$pageNo = $json->{'pageInfo'}->{'pageNum'};

$conManager = new ConManager();
$conManager->getConnection();


if ($json->{'action'} == 'load') {
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
} else if ($json->{'action'} == 'save') {
    $sql = "";
    $params = array();
    $errors = "";

    //deal with those deleted
    $deletedRecords = $json->{'deletedRecords'};
    foreach ($deletedRecords as $value) {
        $params[] = $value->order_no;
    }
    $sql = "delete from orders where order_no in (" . join(",", $params) . ")";
    if (mysql_query($sql) == FALSE) {
        $errors .= mysql_error();
    }

    //deal with those updated
    $sql = "";
    $updatedRecords = $json->{'updatedRecords'};
    foreach ($updatedRecords as $value) {
        $sql = "update `orders` set " .
                "`employee`='" . $value->employee . "', " .
                "`country`='" . $value->country . "', " .
                "`customer`='" . $value->customer . "', " .
                "`order2005`=" . $value->order2005 . ", " .
                "`order2006`=" . $value->order2006 . ", " .
                "`order2007`=" . $value->order2007 . ", " .
                "`order2008`=" . $value->order2008 . ", " .
                "`delivery_date`='" . $value->delivery_date . "' " .
                "where `order_no`=" . $value->order_no;
        if (mysql_query($sql) == FALSE) {
            $errors .= mysql_error();
        }
    }



    //deal with those inserted
    $sql = "";
    $insertedRecords = $json->{'insertedRecords'};
    foreach ($insertedRecords as $value) {
        $sql = "insert into orders (`employee`, `country`, `customer`, `order2005`,`order2006`, `order2007`, `order2008`, `delivery_date`) VALUES ('" .
                $value->employee . "', '" . $value->country . "', '" . $value->customer . "', '" . $value->order2005 . "', '" . $value->order2006 . "', '" . $value->order2007 . "', '" . $value->order2008 . "',  '" . $value->delivery_date . "')";
        if (mysql_query($sql) == FALSE) {
            $errors .= mysql_error();
        }
    }


    $ret = "{success : true,exception:''}";
    echo $ret;
}
?>