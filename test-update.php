<?php
// test-update-product.php

require_once 'ORM.php';
require_once 'Product.php';

$orm = new ORM('products', 'Product');

$product = new Product('Updated Product', 100, 'Description updated', 1);
$orm->update($product);

echo "Product updated\n";
