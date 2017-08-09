<?php
// ouverture de la bdd en local ----
try {
	if ($_SERVER["REMOTE_ADDR"]=='127.0.0.1') { 
		$db = new \PDO('mysql:host=localhost;dbname=jacoop', 'user1', 'pass') or die("plantage");
	} else {
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
	global $db;

	try {
		$result = $db->prepare($req);
		$result->execute($param);
	}
	catch(PDOException $e) {
			// pour l'insertion, on a une erreur de code 1062 déclenchée pour une violation d'unicité, qui sera retournée à la place de false si autre erreur
			return $result = ($e->errorInfo[1] == 1062) ? 1062 : false;
		}
	return $result;
};