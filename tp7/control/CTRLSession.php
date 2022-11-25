<?php
class CTRLSession{ 

    public function __construct(){
        if (session_status() !== PHP_SESSION_ACTIVE) {
           session_start();
        }
    }
    
    public function iniciar($usnombre,$uspass) {
       $resp=FALSE;
       $OBJUsuario=new Usuario;
       $arreglo=$OBJUsuario->listar("usnombre ='".$usnombre."' AND uspass='".$uspass."' AND ISNULL(usdeshabilitado)");
       foreach ($arreglo as $OBJunusuario) {
          $_SESSION["usnombre"]=$usnombre;
          $_SESSION["idusuario"]=$OBJunusuario->getidusuario();
          $_SESSION["rolactivo"]=null; // levanta con null el rol / sin rol - 

          // levanta el rol
          $OBJUsuarioRol=new UsuarioRol();
          if ($arreglo2=$OBJUsuarioRol->listar("idusuario='".$OBJunusuario->getidusuario()."'")) { 
             $OBJunusuariorol=$arreglo2[0];
             $_SESSION["rolactivo"]=$OBJunusuariorol->getOBJRol()->getidrol(); // levanta el primero
          }  
          $resp=TRUE; // login oka
       }
       return $resp;
    }

    // si la session tiene usuario correcto (x ende password sino no podria haber entrado)
    public function validar() {
        $resp = FALSE;
        if ( isset($_SESSION["usnombre"])) {
           $resp=TRUE;
        }
        return $resp;
    }

    public function activa() {
		$resp=false;
		if (session_status()=== PHP_SESSION_ACTIVE) {
			$resp=true;
		}
		return $resp;
    }

    public function getUsuario(){
        return $_SESSION['usnombre'];
    }
    public function getidUsuario(){
        return $_SESSION['idusuario'];
    }

    public function getRol(){
        $resp=null;
        if (isset($_SESSION['rolactivo'])) {
            $resp=$_SESSION['rolactivo']; 
        }
        return $resp;
    }

    public function setRol($idrol){
        $resp=null;
        if (isset($idrol)) {
            $_SESSION['rolactivo']=$idrol; 
        }
        return $resp;
    }

    public function cerrar(){
		if ($this->activa()) {
        	session_unset();
            session_destroy();
		}
	}

    public function puedoentrar($desdedonde) {
 
        // esta convesrsion era la que no queria hacer, habia que usar los codigos ascii nomas
        $desdedonde=str_replace(chr(92),chr(47),$desdedonde);
        $resta=substr($desdedonde,0,strrpos($desdedonde,chr(47)));
        $pos=strrpos($resta,chr(47));
        $tofind="..".substr($desdedonde,$pos);
        // formatee __FILE al formato de meurl => ../vista/carrito/index.php etc
        
        $objmenu=new Menu();
        $ok=false;
        $lista=$objmenu->listar("meurl='".$tofind."'");

        if ($lista) {
            $sinrolrequerido=$lista[0]->getsinrolrequerido();
            $idmenu=$lista[0]->getidmenu();

            if ($sinrolrequerido != null) {
               $ok=true;
            } else {
                // ya tenemos la id del menu ahora vamos a ver si dentro de los roles que tiene esta el activo o es rolless
                $objmenurol=new Menurol();
                if ($this->getRol() != null  && $objmenurol->listar("idmenu='".$idmenu."' and idrol='".$this->getRol()."'") != NULL ) { 
                    $ok=true;
                }
            }    
        }
         return $ok;
    }         
}
?>