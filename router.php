<?php 
namespace App;

class Router{ 

    //url courant et un tableau contenant toutes les routes
    private $url;
    //trié les méthodes pour éviter de tout trié
    // tri des url get ensemble post delete etc rout['GET'] pour les  urls en get
    // route['POST'][] pour les routes en post..
    private $routes = [];

    public function __construct($url){
        $this->url=$url;
    }

    // path chemin et callable la fonction qu'on va appeler
    // un peu comme en laravel 
    public function get($path,$callable){

        $route = new Route($path,$callable);
        
        $this->routes['GET'][]=$route;


    }

    public function post($path,$callable){

        $route = new Route($path,$callable);
        $this->routes['POST'][]=$route;


    }

    // verifie si url tapé correspond à une des url
    public function run(){
        // recupérer la method HTTP 
        if(!isset($this->routes[$_SERVER['REQUEST_METHOD']])){
            throw new RouteurException('REQUEST_METHOD DOES NOT EXIST') ;
        }

        //parcours les routes en fonction de la méthode get post etc...
        foreach($this->routes[$_SERVER['REQUEST_METHOD']] as $route){
            // la route correspond elle a l'url actuellement tapée?
            if($route->match($this->url)){
                // si ça match on appel la closure passé en paramètre
                return $route->call(); //appel de méthode
            }
        }
        throw new RouteurException('NOT MATCHING ROUTE');
        
    }




}
