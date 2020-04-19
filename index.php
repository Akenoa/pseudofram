<?php 

class Config{ 

    //sous forme de tableau?
    const DB='dbcourse';
    const HOSTNAME='127.0.0.1';
    const USR='root';
    const PWD='';

}

abstract class ModelBase{
    protected $nomTable;
    protected $primaryKeyName;
    
    /* 
    * CONSTRUCTEUR DE CLASSE
    * @param String le nom de la table
    * @param String le nom de la clé primaire
    * Constructeur de ModelBase contient le minimum pour créer un modèle avec un ID
    */
    function  __construct(String $tableName,String $primaryKey){
        $this->nomTable=$tableName; 
        $this->primaryKeyName=$primaryKey;
    }

    /*
    * @optionalParam $FieldToSelect tableau contenant le nom des champs à sélectionné
    * @return String la seconde partie de la requête pour faire une lecture si chaîne vide alors prend tout champs de la table
    * IDEE pour where penser à envoyé 2 tableau field et champs les combine faire un foreach comme dans delete/update
    */
    public function getSelectFromTable($FieldToSelect=null){
        $nomTable = "`".$this->nomTable."`";
        $firstPartRequest="SELECT ";
        $secondPartRequest="";
        if(isset($FieldToSelect) && is_array($FieldToSelect)){
            $fields="`".implode("`,`",$FieldToSelect)."`";
            $secondPartRequest.=" $fields ";

        }
        else {
            $secondPartRequest.="*";
        }
        
        $beforeWherePart=" FROM $nomTable ";

        $finalRequest=$firstPartRequest.$secondPartRequest.$beforeWherePart;
        return $finalRequest;

    }

    /* 
    * @return String la première partie de la requête pour  faire une insertion
    */
    public function getFirstInsertPartRequest():string{
        $firstPartInsertRequest="";
        $nomTable = "`".$this->nomTable."`"; //cas ou ID pas auto increment?
        $firstPartInsertRequest = "INSERT INTO $nomTable ";
        return $firstPartInsertRequest; 
    }


    /* 
    * @return String la première partie de la requête pour  faire une supression
    */
    public function getFirstDeletePartRequest():string{
        $firstPartDeleteRequest="";
        $nomTable = "`".$this->nomTable."`";
        $firstPartDeleteRequest = "DELETE FROM $nomTable ";
        return $firstPartDeleteRequest; 
    }     

    /*
    * @optionalParam $IndexToDelete tableau contenant les idS des enregistrement à supprimer.
    * @return String la seconde partie de la requête pour faire une suppression si chaîne vide alors purge table
    */
    public function getSecondDeletePartRequest($IndexToDelete=null){
        $secondPartRequest="";
        $primaryKey="`".$this->primaryKeyName."`";
        if(isset($IndexToDelete) && is_array($IndexToDelete)){
            $lastElement=end($IndexToDelete);
            $secondPartRequest="WHERE $primaryKey = ";
            foreach($IndexToDelete as $value){
                if($value===$lastElement){
                    $secondPartRequest.=" $value ";

                }
                else{
                    $secondPartRequest.=" $value OR $primaryKey = ";
                }

            }
        }
        return $secondPartRequest;

    }

    /* 
    * @param Array le nom des champs de la table
    * @param Array les valeurs à associé à ces champs.
    * @return String la reuqête complète pour une insertion
    */
    public function getSecondInsertPartRequest(array $ArrayField,array $ArrayValue):string{
        $ArrayFieldValue=array_combine($ArrayField,$ArrayValue);
        $secondPartRequest="";
        $fields="`".implode("`,`",array_keys($ArrayFieldValue))."`"; // `champ1`,`champ2`
        $fieldValues="'".implode("','",$ArrayFieldValue)."'"; //'valeur1','champ1'

        $secondPartRequest="($fields) VALUES ($fieldValues)";
        return $secondPartRequest;

    }
    
    
    
    /*
    * @return String la première partie de la requête pour update
    */ 
    public function getFirstUpdatePartRequest():string{
        $firstPartRequest="";
        $nomTable = "`".$this->nomTable."`";
        //$primaryKey="`".$this->$primaryKeyName."`";
        $firstPartUpdateRequest = "UPDATE `$nomTable` SET ";
        return $firstPartRequest; 

    }

    /*
    * @param Array $FieldToUpdate tableau contenant les champs à updater
    * @param Array $newSetableValue tableau contenant les nouvelles valeursà aattribuer
    * @return String la seconde partie de la requête pour update `champ`=`val`
    */ 
    public function getSecondUpdatePartRequest(array $FieldToUpdate, array $newSetableValue):string {
        $ArrayFieldValue=array_combine($FieldToUpdate,$newSetableValue);
        $lastElem=end($ArrayFieldValue);
        $secondPart="";
        foreach($ArrayFieldValue as $key=>$value){
            if($value===$lastElem){
                $secondPart.=" `$key` = $value";
            }
            else{
                $secondPart.=" `$key` = $value,";

            }
        } 
        return $secondPart;

    } 

    /*
    * @param Array liste des index à modifier dnas la table pour requête update
    * WHERE `pk`='indexN' OR ... 
    * @return String la fin de la requête update avec le where 
    */
    public function getThirdUpdateRequest(array $IndexToUpdate):string{
        $primaryKey="`".$this->primaryKeyName."`";
        $thirdPartRequest=" WHERE $primaryKey = ";
        $lastElem=end($IndexToUpdate);
        foreach($IndexToUpdate as $value){
            if($value===$lastElem) {
                $thirdPartRequest.=" $primaryKey = $value ";
            }
            else
            {
                $thirdPartRequest.=" $primaryKey = $value OR ";
            }
        }
        return $thirdPartRequest;
    }

    /*
    * @param String $request la requete qu'on veut préparer puis executer
    * @return Bool résultat si la requête réussi true sinon false
    */ 
    public function prepareThenExecute(string $request):?bool{
        $objPdo=ConnexionClasse::getCnx();
        $requestStatement=$objPdo->prepare($request);
        try{
            return $requestStatement->execute();
        }
        catch(Exception $error){
            return "Une erreur est survenue au moment de réalisé la requête " . $error->getMessage();
        }
        

    }

    /* 
    * @param Array le nom des champs de la table
    * @param Array les valeursà associé à ces champs.
    * @return String la requête complète pour une insertion
    */
    public function FullInsertRequest(array $ArrayField,array $ArrayValue):string{ 
        $firstPart=$this->getFirstInsertPartRequest();
        $secondPart=$this->getSecondInsertPartRequest($ArrayField,$ArrayValue);
        $finalRequest = $firstPart.$secondPart;
        return $finalRequest;

    }

    /* 
    * @optionalParam $IndexToDelete tableau contenant les idS des enregistrement à supprimer.
    * @return String la requête complète pour une supression
    */
    public function FullDeleteRequest($IndexToDelete=null):string{ 
        $firstPart=$this->getFirstDeletePartRequest();
        $secondPart=$this->getSecondDeletePartRequest($IndexToDelete);
        $finalRequest = $firstPart.$secondPart;
        return $finalRequest;


    } 

    /* 
    * @param $IndexToUpdate tableau contenant les idS des enregistrement à modifier.
    * @param Array $FieldToUpdate tableau contenant les champs à updater
    * @param Array $newSetableValue tableau contenant les nouvelles valeursà aattribuer
    * @return String la requête complète pour une modification
    */
    public function FullUpdateRequest(array $IndexToUpdate,array $FieldToUpdate, array $newSetableValue):string{

        $firstPart=$this->getFirstUpdatePartRequest();
        $secondPart=$this->getSecondUpdatePartRequest($FieldToUpdate,$newSetableValue);
        $thirdPart=$this->getThirdUpdateRequest($IndexToUpdate);
        $finalRequest=$firstPart.$secondPart.$thirdPart;
        return $finalRequest;


    }


}

class ProduitModel extends ModelBase{ 

    private $columnOfTable;

    function  __construct(String $tableName,String $primaryKey,Array $ColumunArray){
        $this->nomTable=$tableName;
        $this->primaryKeyName=$primaryKey;
        $this->columnOfTable=$ColumunArray;
        parent::__construct($this->nomTable,$this->primaryKeyName);
        
    }

    public function getTable():?string{
        return $this->nomTable;

    }
    
    public function getPrimaryKeyName():string{
        return $this->primaryKeyName;
    }

    public function getColumn():array{
        return $this->columnOfTable;
    }
    
    /*INSERT INTO table (nom_colonne_1, nom_colonne_2, ...
 VALUES ('valeur 1', 'valeur 2', ...)*/
    /*public function prepareInsertModel(array $Tabvaleur){ 
        $nomtable = "`".$this->nomTable."`";
        $champs = $this->columnOfTable;
        $chaineDeChamps = "`".implode("`, `", $champs)."`";
        $chaineValeur="'".implode("','", $Tabvaleur)."'";
        $firstPartRequest="INSERT INTO $nomtable ($chaineDeChamps) VALUES($chaineValeur)";
        return $firstPartRequest;
        
    }*/

    /* dans l'éventualité ou on veut faire une requête sur une seule colonne ? 
    public function getSpecifiCol(string $ColumnIWant):?string{ 
        foreach($this->columnOfTable as $columnName){
            if($columnName===$ColumnIWant){
                return $columnName;
            }
        }

    }*/



}

/* FIN DE LA CLASSE FICHIER CNX */
class ConnexionClasse {

    /* attributs static de la classe ConnexionClasse */
    private static $dsn;
    public static $dbh;

    /*  méthode static pour créer une instance pdo */
    public static function getCnx():?PDO{
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
        $newVarArr=['riznouveau','tomatenouvelle'];
        $request=$pdt->FullUpdateRequest($arrInd,$arrCol,$newVarArr);
        return $request;
        //return $pdt->prepareThenExecute($request);


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
