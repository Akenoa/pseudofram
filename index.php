<?php 

class Config{ 

    //sous forme de tableau?
    const DB='dbcourse';
    const HOSTNAME='127.0.0.1';
    const USR='root';
    const PWD='';

}
/* FIN DE LA CLASSE FICHIER CNX */
class ConnexionClasse {

    /* attributs static de la classe ConnexionClasse */
    private static $dsn;
    public static $dbh;

    /*  méthode static pour créer une instance pdo */
    public static function CreerPDO(): ?PDO{
        self::$dsn="mysql:dbname=".Config::DB.";host=".Config::HOSTNAME;
        try{
            self::$dbh = new PDO(self::$dsn,Config::USR,Config::PWD);
            return self::$dbh;
        } 
        catch(PDOException $err){
            echo "Error : " . $err->getMessage();
            self::$dbh=null;
            return self::$dbh;

        }
        

    }
    
} 
/* FIN DE LA PREMIERE CLASSE CREATION D UNE INSTANCE DE CONNEXION */ 

class RequeteClasse{ 

    /**
     * @return PDO object pour se connecter.
     */
    public static function faisConnexion():?PDO{
        return ConnexionClasse::CreerPDO();
    }

    /*
    * @param Arram $arrayOfLastItemDesired 
    * @return the last item of array.
    
    public static function donneDernierItem(Array $arrayOfLastItemDesired){
        $lastItemArray = end($arrayOfLastItemDesired);
        return $lastItemArray;

    }
    */

    /*
    * @param String $request la requete qu'on veut préparer puis executer
    * @return Bool résultat si la requête réussi true sinon false
    */ 
    public static function prepareThenExecute(string $request):?bool{
        $objPdo=RequeteClasse::faisConnexion();
        $requestStatement=$objPdo->prepare($request);
        return $requestStatement->execute();

    }

    /*
    * @param String $request la requete qu'on veut préparer puis executer
    * @return Array tableau assoaciatif des résultat à lire
    */ 
    public static function prepareThenExecuteReadDataAssoc(string $request):?Array{
        $objPdo=RequeteClasse::faisConnexion();
        $requestStatement=$objPdo->prepare($request);
        $requestStatement->execute();
        $arrayOfDataResult=$requestStatement->fetchAll(PDO::FETCH_ASSOC);

    }
     /*
    * @param String $request la requete qu'on veut préparer puis executer
    * @return Array tableau indexé en partant de 0
    */ 
    public static function prepareThenExecuteReadDataNum(string $request):?Array{
        $objPdo=RequeteClasse::faisConnexion();
        $requestStatement=$objPdo->prepare($request);
        $requestStatement->execute();
        $arrayOfDataResult=$requestStatement->fetchAll(PDO::FETCH_NUM);

    }

/*****************************REQUETE CRUD*************************************** */ 
/*****************************REQUETE CRUD*************************************** */ 
/*****************************REQUETE CRUD*************************************** */ 
/*****************************REQUETE CRUD*************************************** */ 

    /*
    * @param Array $ParamReqAdd : tableau qui contient le nécessaire pour écrire toute la requête
    * le tableau doit prendre la forme suivante : (1ère clé pour la table les autres fonctionnent par 
    * paire clé-valeur pour champs et les valeurs à insérer)
    * ['table'=>'nomtable', 
    * 'indice'=>['champ1'=>'value1',"champ2"=>'$value2]
    */
    public static function prepareInsert(Array $ParamReqAdd): ?String{
        $firstPartRequest="";
        $secondPartRequest="";
        $finalRequest="";//la requête à retourner à la fin

        $nomtable="`".$ParamReqAdd['table']."`";
        $firstPartRequest="INSERT INTO $nomtable ( ";
        $secondPartRequest=" VALUES (";

        $arrayKeyIndice=array_splice($ParamReqAdd,1); // ['indice'=>['champ'=>'valeur]]
        foreach($arrayKeyIndice as $listOfInsertField){
            $lastElement=end($listOfInsertField); //récupérer le dernier élément pour pouvoir clore la requête
            foreach($listOfInsertField as $key=>$value){
                if($value===$lastElement){
                    $firstPartRequest.="`$key`)";
                    $secondPartRequest.="'$value')";

                }
                else{ 
                    $firstPartRequest.="`$key`,";
                    $secondPartRequest.="'$value',";
                }

            }

        } 
        $finalRequest=$firstPartRequest.$secondPartRequest;
        return $finalRequest;
    }

    /*
    * @param Array $ParamReqAdd : tableau qui contient le nécessaire pour écrire toute la requête
    * le tableau doit prendre la forme suivante : (1ère clé pour la table les autres fonctionnent par 
    * paire clé-valeur pour champs et les valeurs à insérer)
    * ['table'=>'nomtable', 
    * 'primaryKName'=>'nompk',
    * 'indice'=>['champ1'=>'value1',"champ2"=>'$value2]
    */
    public static function prepareDelete(Array $ParamReqDel):?string{
        $firstPartRequest="";
        $secondPartRequest="";
        $finalRequest="";//ce qu'on va retourner à la fin 
    
        $nomtable="`".$ParamReqDel['table']."`";
        $primaryKeyName="`".$ParamReqDel['primaryKName']."`";
        $firstPartRequest="DELETE FROM $nomtable  ";
    
        $intermediateArrayForDelRequest=array_slice($ParamReqDel,2); // ['indice'=>['champ'=>'valeur]]
        foreach($intermediateArrayForDelRequest as $listOfDeletableItem){
            $lastItem=end($listOfDeletableItem); //recupére le dernier element du tableau pour écrire correctement l'ordre sql
            //la liste des choses à supprimer est vide à 0  ->purge de la table
            if(count($listOfDeletableItem)===0){ 
                $secondPartRequest="";
    
            }
            else{
                $firstPartRequest .=" WHERE ";
                foreach($listOfDeletableItem as $key=>$value){
                    if($value===$lastItem){ //fin de la requête
                        $secondPartRequest.=" $primaryKeyName='$value' ";
                    }
                    else{
                        $secondPartRequest.=" $primaryKeyName = '$value' OR ";  
                    }
                                 
                }
            }
           
        }
        $finalDeleteRequest=$firstPartRequest.$secondPartRequest;
        return $finalDeleteRequest;
    }

    /**
     * @param PDO $pdoObject: obj de connexion PDO pour pouvoir faire les requête
     * @param Array $ParamArray: tableau contenant le nécessaire pour faire la requête 
     * le tableau doit prendre la forme suivante : (1ère clé pour la table les autres fonctionnent par 
     * paire clé-valeur pour champs et les valeurs à insérer)
     * ['table'=>'nomtable', 
     * 'champ1'=>'value1',
     * 'champ2'=>'valeur2'] 
     * @return Bool true = insertion réussie, false : insertion échouée
     */
    public static function ReqAdd(Array $ParamReqAdd): bool{      
       $request=RequeteClasse::prepareInsert($ParamReqAdd);
       $requestResult=RequeteClasse::prepareThenExecute($request);
        return $requestResult;

    } 


    /*
    09/04/2020 EN PAUSE
    public static function  ReqReadV2(PDO $pdoObject,Array $ParamReadArray){
        $nomTable="`".$ParamArray['table']."`"; //SELECT FROM TABLE;
        $firstPartRequest=""; // CHAMPS,CHAMPS,
        $secondPartRequest=""; // WHERE `colonne` = champs
        
        
    }*/

    /**
     * @param PDO $pdoObject: obj de connexion PDO pour pouvoir faire les requête
     * @param String $request: la requête qu'on veut executé.
     * @return Array $ReadableArrayData le tableau contenant les données sous forme tableau-associatif
    */
    public static function ReqRead(PDO $pdoObject,string $request) : array {

        $statement=$pdoObject->prepare($request); 
        $statement->execute();
        $ReadableArrayData=$statement->fetchAll(PDO::FETCH_ASSOC);
        return $ReadableArrayData;

    } 

    /**
     * @param Array $ParamDelArray: tableau contenant le nécessaire pour faire la requête 
     * le tableau doit prendre la forme suivante : (1ère clé pour la table, seconde pour le nom du champ de la clé primaire et la troisième clé
     * est associé à un tableau de l'ensemble des choses à supprimer (si le tableau est vide ça pruge la table : DELETE FROM TABLE) : 
     * [
     * 'table'=>'nomtable',
     * 'primaryKName'=>'nomPk',
     * 'identifiants'=>[X,X,X]
     * ]
     * @return Bool true = suppresion réussie, false : suppresion échouée
     */
    public static function ReqDelete(Array $ParamDelArray):bool{
        $request=RequeteClasse::prepareDelete($ParamDelArray);
        $requestResult=RequeteClasse::prepareThenExecute($request);
        return $requestResult;

    }
    /**
     * @param Array $ParamReqUpd: tableau contenant le nécessaire pour faire la requête 
     * le tableau doit prendre la forme suivante : (1ère clé pour la table, seconde pour le nom du champ de la clé primaire et la troisième clé
     * est associé à un tableau de l'ensemble des choses à modifier : 
     * [
     * 'table'=>'nomtable',
     * 'primaryKName'=>'nomPk',
     * 'identifiants'=>[X,X,X]
     * ] 
     * @param Array $FieldValueUpd : tableau contenant une clé unique qui a pour valeur associée un tableau pair clé-valeur qui sert à modifier les enregistrements : 
     * [
     * 'indices'=>[
     *'champ1'=>'nouvellevaleur1',
     *'champ2'=>'nouvellevaleur2'
     * ]
     * @return Bool true = modification réussie, false : modification échouée
     */
    public static function ReqUpdate(Array $ParamReqUpd,Array $FieldValueUpd):bool{      
        $request=RequeteClasse::prepareUpdate($ParamReqUpd,$FieldValueUpd);
        $requestResult=RequeteClasse::prepareThenExecute($request);
        return $requestResult;
 
     } 
 
    /**
     * @param Array $ParamReqUpd: tableau contenant le nécessaire pour faire la requête 
     * le tableau doit prendre la forme suivante : (1ère clé pour la table, seconde pour le nom du champ de la clé primaire et la troisième clé
     * est associé à un tableau de l'ensemble des choses à modifier : 
     * [
     * 'table'=>'nomtable',
     * 'primaryKName'=>'nomPk',
     * 'identifiants'=>[X,X,X]
     * ] 
     * @param Array $FieldValueUpd : tableau contenant une clé unique qui a pour valeur associée un tableau pair clé-valeur qui sert à modifier les enregistrements : 
     * [
     * 'indices'=>[
     *'champ1'=>'nouvellevaleur1',
     *'champ2'=>'nouvellevaleur2'
     * ]
     * @return string $finalUpdateRequest : l'ordre sql prêt à être executé
     */
    public static function prepareUpdate(Array $BaseReqUpd, Array $FieldValueUpd):?string{
        $finalUpdateRequest=""; //toute la requête
        $firstPartRequest=""; // UPDATE TABLE
        $secondPartRequest=""; //`CHAMP` = 'VALUE'
        $thirdPartRequest=""; // `PK`=VALUE PAS OUBLIER D AJOUTER WHERE A LA FIN
        
        $nomTable="`".$BaseReqUpd['table']."`"; // utile à syntaxe sql
        $primaryKeyName="`".$BaseReqUpd['primaryKName']."`";// utile syntaxe sql

        $firstPartRequest=" UPDATE $nomTable";
        //recup tableau sans la table et la pk, que la clé et le sous tableau des id à update
        $intermediateArrayForUpdRequest=array_slice($BaseReqUpd,2); 
        //construction de la dernière partie de la requête après le WHERE : `cléprimairechamp`='value' OR `cléprimaire`='value2'
        foreach($intermediateArrayForUpdRequest as $listOfUpdtableItem){
            $lastItem=end($listOfUpdtableItem);
            foreach($listOfUpdtableItem as $key=>$value){
                if($value===$lastItem){
                    $thirdPartRequest.=" $primaryKeyName = '$value' ";
                }
                else{
                    $thirdPartRequest.=" $primaryKeyName = '$value' OR ";
                }

            }
        } 
        $intermediateArrayForField=array_slice($FieldValueUpd,0); //récupération du tableau sous forme ['champ'=>['clé1'=>'value1','clé2'=>'value2']]
        foreach($intermediateArrayForField as $listOfSetField){
            $lastItemField=end($listOfSetField); 
            //construction de la seconde partie de la requête après le SET `nomchamp`='nouvellevaleur',`nomchamp2`='nouvellevaleur2' 
            foreach($listOfSetField as $keyField=>$valueField){ 
                if($valueField===$lastItemField) {
                    $secondPartRequest.="  `$keyField` = '$valueField' ";
                }
                    
                else {
                    $secondPartRequest.="  `$keyField` = '$valueField' ,";
                }

            }
        }
        $finalUpdateRequest=$firstPartRequest." SET ".$secondPartRequest."WHERE ".$thirdPartRequest;
        return $finalUpdateRequest;
        
    }

//FIN DE LA CLASSE
}
/*****************************FIN REQUETE CRUD*************************************** */
/*****************************FIN REQUETE CRUD*************************************** */ 
/*****************************FIN REQUETE CRUD*************************************** */ 




/*****************************PARTIE TEST*************************************** */ 
/*****************************PARTIE TEST*************************************** */
/*****************************PARTIE TEST*************************************** */
/*****************************PARTIE TEST*************************************** */ 

/*TEST SUR UPDATE 
$tableauUpdtBase=[
    'table'=>'t_produit',
    'primaryKName'=>'id',
    'identifiants'=>[3,8]

]; 

$tableauUpdtSuite=[
    'indices'=>[
        'nomproduit'=>'hello',
        'qtepdt'=>'4'
    ]
];
$var=RequeteClasse::ReqUpdate($tableauUpdtBase,$tableauUpdtSuite);
var_dump($var);*/




/* TEST SUR LA SUPRESSION  
$tableauSuppr=[
    'table'=>'t_produit',
    'primaryKName'=>'id',
    'identifiants'=>[3,4]

];
$var=RequeteClasse::ReqDelete($tableauSuppr);
var_dump($var);*/



/*TEST SUR L INSERTION
$tableauReqInsert=[
    "table"=>'t_produit',
    "indice"=>[
            "nomproduit"=>"beurre",
            "qtepdt"=>'2'
            ]
];
$insertion=RequeteClasse::ReqAdd($tableauReqInsert);
var_dump($insertion);*/



//print_r($arrayResult);

/* TEST SUR LA SELECTION */


/*****************************PARTIE CONTROLLER*************************************** */ 
/*****************************PARTIE CONTROLLER*************************************** */ 
/*****************************PARTIE CONTROLLER*************************************** */ 
/*****************************PARTIE CONTROLLER*************************************** */ 
class PseudoController{ 

    public static function indexController(){
        $obj=ConnexionClasse::CreerPDO();
        $request="SELECT nomproduit,qtepdt FROM t_produit";
        $arrayResult=RequeteClasse::ReqRead($obj,$request);
        return $arrayResult;
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
$var=PseudoController::indexController();
    foreach($var as $key=>$value){
        echo "Id : $key  / nom complet :  {$value['nomproduit']} / quantité : {$value['qtepdt']} <br />";  
    }
?>

</body>
</html>
