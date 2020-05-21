20/05/2020 : le framework est fonctionnel, mais il est toujours améliorable, n"anmoins considéré comme terminé ! 

**Architecture du projet :** 

- M : dossier modèle qui contient les fichiers de configuration de connexion et la classe qui permet de réaliser les connexions. 
- C : dossier qui contient le fichier pseudocontroller qui contient les classes mastercontroller et pseudocontroller (controller d'exemple pour l'application) 
  V : dossier contient toutes les vues (fihcier en .html.twig)  
- R: dossier qui contient les fichier nécessaire au routage : la classe route,la classe router et la classe routerexception. 
- Le dossier vendor contient les dépendance nécessaire au fonctionnement de l'application.  
- le fichier htaccess sert à rediriger vers index.php qui contient toutes les routes de l'application (c'est dans ce fichier qu'il faut les définir)
- le fichier twigloader sert à charger l'environnement pour que le moteur de template twig fonctionne.

**DOSSIER M**

fichier : conf_cnx.php

**Classe Config**

Contient des variables constantes pour se connecter à une base de donnée (le nom de la base de donnée, l'hôte,l'utilisateur et le mot de passe.). 



**Classe ConnexionClasse**

Une seule fonction static qui permet de renvoyer une instance PDO pour faire la connexion. Un attribut privé static $dsn qui contient la chaîne complète (datasourcename) :

mysql:dbname=MaBaseDeDonne;host=MonHote. Si la connexion réussi une instance de PDO est retournée sinon null.  



fichier models.php (contient la partie ORM générique + un exemple de modèle concret pour le fonctionnement du framework.) 



La classe ModelBase contient le nécessaire à la réalisation des requêtes (lecture,insertion,modification et suppression), l'ensemble de l'ORM a été pensé pour fonctionner avec des tableaux notamment pour les formulaires qui utilisent les super variables $_POST et $_GET pour pouvoir récupérer les valeurs plus facilement via des formulaires.  Tous les modèles à créer doivent alors hérités de la classe ModelBase!

ModelBase à 3 propriétés protégées : le nom de la table,le nom de la clé primaire et un tableau contenant la liste des autres noms de colonne. (obligatoirement un tableau même si une seule colonne, un modèle à forcément une autre colonne en plus de l'id/clé primaire notamment pour les clé primaire qui serait des codes et dont le nom des codes ne seraient pas explicites !)
En plus des fonctions de CRUD basique et de récupération d'enregistrement, on retrouve évidement des getter et setter sur les propriétés (dont un qui nous permet de récupérer le nom des colonnes sous forme de tableau et un qui permet de récupérer la clé primaire et le nom des colonnes sous forme de tableau.)



ModelConcret(ProduitModel ici)

Hérite de modèle base, son constructeur passe les paramètre nécessaire à son parent. 



Exemple de création d'un getter : il faut les créer manuellement ,pendant un temps l'idée d'utilisation de méthode magique a été utilisé mais elles devient vite source de bug (notamment à cause de typages trop souple de php) donc a été abandonnée!

```php
public function getNomProduitColonne():string{
        return $this->modelProperties['nomproduit'];

    }
```





**FICHIER OUT DOSSIER**

**fichier index.php**

contient toutes les routes pour l'applications et charge les éléments nécessaire au fonctionnement de celle-ci, le fichier se charge de lancer le routage du système via l'appel de la fonction run();

require_once le fichier de conf_cnx.php, j'utilise require car la page en a vraiment BESOIN et pas simplement include car le script ne peut pas fonctionner sans la partie de connexion. Un require pour le fichier models.php car il est nécessaire pour créer nos models et requête dessus. 

**.htaccess** : fait la redirection automatique vers index.php 

**twigloader.php** : charge environnement nécessaire au fonctionnement du moteur de template twig.  



**DOSSIER V** : ne contient que les vues en html.twig.

**DOSSIER R** 

**RouterException.php** : extends la classe native de php Exception juste pour pouvoir retourner un message plus explicite en cas de débugage (plus claire de savoir si le problème vient du routage ou du code PHP en général.) 

**Route.php** 

3 attributs privés : $path : le chemin de la route, $callable : la closure en lien avec cette route, et $matches=[]; (un tableau qui contient tout les résultats possibles qui peuvent matcher cette route). 

la fonction match sert à voir si l'url sur laquelle on se retrouve match une de nos routes. 

call sert à appeler la closure, càd la fonction qui s'execute en lien avec la route  



Exemple de route : 



```php
$router->get('/addPdt',function(){ PseudoController::VueInsert(); });
$router->post('/addPdt',function(){ PseudoController::GoInsert(); });

$router->get('/produit/updt/:id',function($id){ PseudoController::VueUpdate($id); });
$router->post('/produit/updt/:id',function($id){ PseudoController::GoUpdate($id); });
```



**Router.php**

2 propriétés privées : url qui va correspondre à l'url courant et routes=[] qui va contenir toutes les instances de la classe route du framework. 

Un routeur s'instancie de la façon suivante : 

```php
$router= new Router($_GET['url']);
//une fois les routes créer comme vu précédemment on peut lancer le router de cette fçaon :
$router->run();
```

2 méthodes : post et get qui servent à savoir par quoi les données passe et permettent de faire un premier tri, ce qui évitera par la suite d'avoir à trier toutes les routes, on triera seulement celle dont on a besoin (selon si c'est un get ou un post). 

Le tableau routes se conçoit de la façon suivant : 

```php
$route=newRoute($path,$callable)
$this->routes['GET'][]=$route ;
//ou on retrouve aussi l'alternative suivante : 
$this->routes['POST'][]=$route ;
```



**DOSSIER C** 

**fichier pseudocontroller.php** 

charge les modèles.

**Classe MasterController** permet de charger l'endroit ou seront repertoriés les vue et contient aussi le renvoie vers la vue "Accueil de Framework". 



**Classe PseudoCondroller** (exemple de controller concret ) HERITE DE MASTERCONTROLLER :

créer une instance de la classe produit dans une méthode fiveProduitInstance(); qui permettre d'utiliser un objet de type produit pour réaliser nos requêtes (un peu comme les framework classique)

Toutes les autres méthodes sont des méthodes de CRUD classiques et renvoie vers les vues correspondantes. Les fonctions ont été imaginé et crée sur l'idée de resource de Laravel (une méthode qui correspond à une route en get qui envoie vers le formulaire et la même en post pour réaliser l'action)

