<?php session_start();
if (isset($_SESSION['nick'])) header('location: contact.php');

include "../bdd/pdoManager.php";

// sécurité : mettre un htaccess deny all dans bdd.
// utiliser hash_equals(data, saisie); pour vérifier le crypt

// initialisation
$page = "enregistrement";
$nick = isset($_GET['nick'])? $_GET['nick'] : null;
$pwd = isset($_GET['pwd'])? $_GET['pwd'] : null;
$email = isset($_GET['email'])? $_GET['email'] : null;

// 1. traitement des variables get éventuelles : si elles correspondent à un mdp et un nick et un email d'une même ligne de la bdd où actif est false, on valide cette ligne en activant l'utilisateur. Sinon message d'erreur l'invitant à se connecter ou se réinscrire.
if($nick && $pwd) {

  if(requeteSql("UPDATE users SET actif=TRUE WHERE nick=:nick AND email=:email AND pwd=:pwd","erreur update",array(":email" => $email, ":nick" => $nick, ":pwd" => $pwd))->rowCount() == 1) $flash = 'Utilisateur enregistré.';
  else $errorFlash = "Utilisateur déjà enregistré ou déjà existant. Essayez de vous connecter ou réinscrivez vous.";

} elseif(isset($_POST['SoumettreEnregistrement'])) {
  // 2. ou bien traitement des variables post d'enregistrement éventuelles : on enregistre un utilisateur si ce dernier a rempli tous les champs et on laisse son compte inactif par défaut

  // initialisation
  $nick = isset($_POST['nick']) ? $_POST['nick'] : null;
  $email = isset($_POST['email']) ? $_POST['email'] : null;
  $pwd = isset($_POST['pwd']) ? $_POST['pwd'] : null;
  $pwd2 = isset($_POST['pwd2']) ? $_POST['pwd2'] : null;

  // si la saisie n'est pas complète, message d'erreur correspondant
  if(!$pwd || !$nick || !$email || !$pwd2) $errorFlash = "Merci de remplir tous les champs";
  elseif ($pwd != $pwd2) $errorFlash = "Les mots de passe ne correspondent pas";
  else {
    var_dump("mot de passe à enregistrer : $pwd");
    $pwd = password_hash($pwd, PASSWORD_DEFAULT);
    var_dump("mot de passe à enregistrer crypté: $pwd");

    // si l'utilisateur ou l'email existe déjà et est non activé, on efface les lignes correspondantes au nick ou à l'email
    if (requeteSql("SELECT * FROM users WHERE actif=0 AND (email='$email' OR nick='$nick')", "erreur lecture")->fetchall()) requeteSql("DELETE FROM users WHERE actif=0 AND (email='$email' OR nick='$nick')", "erreur suppression");

    // S'il est activé, on a violation d'unicité.
    if (requeteSql("INSERT INTO users (nick,email,pwd) VALUES ('$nick','$email','$pwd')", "erreur insertion bdd") === 1062) 
      $errorFlash = "Utilisateur déjà existant";
    else // sinon, message d'invitation à valider son email contenant le lien avec le mdp crypté
      $flash = "Login validé. Merci de confirmer votre enregistrement en cliquant sur le lien envoyé sur votre boite mail. <a href='?pwd=$pwd&nick=$nick&email=$email'>lien</a>";
  }
} elseif(isset($_POST['SoumettreConnexion'])) {
    // 2.1 ou bien traitement des variables post de connexion on initialise la session d'un utilisateur si ce dernier existe dans la bdd, sinon message d'erreur correspondant
    $nickOuEmail = isset($_POST['nickOuEmail']) ? $_POST['nickOuEmail'] : null;
    $pwd = isset($_POST['pwd']) ? $_POST['pwd'] : null;

// on peut avoir un problème ici si un utilisateur choisi un email comme mot de passe. Il faudrait interdire le caractère "@" dans ce dernier lors de l'enregistrement pour lever toute ambigüité
    $user = requeteSql("SELECT * FROM users WHERE (email='$nickOuEmail' OR nick='$nickOuEmail') AND actif=TRUE", "erreur lecture bdd 2")->fetch(PDO::FETCH_OBJ);

    if ($user->nick && password_verify($pwd,$user->pwd)) {
      $flash = "Vous êtes connecté.";
      $_SESSION['nick'] = $user->nick;
      $_SESSION['email'] = $user->email;
      $_SESSION['id'] = $user->id;
    } else {
      $errorFlash = "Identifiants incorrects";
    }

}

if (isset($_GET['connexion'])) $page = "connexion";
if (isset($_GET['contact'])) $page = "contact";

// 3. on pourrait en profiter pour nettoyer la bdd si des comptes sont restés inactifs trop longtemps (nécessiterait une colonne date)
?>
<!DOCTYPE html>
<html lang="fr-FR">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="Exercice à effectuer pour jacoop">
    <meta name="author" content="jacoop">
    <link rel="icon" href="favicon.ico">

    <title>Projet création de site web - <?= $page ?></title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/starter-template.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">adapter la navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Jacoop</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li <?php if($page == 'enregistrement') echo 'class="active"'; ?>><a href="index.php">S'enregistrer</a></li>
            <li <?php if($page == 'connexion') echo 'class="active"'; ?>><a href="?connexion">Se connecter</a></li>
            <li <?php if($page == 'contact') echo 'class="active"'; ?>><a href="?contact">Contact</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container">

      <div class="starter-template">
        <h1>Jacoop</h1>
      </div>
<!-- affichage d'une notification éventuelle -->
<?php if(isset($flash)) { ?>
  <div class="alert alert-success col-xs-4 col-sm-4 col-lg-4 col-xs-offset-4 col-sm-offset-4 col-lg-offset-4">
    <strong>Opération effectuée</strong> <?= $flash; ?>
  </div>
<?php } elseif (isset($errorFlash)) { ?>
  <div class="alert alert-danger col-xs-4 col-sm-4 col-lg-4 col-xs-offset-4 col-sm-offset-4 col-lg-offset-4">
    <strong>Erreur</strong> <?= $errorFlash; ?>
  </div>
<?php } 

/* VIEW.
on charge le contenu selon dans l'ordre : 
qu'on soit connecté,
qu'on veuille confirmer un email, 
qu'on veuille s'enregistrer, 
qu'on veuille se connecter ou
qu'on arrive tout frais sur la page.
*/
switch (true) {
  case isset($_GET['nick']):
    if(!isset($flash) && !isset($errorFlash)) {
      $errorFlash = "Le lien sur lequel vous avez cliqué contient une erreur. Réssayez de vous connecter ou réinscrivez-vous.";
?>
  <div class="alert alert-danger col-xs-4 col-sm-4 col-lg-4 col-xs-offset-4 col-sm-offset-4 col-lg-offset-4">
    <strong>Erreur</strong> <?= $errorFlash; ?>
  </div>
<?php
      include "view/enregistrementEmail.php";
    } else include "view/confirmationEmail.php";
  break;
  case isset($_POST['nick']):
    include "view/enregistrementEmail.php";
  break;
  case isset($_POST['SoumettreConnexion']) || ($page == "connexion"):
    include "view/formulaireConnexion.php";
  break;
  default:
    include "view/formulaire.php";
  break;
}
?>

    </div><!-- /.container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>