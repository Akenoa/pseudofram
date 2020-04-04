<?php 

class ConnexionClasse {

    /* static attributes for ConnexionClasse*/
    /*private static $db;
    private static $hostname;
    private static $user;
    private static $pwd;*/
    private static $dsn;
    public static $dbh;

    /* method to create pdo instance */
    public static function CreerPDO(string $db,string $hostname,string $user, string $pwd): ?PDO{
        self::$dsn="mysql:dbname=$db;host=$hostname";
        try{
            self::$dbh = new PDO(self::$dsn,$user,$pwd);
            return self::$dbh;
        } 
        catch(PDOException $err){
            echo "Error : " . $err->getMessage();

        }
        

    }
    
    
}

$db="dbcourse";
$hostname="127.0.0.1";
$usr="root";
$pass="";

$obj=ConnexionClasse::CreerPDO($db,$hostname,$usr,$pass);




$request="SELECT * FROM t_produit";

$statement=$obj->prepare($request);
$statement->execute(); 
$arrayResult=$statement->fetchAll(PDO::FETCH_ASSOC); 
print_r($arrayResult);
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
