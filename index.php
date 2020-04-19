<?php 
require_once('conf_cnx.php');
require('models.php');




/* FIN DE LA CLASSE FICHIER CNX */
 
/* FIN DE LA PREMIERE CLASSE CREATION D UNE INSTANCE DE CONNEXION */  



/*****************************PARTIE CONTROLLER*************************************** */ 
/*****************************PARTIE CONTROLLER*************************************** */ 
/*****************************PARTIE CONTROLLER*************************************** */ 
/*****************************PARTIE CONTROLLER*************************************** */ 
class PseudoController{ 

    public static function indexController(){
        /*$obj=ConnexionClasse::CreerPDO();
        $request="SELECT nomproduit,qtepdt FROM t_produit";
        $arrayResult=RequeteClasse::ReqRead($obj,$request);
        return $arrayResult;*/
    }

    public static function testModel(){
        //$mdl=new ModelBase('t_produit','id');
        $array=['nomproduit','qtepdt'];
        $pdt = new ProduitModel('t_produit','id',$array); 
        $arrCol=$pdt->getColumn();
        $arrInd=['2','3'];
        $newVarArr=['riznouveau','8'];
        $request=$pdt->FullUpdateRequest($arrInd,$arrCol,$newVarArr);
        //return $request;
        return $pdt->prepareThenExecute($request);


    }
    



}


?>

<!DOCTYPE html>
<html>
<body>
<!--****************************************PARTIE VUE *********************************-->
<!--****************************************PARTIE VUE *********************************-->
<!--****************************************PARTIE VUE *********************************-->
<!--****************************************PARTIE VUE *********************************-->
<h1>Liste des courses</h1>
<?php 
/*$var=PseudoController::indexController();
    foreach($var as $key=>$value){
        echo "Id : $key  / nom complet :  {$value['nomproduit']} / quantité : {$value['qtepdt']} <br />";  
    }*/
$foo=PseudoController::testModel();
var_dump($foo);
?>

</body>
</html>
