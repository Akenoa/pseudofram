<?php
abstract class ModelBase{
    protected $nomTable;
    protected $primaryKeyName;
    protected  $modelProperties;
    
    /* 
    * CONSTRUCTEUR DE CLASSE
    * @param String le nom de la table
    * @param String le nom de la clé primaire
    * Constructeur de ModelBase contient le minimum pour créer un modèle avec un ID
    */
    function  __construct(String $tableName,String $primaryKey,Array $modelPropertiesArray){
        $this->nomTable=$tableName; 
        $this->primaryKeyName=$primaryKey;
        $this->modelProperties=$modelPropertiesArray;
        $CorrectKeyArray=array_fill_keys($modelPropertiesArray,$this->modelProperties);
        $CorrectKeyValueArray=array_combine($modelPropertiesArray,$this->modelProperties);
        //$var=array_flip($PropertiesArray);
        $this->modelProperties=$CorrectKeyValueArray;
    }

    /*
    * @return string nomtable : le nom de la table.
    */
    public function getNomTable():string{
        return $this->nomTable;

    }
    /*
    * @return string primaryKeyName : le nom de la clé primaire de la table.
    */
    public function getPrimaryKeyName():string{
        return $this->primaryKeyName;
    } 

    /*
    * @return array modelPropertiesArray : le nom des colonnes de la table sans la clé primaire 
    */
    public function getColumn():array{
        return $this->modelProperties;
    }
    
    /*
    * @return array primaryKeyName : le nom des colonnes de la table sans la clé primaire 
    */
    public function getPkANDColumn():array{ 

        array_unshift($this->modelProperties,$this->primaryKeyName);
        return $this->modelProperties;
    }


    /*
    * @optionalParam $FieldToSelect tableau contenant le nom des champs à sélectionné sinon fait un select *
    * @optionalParam $whereClause chaine contenant la clause where
    * @optionalParam $orderByClause chaine contenant la clause order by
    * @optionalParam $limitClause chaine contenant la clause limit
    * @return String la seconde partie de la requête pour faire une lecture si chaîne vide alors prend tout champs de la table
    */
    public function getSelectFromTable($FieldToSelect=null,$whereClause=null,$orderByClause=null,$limitClause=null){
        $nomTable = "`".$this->nomTable."`";
        $firstPart="SELECT ";
        $beforeWherePart=" FROM $nomTable ";
        $secondPart="";
        
        //prend champs ou tout si rien d'indiqué
        if(isset($FieldToSelect) && is_array($FieldToSelect)){
            $fields="`".implode("`,`",$FieldToSelect)."`";
            $secondPart.=" $fields ";

        }
        else {
            $secondPart.="*";
        } 
        //clause where
        if(isset($whereClause) && is_string($whereClause)){
            $wherePart = " WHERE ".$whereClause;
        }
        else {
            $wherePart="";
        }
        //clause order by
        if(isset($orderByClause) && is_string($orderByClause)){
            $orderPart = " ORDER BY $orderByClause ";
        }
        else {
            $orderPart="";
        }
        //clause limit 10 10,50 ... etc
        if(isset($limitClause) && is_string($limitClause)){
            $limitPart = " LIMIT ".$limitClause;
        }
        else {
            $limitPart="";
        }
        
        

        $finalRequest=$firstPart.$secondPart.$beforeWherePart.$wherePart.$orderPart.$limitPart;
        return $finalRequest;

    }

    /* 
    * @return String la première partie de la requête pour  faire une insertion
    */
    public function getFirstInsertPartRequest():string{
        $firstPartInsert="";
        $nomTable = "`".$this->nomTable."`"; //cas ou ID pas auto increment?
        $firstPartInsert = "INSERT INTO $nomTable ";
        return $firstPartInsert; 
    }


    /* 
    * @return String la première partie de la requête pour  faire une supression
    */
    public function getFirstDeletePartRequest():string{
        $firstPart="";
        $nomTable = "`".$this->nomTable."`";
        $firstPart = " DELETE FROM $nomTable ";
        return $firstPart; 
    }     

    /*
    * @optionalParam $IndexToDelete tableau contenant les idS des enregistrement à supprimer.
    * @return String la seconde partie de la requête pour faire une suppression si chaîne vide alors purge table
    */
    public function getSecondDeletePartRequest($IndexToDelete=null){
        $secondPart="";
        $primaryKey="`".$this->primaryKeyName."`";
        if(isset($IndexToDelete) && is_array($IndexToDelete)){
            $lastElement=end($IndexToDelete);
            $secondPart="WHERE $primaryKey = ";
            foreach($IndexToDelete as $value){
                if($value===$lastElement){
                    $secondPart.=" $value ";

                }
                else{
                    $secondPart.=" $value OR $primaryKey = ";
                }

            }
        }
        return $secondPart;

    }

    /* 
    * @param Array le nom des champs de la table 
    * si l'id est  AI on peut le mettre à null ça va insérer si pas AI on peut mettre une valeur grâce aux tableau.
    * @param Array les valeurs à associé à ces champs.
    * @return String la requête complète pour une insertion
    */
    public function getSecondInsertPartRequest(array $ArrayField,array $ArrayValue):string{
       
        $ArrayFieldValue=array_combine($ArrayField,$ArrayValue);
        $secondPart="";
        $fields="`".implode("`,`",array_keys($ArrayFieldValue))."`"; // `champ1`,`champ2`
        $fieldValues="'".implode("','",$ArrayFieldValue)."'"; //'valeur1','champ1'

        $secondPart="($fields) VALUES ($fieldValues)";
        return $secondPart;

    }
    
    
    
    /*
    * @return String la première partie de la requête pour update
    */ 
    public function getFirstUpdatePartRequest():string{
        $firstPart="";
        $nomTable = "`".$this->nomTable."`";
        //$primaryKey="`".$this->$primaryKeyName."`";
        $firstPart = "UPDATE $nomTable SET ";
        return $firstPart; 

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
                $secondPart.=" `$key` = '$value'";
            }
            else{
                $secondPart.=" `$key` = '$value',";

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
        $thirdPart=" WHERE ";
        $lastElem=end($IndexToUpdate);
        foreach($IndexToUpdate as $value){
            if($value===$lastElem) {
                $thirdPart.=" $primaryKey = '$value' ";
            }
            else
            {
                $thirdPart.=" $primaryKey = '$value' OR ";
            }
        }
        return $thirdPart;
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

    /*
    * @param String $request la requete qu'on veut préparer puis executer
    * @return Array résultat sous forme de tableau  indexé numérique démarrant à 0.
    
    public function prepareThenReadDataAssocNum(string $request):?Array{
        $objPdo=ConnexionClasse::getCnx();
        $requestStatement=$objPdo->prepare($request);
        $requestStatement->execute();
        $resultDataFETCH[] = $requestStatement->fetchAll(PDO::FETCH_NUM);
        $dataReadArray=[];
        foreach($resultDataFETCH as $key=>$value){
            $dataReadArray[$key]=$value;
        }
        return $dataReadArray;


    }*/

    /*
    * @param String $request la requete qu'on veut préparer puis executer
    * @return Array résultat sous forme de tableau avec de sindex numérique et aussi clé en tant qu'index(associatif)
    
    public function prepareThenReadDataAssocAll(string $request):?Array{
        $objPdo=ConnexionClasse::getCnx();
        $requestStatement=$objPdo->prepare($request);
        $requestStatement->execute();
        $dataReadArray=[];
        $resultDataFETCH[] = $requestStatement->fetchAll(PDO::FETCH_BOTH);
        foreach($resultDataFETCH as $key=>$value){
            $dataReadArray[$key]=$value;
        }
        return $dataReadArray;


    }*/

    /*
    * @param String $request la requete qu'on veut préparer puis executer
    * @return Bool résultat si la requête réussi true sinon une chaîne avec un message
    */ 
    public function prepareThenExecute(string $request){
        $objPdo=ConnexionClasse::getCnx();
        $requestStatement=$objPdo->prepare($request);
        if($requestStatement->execute()){
            return $requestStatement->execute();
        }
        else{
            $msg="Il y a une erreur sur la requête";
            return $msg;
        }
        
        
        

    }
    /*
    * @param String $request la requete qu'on veut préparer puis executer
    * @optionalParam String $fetchingMode le mode de lecture qu'on souhaite pour le tableau par défaut : both
    * autre valeur possible : num ou assoc
    * @return Array résultat sous forme de tableau associatif.
    */
    public function prepareThenReadData(string $request,string $fetchingMode="both"):?Array{
        $objPdo=ConnexionClasse::getCnx();
        $requestStatement=$objPdo->prepare($request);
        $requestStatement->execute();
        $dataReadArray=[];
        if($fetchingMode==="num")$resultDataFETCH[] = $requestStatement->fetchAll(PDO::FETCH_NUM);
        elseif($fetchingMode==="assoc")$resultDataFETCH[] = $requestStatement->fetchAll(PDO::FETCH_ASSOC);
        else $resultDataFETCH[] = $requestStatement->fetchAll(PDO::FETCH_BOTH);
        
        foreach($resultDataFETCH as $key=>$value){
            $dataReadArray[$key]=$value;
        }
        return $dataReadArray;


    }


}

class ProduitModel extends ModelBase{ 


    function  __construct(String $tableName,String $primaryKey,Array $PropertiesArray){
        $this->nomTable=$tableName;
        $this->primaryKeyName=$primaryKey;
        $this->modelProperties=$PropertiesArray;
        
        
        parent::__construct($this->nomTable,$this->primaryKeyName,$this->modelProperties);
        
    }


    public function getNomProduit():string{
        //return  $this->modelProperties;
        return $this->modelProperties['nomproduit'];

    }

    public function getQtePdt():string{
        //return  $this->modelProperties;
        return $this->modelProperties['qtepdt'];

    }

    /* on retire les méthodes magique source de bug à cause du typage php. Privilégier les constructions avec les tableaux.
    public function __set($property,$value){
        return $this->modelProperties[$property] = $value;
    } 

    public function __get($property){

        return array_key_exists($property,$this->modelProperties) ? $this->modelProperties[$property] : null;
    }*/

  }
