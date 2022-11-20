<?php 
include_once('../../configuracion.php');

$data = data_submitted();
$objControl = new CTRLUsuarioRol();
$list = $objControl->buscar($data);
//echo json_encode($list,null,2);
$arreglo_salida =  array();
foreach ($list as $elem ){
     
    $nuevoElem["idusuario"] = $elem->getobjusuario()->getidusuario();
    $nuevoElem["idrol"]=$elem->getobjrol()->getidrol();
    $nuevoElem["rodescripcion"]=$elem->getobjrol()->getrodescripcion();
   
    array_push($arreglo_salida,$nuevoElem);
}
echo json_encode($arreglo_salida);
?>