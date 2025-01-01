<?php
require_once '../../vendor/autoload.php';
use QuickPay\QuickPay;
require_once "../includes/connect.php";

$customerData = json_decode(file_get_contents("php://input"), true);

$connection = db_connect($sqhost, $squser, $sqpass, $customerData["db"]);

$query = db_select("SELECT * FROM rentalperiod WHERE id = $customerData[id]", __FILE__ . " linje " . __LINE__);
$res = db_fetch_array($query);
$orderId = $res["order_id"];

if($orderId == null || $orderId == 0) {
    echo json_encode(["error" => "No order id found", "id" => $customerData["id"]]);
    exit;
}

$query = db_select("SELECT * FROM ordrer WHERE id = '$orderId'", __FILE__ . " linje " . __LINE__);
$res = db_fetch_array($query);
$amount = $res["sum"] * 1.25 * 100;

$query = db_select("SELECT apikey FROM rentalquickpay WHERE id = 1", __FILE__ . " linje " . __LINE__);
$res = db_fetch_array($query);
$apiKey = $res["apikey"];

$quickpay = new QuickPay(":$apiKey");
$quickpay->mode = 'sandbox';

$allowedChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

$shuffledChars = str_shuffle($allowedChars);

$length = rand(4, 20);
$randomString = substr($shuffledChars, 0, $length);

/* $amount = $customerData["price"] * 100; */

$sub = $quickpay->request->post('/payments', [
    'order_id' => $randomString,
    'currency' => 'DKK',
])->asArray();

$res = $quickpay->request->put("/payments/$sub[id]/link", [
    'amount' => $amount,
    "language" => "da",
    "deadline" => 180,
])->asArray();

// open link in a new window
echo json_encode(["url" => $res["url"], "id" => $sub["id"]]);

$shuffledChars = str_shuffle($allowedChars);
$randomString = substr($shuffledChars, 0, $length);
?>