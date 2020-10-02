<?php
	error_reporting(E_ALL);
	ini_set("display_errors", 1);

	$URL_SITE = "https://tonqac-mp-commerce-php.herokuapp.com/";

	// SDK de Mercado Pago
	require 'vendor/autoload.php';

	// Agrego credenciales del comercio
	MercadoPago\SDK::setAccessToken("APP_USR-6317427424180639-042414-47e969706991d3a442922b0702a0da44-469485398");
	
	// Agrego credenciales del programador
	MercadoPago\SDK::setIntegratorId("dev_24c65fb163bf11ea96500242ac130004");

	// Creo un objeto de preferencia
	$preference = new MercadoPago\Preference();

	// Información del producto
	$item = new MercadoPago\Item();

	$item->id = "1234";
    $item->description = "Dispositivo móvil de Tienda e-commerce";
	$item->picture_url = $URL_SITE."assets/".basename($_POST["img"]);

	$item->title = $_POST["title"];
	$item->quantity = (int)$_POST["unit"];
	$item->unit_price = (float)$_POST["price"];
	$preference->items = array($item);

	// Información del comprador
	$payer = new MercadoPago\Payer();
	$payer->name = "Lalo";
	$payer->surname  = "Landa";
	$payer->id = "471923173";
	$payer->email = "test_user_63274575@testuser.com";
	$payer->phone = array(
		"area_code" => "11",
		"number" => "22223333"
	);
	$payer->address = array(
		"street_name" => "False",
		"street_number" => 123,
		"zip_code" => "1111"
	);
	$preference->payer = $payer;

	
	// Medios de pago
	$preference->payment_methods = array(
		"excluded_payment_methods" => array(
			array("id" => "amex")
		),
		"excluded_payment_types" => array(
			array("id" => "atm")
		),
		"installments" => 6
	);

	// Back URLs
	$preference->back_urls = array(
		"success" => $URL_SITE."success.php",
		"failure" => $URL_SITE."failure.php",
		"pending" => $URL_SITE."pending.php"
	);

	$preference->auto_return = "approved";
	$preference->notification_url = $URL_SITE."notifications.php";
	$preference->external_reference = "tonqac@yahoo.com";
	$preference->save();
?>

<form action="success.php" method="POST">
  <script
   src="https://www.mercadopago.com.ar/integrations/v1/web-payment-checkout.js"
   data-preference-id="<?php echo $preference->id; ?>"
   data-button-label="Pagar la compra">
  </script>
</form>