<?php 
require 'vendor/autoload.php';


$router= new App\Router($_GET['url']);

$router->get('/posts',function(){ echo "tous les articles"; });
$router->get('/posts/:id',function($id){ echo "article : ".$id; });
$router->post('/posts/:id',function($id){ echo "poster pour article : ".$id; });

$router->run();
