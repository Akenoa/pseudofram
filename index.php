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

    public static function ReqAdd(PDO $pdoObject, Array $ParamArray){      
        $msg="";
        $compteur=0;
        $stmt=null;
        $nbElem=count($ParamArray);
        $firstPartRequest="";
        $secondPartRequest="";
        $finalInsertRequest="";  

        $firstPartRequest="INSERT INTO ". $ParamArray['table']." ( ";
        //array_shift($ParamArray);
        $arrayFieldValue=array_splice($ParamArray,1,$nbElem-1);
        $nbElemFieldValue=count($arrayFieldValue);
        $secondPartRequest=" VALUES (";
        foreach ($arrayFieldValue as $key => $value) {
            
            $firstPartRequest.= " '$key', ";
            $secondPartRequest.= "'$value', ";
            $compteur=$compteur+1;
            if($compteur===$nbElemFieldValue){ 
                //en mettant un break on voit qu'il y a un tour de trop ds la boucle. why?!
                //le nb element est bon et le tableau aussi.
                $firstPartRequest.="'$key')";
                $secondPartRequest.="'$value' )";
            
            }
        } 
        $finalInsertRequest=$firstPartRequest.$secondPartRequest;
        $stmt=$pdoObject->prepare($finalInsertRequest);
        $var = $stmt->execute();
        $nbelem2=count($arrayFieldValue);
        return $arrayFieldValue;
        //$message="Insertion OK";
            
      //array_splice($tableauTrie,$i,0,$key);      
        


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
    "nomproduit"=>"paquet de nouilles",
    "qtepdt"=>"1",
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
