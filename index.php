<?php 
/* chargement des fichiers nécessaires
au fonctionement du routeur et de l'application*/
require('vendor/autoload.php');
require('R/router.php');
require('C/pseudocontroller.php');


$router= new Router($_GET['url']);

// Création des routes
$router->get('/',function(){ MasterController::frameworkHomePage() ; });
$router->get('/index',function(){ PseudoController::AllEnregistrement() ; });
$router->get('/produit/:id',function($id){ PseudoController::DetailEnregistrement($id); });

$router->get('/addPdt',function(){ PseudoController::VueInsert(); });
$router->post('/addPdt',function(){ PseudoController::GoInsert(); });

$router->get('/produit/updt/:id',function($id){ PseudoController::VueUpdate($id); });
$router->post('/produit/updt/:id',function($id){ PseudoController::GoUpdate($id); });

$router->get('/produit/dlt/:id',function($id){ PseudoController::VueDelete($id); });
$router->post('/produit/dlt/:id',function($id){ PseudoController::GoDelete($id); });



//execution du système de routage
$router->run();