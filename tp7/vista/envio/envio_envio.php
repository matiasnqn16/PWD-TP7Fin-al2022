<?php 
include_once "../../configuracion.php";
$data = data_submitted();

$respuesta=true; // por defecto asumimos que todos tienen stock, sino cambiamos a false
//----------- abntes de que nada tengo q validar stock

$objcompraitem=new CTRLCompraitem();
$list = $objcompraitem->buscar($data);
$mensaje="";
foreach ($list as $elem ){

    // si seguimos sin ninguno que bloquee seguimos validando
    if ($respuesta) {
        $idproducto=$elem->getObjProducto()->getidproducto();
        $prodetalle=$elem->getObjProducto()->getprodetalle();
        $procantstock=$elem->getObjProducto()->getprocantstock();
        $cicantidad=$elem->getcicantidad();

        if ($cicantidad>$procantstock) {
           $respuesta=false;
           $mensaje= $mensaje . "Envio maximo permitido ".$prodetalle." = ".$procantstock. " "; 
       }
    }   
}

if ($respuesta) {
    /* foreach ($data as $ele) echo "<script>console.log('".$ele."')</script>";} */

    //necesito un objcompraestadotipo2 y 3
    $objcompraestadotipo=new CTRLCompraestadotipo();
    $listestado=$objcompraestadotipo->buscar("2");
    $objestadotipo2=$listestado[0];
    $listestado=$objcompraestadotipo->buscar("3");
    $objestadotipo3=$listestado[0];

    // primero busco el estado actual y le grabo fecha hasta
    $objcompraestado = new CTRLCompraestado();

    $listestado=$objcompraestado->buscar(["idcompra"=>$data['idcompra'],
                                        "cefechafin"=>null,
                                        "idcompraestadotipo"=>2]);

    foreach ($listestado as $objelem ){
        $cefechaini=$objelem->getcefechaini();
        $idcompraestado=$objelem->getidcompraestado();
        $datosmodif=['idcompra'=>$data['idcompra'],
                     'idcompraestado'=>$idcompraestado,
                     'idcompraestadotipo'=>'2', 
                     'cefechaini'=>$cefechaini,'cefechafin'=>date("Y-m-d H:i:s")];
        //    var_dump($objcompraestado);    
        $respuesta=$objcompraestado->modificacion($datosmodif);
        if ( !$respuesta ) {
           $mensaje = "Error actualizando compraestadotipo 2"; 
        }
    }
    if ($respuesta) {
        // despues inserta un reg con el nuevo estado "3" enviado
        $respuesta=$objcompraestado->alta( [ 'idcompraestado'=>null,'cefechaini'=>date("Y-m-d H:i:s"),'cefechafin'=>null,
                                       'idcompra'=>$data['idcompra'],
                                       'idcompraestadotipo'=>'3']);
        if (!$respuesta ) {
           $mensaje = "Error insertando compraestadotipo 3"; 
        }
        // finalmente barro los item y actualizo stock de los articlos
        if ($respuesta) {
            $objcompraitem=new CTRLCompraitem();
            $list = $objcompraitem->buscar($data);
            foreach ($list as $elem ){
            
                $respuesta=false;

                $idproducto=$elem->getObjProducto()->getidproducto();
                $cicantidad=$elem->getCiCantidad();
                $pronombre=null; $prodetalle=null; $procantstock=null;

                $objproducto=new CTRLProducto();
                $arreglo=$objproducto->buscar(['idproducto'=>$idproducto]);

                foreach($arreglo as $objunproducto) {
                    $respuesta=$objproducto->modificacion(['idproducto'=>$idproducto,
                                                        'pronombre'=>$objunproducto->getpronombre(),
                                                        'prodetalle'=>$objunproducto->getprodetalle(),
                                                        'procantstock'=>$objunproducto->getprocantstock()-$cicantidad]);
                }                                                    
                if ( !$respuesta ) {
                   $mensaje = "Error actualizando articulos"; 
                }
            } 
        }  // oka rspuesta a a cambio de estado  
    } // oka respuesta a cambia de estado
} // oka respuesta de stock
$retorno['respuesta'] = $respuesta;
if (isset($mensaje)){
    $retorno['errorMsg']=$mensaje;
}
echo json_encode($retorno);
?> 
