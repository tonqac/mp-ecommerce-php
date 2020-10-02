<?php
    $txt = "\n INIT NOTIFICATION POST \n".date("Y-m-d H:i:s"). "\n". print_r($_POST,true);
    file_put_contents('results.txt', $txt, FILE_APPEND | LOCK_EX);

    $txt = "\n INIT NOTIFICATION GET \n".date("Y-m-d H:i:s"). "\n". print_r($_GET,true);
    file_put_contents('results.txt', $txt, FILE_APPEND | LOCK_EX);

    $txt = "\n INIT NOTIFICATION REQUEST \n".date("Y-m-d H:i:s"). "\n". print_r($_REQUEST,true);
    file_put_contents('results.txt', $txt, FILE_APPEND | LOCK_EX);

    // SDK de Mercado Pago
    require 'vendor/autoload.php';

    MercadoPago\SDK::setAccessToken("APP_USR-6317427424180639-042414-47e969706991d3a442922b0702a0da44-469485398");

    // Obtengo información del pago
    $payment = MercadoPago\Payment::find_by_id($_GET["data_id"]);
    $merchant_order = MercadoPago\MerchantOrder::find_by_id($payment->order->id);

    $txt = "\n GET NOTIFICATION \n".date("Y-m-d H:i:s"). "\n". print_r($merchant_order,true);
    file_put_contents('results.txt', $txt, FILE_APPEND | LOCK_EX);

    $json = json_encode((array) $merchant_order, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    file_put_contents('results.json', $json);

    header('Content-Type: application/json');
    echo json_encode(['HTTP/1.1 200 OK'], 200);
?>