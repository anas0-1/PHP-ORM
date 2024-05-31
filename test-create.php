<?php
// test-create-product-table.php

require_once 'ORM.php';
require_once 'Product.php';

$orm = new ORM('products', 'Product');
$orm->createTable();

echo "Products table created\n";
