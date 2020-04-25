25/04/2020 (le document est en évolution car des modifications vont survenir au fur et à mesure de la création du FrameWork il permet de faire le point sur ce qu'il se passe.)

Avancement sur le FrameWork

fichier conf_cnx.php

Classe Config

Contient des variables constantes pour se connecter à une base de donnée (le nom de la base de donnée, l'hôte,l'utilisateur et le mot de passe.). 

Interrogation: mettre ces constantes sous forme de tableau?

Classe ConnexionClasse

Une seule fonction static qui permet de renvoyer une instance PDO pour faire la connexion. Un attribut privé static $dsn qui contient la chaîne complète (datasourcename) :

mysql:dbname=MaBaseDeDonne;host=MonHote. Si la connexion réussi une instance de PDO est retournée sinon null.  



fichier index.php

require_once le fichier de conf_cnx.php, j'utilise require car la page en a vraiment BESOIN et pas simplement include car le script ne peut pas fonctionner sans la partie de connexion. Un require pour le fichier models.php car il est nécessaire pour créer nos models et requête dessus. 

Classe PseudoController

Ne sert qu'à réaliser des test pour le moment. Les fonctions de cette classes sont statics et ne nécessite pas d'instance de l'objet PseudoController. 

Le fichier contient pour le moment la partie vue avec un peu de html histoire d'avoir de la mise en forme minimale pendant les tests. Il y a une balise PHP ouverte dans ce code html juste pour faire les test voir si les appels fonctionne bien entre la vue et le PseudoController. Il faudra déterminer par la suite si c'est possible de simplement envoyer le résultat dans une vue comme dans les framework plus classiques mais ça viendra dans un second temps.



fichier test.php

Pas grand chose à dire, il sert à simplement à tester une fonction de PHP, des expériences sur les tableaux quand je ne suis pas sûre du résultat en lisant la documentation. C'est un peu un fichier témoin pour le moment. 



fichier models.php

Classe abstract ModelBase

3 attributs protégés: le nom de la table et la clé primaires et un tableau contenant le nom des colonnes

La classe dispose d'un constructeur qui sert à donner des valeurs aux 3 attributs protégés via la création d'une classe fille. Chaque modèle dispose au minimum d'un nom et d'une clé primaire et d'une colonne (fourni sous forme de tableau), le tableau est fourni sous la forme suivante 

$tableauColonne['nomcolonne']=>'nomcolonne'

La classe contient le nécessaire pour réaliser des requêtes et récupérer les résultats (notamment dans le cadre d'un SELECT.)

Confirmation: on ne peut pas accèder aux méthodes protégés en dehors de la classe fille même sur un objet enfant, surtout si comme dans mon cas on se situe dans une autre classe(PseudoController en l'occurence)

pour la lecture on a

méthode getSelectFromTable permet de créer une requête SELECT complète selon les choix de l'utilisateurs. 

    public function getSelectFromTable($FieldToSelect=null,$whereClause=null,$orderByClause=null,$limitClause=null):string
    

Elle peut recevoir jusqu'à 4 paramètres, mais ils sont optionnels si aucun n'est renseigné on fait un SELECT * de la table. On peut aussi moduler passé un premier paramètre NULL et remplir la clause WHERE pour faire une recherche. On retourne la requête complète sous forme de chaîne à la fin.

- public function prepareThenReadData(string request, string  fetchingMode="both"):?array
  si aucune valeur n'est renseigné pour $fetchingMode on fait un fetch_both sinon on peut précisier num ou assoc pour obtenir soit un tableau indexé numérique ou un tableau associatif avec le nom des colonnes.





Pour éviter de trop surchargé les fonctions j'ai découpé la création de requête : 

pour les insertions on a : 

- getFirstInsertPartRequest():string // retourne une chaîne qui contient 

    INSERT INTO `nomTable`



- getSecondInsertPartRequest(array TableauChamps, array TableauValeur) qui retourne la suite de la requête. La fonction attend comme paramètre la liste des champs dans les quels on souhaite faire une insertion et les valeurs qu'on veut associé à ces champs (à mettre dans le même ordre.)

(champ1,champs2) VALUES ('valeur1','valeur2')



- FullInsertRequest(array ArrayField,array ArrayValue):string 

Attends les même paramètre que getSecondInsertPartRequest() qui permet de les transmettre à cette dernière. Cette fonction appel getFirstInsertPartRequest() puis getSecondInsertPartRequest() et constitue la requête finale d'insertion qui est alors retournée. 



pour les suppressions on a :

- public function getFirstDeletePartRequest():string

retourne DELETE FROM nomtable

- public function getSecondDeletePartRequest($IndexToDelete=null):string 

 La clé primaire est automatiquement récupéré dans cette fonction.

Paramètre optionnel des IDs ou l'on souhaite faire une suppression, si rien n'est passé une chaîne vide est retourné. On aura donc un DELETE FROM nomtable et ça va purger la table. Si le tableau d'index est fourni on obtient une requête de la forme : DELETE FROM nomTable WHERE cléPrimaire='VAL1' OR cléPrimaire='VAL2'. (le OR ne se créer que s'il y a plusieurs index dans le tableau fourni en paramètre.)

- public function FullDeleteRequest($IndexToDelete=null):string

Paramètre optionnel des IDs ou l'on souhaite faire une suppression pour la fonction getSecondDeletePartRequest, si rien n'est passé une chaîne vide est retourné. On aura donc un DELETE FROM nomtable et ça va purger la table. La requête complète de suppresion est réalisé.



pour les modification on a :  

- getFirstUpdatePartRequest():string;
  Réalise la première partie de la requête et la retourne UPDATE nomTable SET
- getSecondUpdatePartRequest(array FieldToUpdate,array newSetableValue):string; 
  Attends les champs à updater dans un premier et temps et les valeurs de ceux ci (à mettre dans le même ordre comme pour insertion). et réalise la partie champ='NouvelleValeur' de la requête et la retourne.

- getThirdUpdateRequest(array $IndexToUpdate):string;
  Réalise la partie WHERE on update sur un enregistrement ou la clé primaire = une valeur ou plusieurs (si une seule valeur dans le tableau alors pas de OR)
- FullUpdateRequest(array IndexToUpdate,array FieldToUpdate, array $newSetableValue):string
  Rassemble toutes les précédentes partie de la requête pour un update et la retourne au complet, attends les même paramètre que les sous méthodes appelées pour pouvoir les transmettre. 

réflexion sur les paramètres nécessaire à l'insertion et la modification : 

le but est que l'utilisateur puisse modifier ce qu'il souhaite ou passer directement toutes les colonnes via la méthode getColumn par exemple. Penser à créer des fonctions de construction de tableau pourrait être une idée mais il faudrait de toute façon penser à l'autre dans le quel mettre les valeurs pour les clés...

réflexion sur la modification :

on va tester sur le multi request d'abords sur MysqlServer en SQL avec des requêtes conditionnelles et ensuite on l'implémentera en PHP. 

Multirequest basique C'est basique c'est de nouvelle valeur à des colonnes sur plusieurs ids. framework on plutôt tendances à fonctionner comme ça... : ce qui est déjà mis en place

 Index=[

1=>['nom'=>'Doe',

         'age'=>30 ]

2=>['nom'=>'Doe',

         'age'=>30 ]

3=>['nom'=>'Doe',

         'age'=>30 ]

] 

On peut aussi penser à:

Tableau=[

'champ'=>['val1','val2','val3'],

'champ2'=>['val1','val2','val3'] 

]

Sinon 1 req : 

Tableau=['field'=>'value',

'field2'=>'value2']

Obliger à la construction de tableau avant de faire la requête.

reflexion sur update: le multi request avancé sur update avec plusieurs champs/valeurs diff === compliqué pas l'impression que les orm classique le traite. [1,2,3] 

Tableau=[

1=>[

 'nom'=>'jean'

]

2=>[

 'age'=>20,'

ville'=>'Rouen'

] ,

3=>[

'prenom'=>'Jane', 

'nom'=>'Doe'

]

pour les fonctions insert update delete: 

- public function prepareThenExecute(string $request) 

Se connecte à la base,prépare la requête passée en paramètre et si elle échoue un message d'erreur survient sinon renvoie un boléen à vrai..





Classe ProduitModel

1 attribut privé les colonnes de la table. 

Constructeur attends un nom de table, une clé primaire et un tableau contenant le nom des colonnes même s'il y a une seule colonne. Utilise le constructeur du parent pour donner des valeurs aux attributs protégées de la classe mère ModelBase.  



Contient fonctions :

NB: ces fonctions pourrait être dans la classe mère notamment les 2 premières. a étudier pour le moment le doc est réalisé en fonction du code déjà écrit.

- getTable qui retourne une chaîne contenant le nom de la table.
- getPrimaryKeyName qui retourne une chaîne contenant le nom de la clé primaire de la table.
- getColumn qui retourne sous forme de tableau les colonnes pour cette table.
- getColumnAndPk retourne un tableau avec en première clé : la clé primaire de la table et les autres clés contiennent les colonnes de la table.
