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

}

/* FIN DE LA DEUXIEME CLASSE */

$db="dbcourse";
$hostname="127.0.0.1";
$usr="root";
$pass="";

$obj=ConnexionClasse::CreerPDO($db,$hostname,$usr,$pass);

$tableauReqInsert=[
    "table"=>'t_produit',
    "nomproduit"=>"poisson",
    "qtepdt"=>"3",
];

$insertion=RequeteClasse::ReqAdd($obj,$tableauReqInsert);
var_dump($insertion);


$request="SELECT * FROM t_produit";
$statement=$obj->prepare($request);
$statement->execute(); 
$arrayResult=$statement->fetchAll(PDO::FETCH_ASSOC);  
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
