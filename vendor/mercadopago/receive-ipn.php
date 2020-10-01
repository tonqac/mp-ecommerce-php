<?php
require_once "../../../includes/config.php";

$comercioDAO = DAOFactory::getDAO("comercio");
$publicacionDAO = DAOFactory::getDAO("publicacion");
$ventaDAO = DAOFactory::getDAO("venta");
$ventaItemDAO = DAOFactory::getDAO("ventaItem");
$notificacionDAO = DAOFactory::getDAO("notificacion");

// Chequea que se reciba el ID de Mercado Pago, el ID de vendedor y el ID de Venta
if(empty($_GET["id"]) || !ctype_digit($_GET["id"]) || empty($_GET["id_venta"]) || empty($_GET["id_comercio"])) {
    http_response_code(400);
    return;
}

// Obtiene el objeto de Venta y sus Items
$id_venta = (int) Crypto::decrypt($_GET["id_venta"]);
$venta = $ventaDAO->buscarPorId($id_venta);
$venta_items = (array) $ventaItemDAO->buscarPorVenta($id_venta);

// Obtiene el objeto de Comercio e incializa MercadoPago con el acceso token
$id_comercio = (int) Crypto::decrypt($_GET["id_comercio"]);
$comercio = $comercioDAO->buscarPorId($id_comercio);
MercadoPago\SDK::setAccessToken($comercio->getMpToken());

// Obtiene la información de MercadoPago
$payment = new MercadoPago\Payment;
$MP_id_payment = $_GET["id"];
$MP_order_info = null;
$MP_order_info = $payment::find_by_id($MP_id_payment);
$MP_order_info_json = str_replace("\u0000*\u0000", "", json_encode((array) $MP_order_info, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
// Utils::debugObj($MP_order_info);exit;

// Se procesa la información obtenida de MercadoPago
if(count($venta)>0 && count($comercio)>0 && !empty($MP_order_info)) {
    // Utils::debugObj($MP_order_info);exit;
        
    // Se actualiza la información de MP en la venta
    $venta->setMpCollectionId($MP_order_info->id);
    $venta->setMpCollectionStatus($MP_order_info->status);
    $venta->setMpPaymentType($MP_order_info->operation_type);
    $venta->setMpResponse($MP_order_info_json);
    $venta->setActivo(1);
    
    switch($MP_order_info->status){
        case 'pending':
        case 'in_process':
        case 'in_mediation':
            $venta->setEstado(TIPO_VENTA_PENDIENTE);
            break;

        case 'approved':
        case 'authorized':
            $venta->setEstado(TIPO_VENTA_PAGO_OK);
            break;
        
        case 'rejected':
        case 'cancelled':
            $venta->setEstado(TIPO_VENTA_PAGO_ERR);
            break;
    }
    
    // Utils::debugObj($venta);exit;
    $result = $ventaDAO->modificar($venta);
    
    // Se actualizan los items para congelar su información
    if($MP_order_info->status=='approved' || $MP_order_info->status=='authorized'){
        foreach($venta_items as $item){

            // Actualizo el stock de la publicación
            $publicacion = $publicacionDAO->buscarPorId($item->getIdPublicacion());
            $publicacion->setCantidad($publicacion->getCantidad() - $item->getCantidad());
            $publicacionDAO->modificar($publicacion);
            
            // Actualizo importes del item para congelarlos
            $item->setPrecio($publicacion->getPrecio());
            $item->setSubtotal($publicacion->getPrecio() * $item->getCantidad());
            $ventaItemDAO->modificar($item);

            // Genero notificaciones
            $cuerpo = "";
            if($publicacion->getColor()->getTexto(true)!="") $cuerpo.= "- ".__Color__.": ".$publicacion->getColor()->getTexto(true);
            if($publicacion->getTalle()->getTexto(true)!="") $cuerpo.= "- ".__Tamano__.": ".$publicacion->getTalle()->getTexto(true);
            if($publicacion->getMaterial()->getTexto(true)!="") $cuerpo.= "- ".__Material__.": ".$publicacion->getMaterial()->getTexto(true);
            if($publicacion->getTipoAlojamiento()->getTexto(true)!="") $cuerpo.= "- ".__Tipo_alojamiento__.": ".$publicacion->getTipoAlojamiento()->getTexto(true);
            if($publicacion->getTipoAlquiler()->getTexto(true)!="") $cuerpo.= "- ".__Tipo_alquiler__.": ".$publicacion->getTipoAlquiler()->getTexto(true);
            if($publicacion->getCantidadPersonas()->getTexto(true)!="") $cuerpo.= "- ".__Cant_personas__.": ".$publicacion->getCantidadPersonas()->getTexto(true);

            $imagen = ($publicacion->getImagen()->getArchivo()=="")? "/img/logo_gris.svg" : $publicacion->getImagen()->getArchivo();

            $cuerpo = '<img src="'.ROOT_URL.$imagen.'" style="max-width:100%">
                        <h1>'.$publicacion->getNombre()->getTexto(true).'</h1>
                        <h3>'.trim($descripcion,"-").'</h3>';

            // Vendedor
            $obj = new Notificacion();
            $obj->setIdTipo(TIPO_NOTIFICACION_NUEVA_VENTA);
            $obj->setIdUsuarioOrigen($venta->getIdUsuario());
            $obj->setIdUsuarioDestino($publicacion->getIdUsuario());
            $obj->setAsunto(__Asunto_Nueva_Venta__);
            $obj->setCuerpo(__Descripcion__.$cuerpo);

            $notificacionDAO->insertar($obj);

            // Comprador
            $obj = new Notificacion();
            $obj->setIdTipo(TIPO_NOTIFICACION_NUEVA_COMPRA);
            $obj->setIdUsuarioOrigen($publicacion->getIdUsuario());
            $obj->setIdUsuarioDestino($venta->getIdUsuario());
            $obj->setAsunto(__Asunto_Nueva_Compra__);
            $obj->setCuerpo(__Descripcion__.$cuerpo);

            $notificacionDAO->insertar($obj);
        }
    }

    echo "Se actualizó todo OK!";
}else{
    die("Error obtaining the Order Info");
}

?>