<?php 
class Config{ 

    const DB='dbname';
    const HOSTNAME='127.0.0.1';
    const USR='root';
    const PWD='';

}

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
