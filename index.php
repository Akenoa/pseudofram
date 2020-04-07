<?php 

class ConnexionClasse {

    /* attributs static de la classe ConnexionClasse */
    /*private static $db;
    private static $hostname;
    private static $user;
    private static $pwd;*/
    private static $dsn;
    public static $dbh;

    /*  méthode static pour créer une instance pdo */
    public static function CreerPDO(string $db,string $hostname,string $user, string $pwd): ?PDO{
        self::$dsn="mysql:dbname=$db;host=$hostname";
        try{
            self::$dbh = new PDO(self::$dsn,$user,$pwd);
            return self::$dbh;
        } 
        catch(PDOException $err){
            echo "Error : " . $err->getMessage();
            self::$dbh=null;
            return self::$dbh;

        }
        

    }
    
} 
/* FIN DE LA PREMIERE CLASSE */ 

class RequeteClasse{ 

    /**
     * @param PDO $pdoObject: obj de connexion PDO pour pouvoir faire les requête
     * @param Array $ParamArray: tableau contenant le nécessaire pour faire la requête 
     * le tableau doit prendre la forme suivante : (1ère clé pour la table les autres fonctionnent par 
     * paire clé-valeur pour champs et les valeurs à insérer)
     * ['table'=>'nomtable', 'champ1'=>'value1','champ2'=>'valeur2'] 
     * @return Bool true = insertion réussie, false : insertion échouée
     */
    public static function ReqAdd(PDO $pdoObject, Array $ParamArray): bool{      
        $stmt=null; //variable qui servira a être type PDOStatement pour pouvoir execute requête dessus.
        $firstPartRequest=""; //partie contenant l'ordre INSERT INTO avec la table et les champs
        $secondPartRequest=""; //partie contenant la suite de l'ordre VALUE avec les valeurs à associé au champs
        $finalInsertRequest="";  //requête finale concaténation des 2 parties de la requête


        $nomTable = "`".$ParamArray['table']."`"; //mettre le nom de la table entre `` syntaxe SQL
        $firstPartRequest="INSERT INTO $nomTable ( ";
        $arrayFieldValue=array_splice($ParamArray,1); //retirer l'élement table du tableau pour n'avoir que les champs-valeurs
        $lastElementFieldValue=end($arrayFieldValue); //dernier élement du nouveau tableau
        $secondPartRequest=" VALUES (";
        foreach ($arrayFieldValue as $key => $value) {
            //NB: champs de la requête doivent être entre `` et les valeurs entre '' syntaxe SQL
            if($value===$lastElementFieldValue){ 
                //si on est à la fin du tableau on stop la chaine requete SQL
                $firstPartRequest.="`$key`)";
                $secondPartRequest.="'$value')";
            } 
            else {
                $firstPartRequest.= " `$key`,";
                $secondPartRequest.= "'$value',";
            }
            
            
        } 
        $finalInsertRequest=$firstPartRequest.$secondPartRequest;
        $stmt=$pdoObject->prepare($finalInsertRequest);
        return $stmt->execute(); 

    }

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
     * @param PDO $pdoObject: obj de connexion PDO pour pouvoir faire les requête
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
    public static function ReqDelete(PDO $pdoObject,Array $ParamDelArray):bool{
        $firstPartRequest="";
        $secondPartRequest="";
        $finalDeleteRequest=""; 

        $nomTable="`".$ParamDelArray['table']."`";//utliser les `` syntaxe SQL
        $primaryKeyName="`".$ParamDelArray['primaryKName']."`"; //utliser les `` syntaxe SQL


        $firstPartRequest="DELETE FROM $nomTable";
        //recup tableau sans la table et la pk, que la clé et le sous tableau des id à suppr
        $intermediateArrayForDelRequest=array_slice($ParamDelArray,2); 
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
        $statementDelete=$pdoObject->prepare($finalDeleteRequest);
        return $statementDelete->execute();

    }

 
/**
     * @param PDO $pdoObject: obj de connexion PDO pour pouvoir faire les requête
     * @param Array $BaseReqUpd: tableau contenant le nécessaire pour faire la requête 
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
    public static function ReqUpdate(PDO $pdoObject, Array $BaseReqUpd, Array $FieldValueUpd):bool{
        $finalUpdateRequest=""; //toute la requête
        $firstPartRequest=""; // UPDATE TABLE
        $secondPartRequest=""; //`CHAMP` = 'VALUE'
        $thirdPartRequest=""; // `PK`=VALUE PAS OUBLIER D AJOUTER WHERE A LA FIN
        
        $nomTable="`".$BaseReqUpd['table']."`"; // utile à syntaxe sql
        $primaryKeyName="`".$BaseReqUpd['primaryKName']."`";// utile syntaxe sql

        $firstPartRequest=" UPDATE $nomTable";
        //recup tableau sans la table et la pk, que la clé et le sous tableau des id à update
        $intermediateArrayForUpdRequest=array_slice($BaseReqUpd,2); 
        //construction de la dernière partie de la requête après le WHERE : `ccléprimairehamp`='value' OR `cléprimaire`='value2'
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
        $statementUpdate=$pdoObject->prepare($finalUpdateRequest);
        return $finalUpdateRequest;
        
    }

//FIN DE LA CLASSE
}

/* FIN DE LA DEUXIEME CLASSE */

$db="dbcourse";
$hostname="127.0.0.1";
$usr="root";
$pass="";

$obj=ConnexionClasse::CreerPDO($db,$hostname,$usr,$pass);

/* TEST SUR UPDATE */

$tableauUpdtBase=[
    'table'=>'t_produit',
    'primaryKName'=>'id',
    'identifiants'=>[2,4]

]; 

$tableauUpdtSuite=[
    'indices'=>[
        'nomproduit'=>'nouvellevaleur',
        'qtepdt'=>'8'
    ]
];
$var=RequeteClasse::ReqUpdate($obj,$tableauUpdtBase,$tableauUpdtSuite);
var_dump($var);

/* TEST SUR LA SELECTION */
$request="SELECT nomproduit,qtepdt FROM t_produit";
$arrayResult=RequeteClasse::ReqRead($obj,$request);  

/* TEST SUR LA SUPRESSION  
$tableauSuppr=[
    'table'=>'t_produit',
    'primaryKName'=>'id',
    'identifiants'=>[1,5]

];
$var=RequeteClasse::ReqDelete($obj,$tableauSuppr);
var_dump($var);*/



/*TEST SUR L INSERTION
$tableauReqInsert=[
    "table"=>'t_produit',
    "nomproduit"=>"poisson",
    "qtepdt"=>"3",
];
$insertion=RequeteClasse::ReqAdd($obj,$tableauReqInsert);
var_dump($insertion);*/



//print_r($arrayResult);
?>

<!DOCTYPE html>
<html>
<body>

<h1>Liste des courses</h1>
<?php
    foreach($arrayResult as $key=>$value){
        echo "Id : $key  / nom complet :  {$value['nomproduit']} / quantité : {$value['qtepdt']} <br />";  
    }
?>

</body>
</html>
