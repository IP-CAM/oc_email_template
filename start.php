<?php
require_once 'Class_LetterTester.php';

$orderId = 473; //here set an existing order number

$test = new LetterTester();
$test->generateOrderEmail($orderId);    //see the output folder and change the template
?>