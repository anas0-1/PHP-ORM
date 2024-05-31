<?php
// test-update-table.php

require_once 'ORM.php';
require_once 'Product.php';

$orm = new ORM('products', 'Product');
$orm->updateTable();

echo "Table updated successfully.\n";
?>