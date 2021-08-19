<?php 

if (!isset($payment)) exit();

$data_arr = array(
  "public_key" => $public_key,
  "version" => "3",
  "action" => "pay",
  "amount" => $payment["price"],
  "currency" => getCurrencyCode($payment["currency"]),
  "description" => $payment["description"],
  "order_id" => $payment["id"],
  "result_url" => $GLOBALS["site_url"] . "buy_process?LiqPay"
);
$data = base64_encode(json_encode($data_arr));

$sign_string = $secret_key . $data .  $secret_key;

$signature = base64_encode(sha1($sign_string, true));

$payment_form = '
  <form id="payment_form" method="POST" action="https://www.liqpay.ua/api/3/checkout" accept-charset="utf-8">
    <input type="hidden" name="data" value="'.$data.'">
    <input type="hidden" name="signature" value="'.$signature.'">
  </form>
';

echo json_encode(array("success" => 1, "payment_form" => $payment_form));

?>