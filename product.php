<?php
// Product.php

class Product
{
    public $id;
    public $name;
    public $price;

    public function __construct($name, $price, $id = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
    }
}
