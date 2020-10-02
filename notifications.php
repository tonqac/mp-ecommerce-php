<?php
    $txt = "";
    foreach($_POST as $n=>$v){
        $txt.= "$n: ".print_r($v,true);
    }
    $txt = "\n INIT NOTIFICATION POST \n".date("Y-m-d H:i:s"). "\n". json_encode($_POST);
    file_put_contents('results.txt', $txt, FILE_APPEND | LOCK_EX);

    $txt = "";
    foreach($_POST as $n=>$v){
        $txt.= "$n: ".print_r($v,true);
    }

    $txt = "\n FOREACH POST \n".date("Y-m-d H:i:s"). "\n". $txt;
    file_put_contents('results.txt', $txt, FILE_APPEND | LOCK_EX);

    $txt = "\n INIT NOTIFICATION GET \n".date("Y-m-d H:i:s"). "\n". json_encode($_GET);
    file_put_contents('results.txt', $txt, FILE_APPEND | LOCK_EX);

    $txt = "\n INIT NOTIFICATION REQUEST \n".date("Y-m-d H:i:s"). "\n". json_encode($_REQUEST);
    file_put_contents('results.txt', $txt, FILE_APPEND | LOCK_EX);

    if($_GET["type"]=="payment"){

        // SDK de Mercado Pago
        require 'vendor/autoload.php';

        MercadoPago\SDK::setAccessToken("APP_USR-6317427424180639-042414-47e969706991d3a442922b0702a0da44-469485398");
        MercadoPago\SDK::setIntegratorId("dev_24c65fb163bf11ea96500242ac130004");

        // Obtengo información del pago
        $payment = MercadoPago\Payment::find_by_id($_GET["data_id"]);
        $merchant_order = MercadoPago\MerchantOrder::find_by_id($payment->order->id);

        $txt = "\n GET NOTIFICATION \n".date("Y-m-d H:i:s"). "\n". print_r($merchant_order,true);
        file_put_contents('results.txt', $txt, FILE_APPEND | LOCK_EX);

        $json = json_encode((array) $merchant_order, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        file_put_contents('results.json', $json);
    }

    header('Content-Type: application/json');
    echo json_encode(['HTTP/1.1 200 OK'], 200);
?>