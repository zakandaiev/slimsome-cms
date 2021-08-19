<?php 

if (!isset($payment)) exit();

$data_arr = array(
  "ik_co_id" => $public_key,
  "ik_pm_no" => $payment["id"],
  "ik_desc" => $payment["description"],
  "ik_am" => $payment["price"],
  "ik_cur" => getCurrencyCode($payment["currency"])
);

ksort($data_arr, SORT_STRING);
array_push($data_arr, $secret_key);
$signString = implode(':', $data_arr);
$sign = base64_encode(md5($signString, true));

$payment_form = '
  <form id="payment_form" method="POST" action="https://sci.interkassa.com/" accept-charset="UTF-8">
    <input type="hidden" name="ik_co_id" value="'.$public_key.'">
    <input type="hidden" name="ik_sign" value="'.$sign.'">
    <input type="hidden" name="ik_pm_no" value="'.$data_arr["ik_pm_no"].'">
    <input type="hidden" name="ik_desc" value="'.$data_arr["ik_desc"].'">
    <input type="hidden" name="ik_am" value="'.$data_arr["ik_am"].'">
    <input type="hidden" name="ik_cur" value="'.$data_arr["ik_cur"].'">
  </form>
';

echo json_encode(array("success" => 1, "payment_form" => $payment_form));

?>