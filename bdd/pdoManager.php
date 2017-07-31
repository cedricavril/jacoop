<?php
// ouverture de la bdd en local
try {
	if ($_SERVER["REMOTE_ADDR"]=='127.0.0.1') { 
		$db = new \PDO('mysql:host=localhost;dbname=jacoop', 'user1', 'pass') or die("plantage");
	} else {
		// ne sert pas ici
		$db = new \PDO('mysql:host=atelierggsgab.mysql.db;dbname=', '', '') or die("plantage de la bdd");
	}
	$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	$db->exec("SET CHARACTER SET utf8");
	if ($db->connect_error) {
	    die("Connection failed: " . $db->connect_error);
	} // else echo "connected successfully";
}
catch(PDOException $e) {
	die('erreur PDO');
}

function requeteSql($req, $erreur, $param = null) {
	// continuer ici : pour insertion, voir quelle code erreur correspond à exception d'unicité et retourner ce dernier
	global $db;

var_dump('un test t1 avec '.$req);

	try {
		$result = $db->prepare($req);
		$result->execute($param);
	}
	catch(PDOException $e) {
			$result = $e->erreurInfo[1];
			echo "erreur : ".$result;
		}
	return $result;
}

function searchName($name) {
global $db;

};

function saveName($name) {
global $db;

};