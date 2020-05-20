<?php
//ini_set('display_errors', 1);
require_once('M/conf_cnx.php');
require_once('twigloader.php');
require('M/models.php');


 

 
class MasterController{
    /*
    * recupération du nécessaire pour créer un fichier twig, faire un master controller avec ça dedans.
    */
    public static function initTwig(){
        $loader= new \Twig\Loader\FilesystemLoader('./V/');
        $twig=new \Twig\Environment($loader);
        return $twig;

    }

    /*
    * renvoie simplement la vue d'accueil du framework
    */
    public static function frameworkHomePage(){
        $twig = self::initTwig();
        echo $twig->render('FKindex.html.twig');
    }

}


/*****************************PSEUDO CONTROLLER*************************************** */ 
/*****************************PSEUDO CONTROLLER*************************************** */ 
/*****************************PSEUDO CONTROLLER*************************************** */ 
/*****************************PSEUDO CONTROLLER*************************************** */ 
class PseudoController extends MasterController{ 

    /*
    * return ProduitModel $pdt : une instance de la classe ModelProduit
    */
    public static function giveProduitInstance(){
        $array=['nomproduit','qtepdt'];
        $pdt = new ProduitModel('t_produit','id',$array); 
        return $pdt;
    }

    /**
     * Retourne tous les enregistrement de la table sur la vue index.html.twig
     */
    public static function AllEnregistrement(){
        $pdt=PseudoController::giveProduitInstance();
        $request=$pdt->getSelectFromTable();
        $arrayResultat = $pdt->preparethenReadData($request,'assoc');
        $twig=parent::initTwig();
        echo $twig->render('index.html.twig',['dataArray'=>$arrayResultat]);

    }

    /* 
    * Renvoie sur la vue détail d'un produit via son id
    */
    public static function DetailEnregistrement($id){
        $pdt=PseudoController::giveProduitInstance();
        $detailPdt=$pdt->getEnregistrement($id,'num');
        $twig=parent::initTwig();
        echo $twig->render('show.html.twig',['pdt'=>$detailPdt]);

    }

    /**
     * envoie sur le formulaire d'insertion
    */
    public static function VueInsert(){
        $twig=parent::initTwig();
        echo $twig->render('add.html.twig');

    }

        
    /**
    * envoie sur le formulaire de suppression
    */
    public static function VueDelete($id){
        $pdt=PseudoController::giveProduitInstance();
        $twig=parent::initTwig();
        $PdtSpec=$pdt->getEnregistrement($id,'num');
        echo $twig->render('dlt.html.twig',['pdt'=>$PdtSpec]);

    } 

    /**
     * envoie sur le formulaire de modification
    */
    public static function VueUpdate($id){
        $pdt=PseudoController::giveProduitInstance();
        $twig=parent::initTwig();
        $PdtSpec=$pdt->getEnregistrement($id,'num');
        echo $twig->render('updt.html.twig',['pdt'=>$PdtSpec]);

    }

    /*
    * Effectue la suppresion via l'ID passé en  POST et renvoie sur l'index en cas de réussite
    */
    public static function GoDelete($id){
        $pdt=PseudoController::giveProduitInstance();
        $twig=parent::initTwig();
        $PdtSpec=$pdt->getEnregistrement($id,'num');
        $request=$pdt->FullDeleteRequest($id);
        $pdt->prepareThenExecute($request);
        header('Location: ../../index');

    }
    /*
    * Effectue la modification (avec les champs modifié) via l'ID passé en  POST et renvoie sur l'index en cas de réussite
    */
    public static function GoUpdate($id){
        $pdt=PseudoController::giveProduitInstance();
        if(isset($_POST)){
            $arrayField=$pdt->getColumn();
            $arrayValue=$_POST;
            $request=$pdt->FullUpdateRequest($id,$arrayField,$arrayValue);
            $pdt->prepareThenExecute($request);
            header('Location: ../../index');
            
        }
    }

    /*
    * Effectue l'insertion en méthod POST et renvoie sur l'index en cas de réussite
    */
    public static function GoInsert(){
        $pdt=PseudoController::giveProduitInstance();
        if(isset($_POST)){
            $arrayField=$pdt->getColumn();
            $arrayValue=$_POST;
            $request=$pdt->FullInsertRequest($arrayField,$arrayValue);
            $pdt->prepareThenExecute($request);
            header('Location: ./index');
            
        }
    }

}


?>
