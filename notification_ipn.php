<?php
    error_reporting(E_ALL);
    ini_set("display_errors", 1);

    $txt = "\n INIT NOTIFICATION \n".date("Y-m-d H:i:s"). "\n". print_r($_REQUEST,true);
    file_put_contents('results.txt', $txt, FILE_APPEND | LOCK_EX);

    if (!isset($_GET["id"], $_GET["topic"]) || !ctype_digit($_GET["id"])) {
        http_response_code(400);
        return;
    }

    $txt = "\n SECOND NOTIFICATION \n".date("Y-m-d H:i:s"). "\n". print_r($_REQUEST,true);
    file_put_contents('results.txt', $txt, FILE_APPEND | LOCK_EX);

    // SDK de Mercado Pago
    require 'vendor/autoload.php';

    MercadoPago\SDK::setAccessToken("APP_USR-6317427424180639-042414-47e969706991d3a442922b0702a0da44-469485398");

    $merchant_order = null;

    switch($_POST["type"]) {
        case "payment":
            $payment = MercadoPago\Payment::find_by_id($_GET["id"]);
            $merchant_order = MercadoPago\MerchantOrder::find_by_id($payment->order->id);
            break;
        case "merchant_order":
            $merchant_order = MercadoPago\MerchantOrder::find_by_id($_GET["id"]);
            break;
    }

    $txt = "\n GET NOTIFICATION \n".date("Y-m-d H:i:s"). "\n". print_r($merchant_order,true);
    file_put_contents('results.txt', $txt, FILE_APPEND | LOCK_EX);

    $json = json_encode((array) $merchant_order, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    file_put_contents('results.json', $json);
?>