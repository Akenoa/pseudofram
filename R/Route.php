<?php 


class Route{

    private $path;
    private $callable;
    private $matches=[];

    //prend même param que la méthode get
    // dans routeur pour pouvoir instancier 
    //une route directement dans la classe routeur
    public function __construct($path,$callable){
        $this->path=trim($path,'/'); 
        $this->callable=$callable;

    } 

    public function match($url){
        //enlever slash initiuaux et finaux 
        $url=trim($url,'/');
        //remplacer les param par des expression régulière
        // exemple :id on remplace par : suivi de nimporte quel caract alpha numérique plusieurs fois
        // par tout sauf des / plusieurs fois on remplace ça dans le path passé en paramètre
        $path = preg_replace('#:([\w]+)#', '([^/]+)', $this->path);
        $regex = "#^$path$#i"; //verifier tout l'url
        //verifier correspondance
        //entre la totalité du chemin tout ce qu'il y avant après
        //url : l'url de la route qu'on veut
        //matches les résultat retourné par la recherche
        if(!preg_match($regex,$url,$matches)){
            return false;
        } 
        else {
            // dans match on recup le total  de l'url
        // en index 0 du tableau mais on ne souhaite pas le conserver
        array_shift($matches); // degage le premier element 0 du tableau
        $this->matches=$matches; // sauvegarder le match dans une variable pour la route
        return true;
        //var_dump($matches);

        }
        
    }

    public function call(){ 
        //on appel ce qu'on a de colalble càd la closure le function(){} dans index.php
        //var_dump( $this->matches);
        return call_user_func_array($this->callable,$this->matches);
    }

}
