<?php 

$dsn="mysql:dbname=dbcourse";
$user="root";
$pwd="";  
$arrayResult=[];
try{
    $dbh=new PDO($dsn,$user,$pwd);
}
catch(PDOException $e){
    echo "Erreur : " . $e->getMessage();
} 

$request="SELECT * FROM t_produit";

$statement=$dbh->prepare($request);
$statement->execute(); //NB execute return a boolean
$arrayResult=$statement->fetchAll(PDO::FETCH_ASSOC); //fetchassoc create associative array
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
