<?php

/*
 * examples/mysql/loaddata.php
 * 
 * This file is part of EditableGrid.
 * http://editablegrid.net
 *
 * Copyright (c) 2011 Webismymind SPRL
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://editablegrid.net/license
 */



/**
 * This script loads data from the database and returns it to the js
 *
 */

/**
 * fetch_pairs is a simple method that transforms a mysqli_result object in an array.
 * It will be used to generate possible values for some columns.
 */
function fetch_pairs($mysqli, $query) {
    if (!($res = $mysqli->query($query)))
        return FALSE;
    $rows = array();
    while ($row = $res->fetch_assoc()) {
        $first = true;
        $key = $value = null;
        foreach ($row as $val) {
            if ($first) {
                $key = $val;
                $first = false;
            } else {
                $value = $val;
                break;
            }
        }
        $rows[$key] = $value;
    }
    return $rows;
}

/*
  // Database connection
  $mysqli = mysqli_init();
  $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
  $mysqli->real_connect(HOST,USER,PASSWORD,DATABASE );

  // create a new EditableGrid object
  $grid = new EditableGrid();

  $grid->addColumn('id', 'ID', 'integer', NULL, false);
  $grid->addColumn('name', 'Name', 'string');
  $grid->addColumn('firstname', 'Firstname', 'string');
  $grid->addColumn('age', 'Age', 'integer');
  $grid->addColumn('height', 'Height', 'float');
  $grid->addColumn('id_continent', 'Continent', 'string' , fetch_pairs($mysqli,'SELECT id, name FROM continent'),true);
  $grid->addColumn('id_country', 'Country', 'string', fetch_pairs($mysqli,'SELECT id, name FROM country'),true );
  $grid->addColumn('email', 'Email', 'email');
  $grid->addColumn('freelance', 'Freelance', 'boolean');
  $grid->addColumn('lastvisit', 'Lastvisit', 'date');
  $grid->addColumn('website', 'Website', 'string');

  $result = $mysqli->query('SELECT *, date_format(lastvisit, "%d/%m/%Y") as lastvisit FROM demo LIMIT 100');
  $mysqli->close();

  // send data to the browser
  $grid->renderXML($result);
 */
?>