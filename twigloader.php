<?php 
require_once('vendor/autoload.php');
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new \Twig\Loader\FilesystemLoader(__DIR__.DIRECTORY_SEPARATOR.'V'.DIRECTORY_SEPARATOR);
$twig = new \Twig\Environment($loader);