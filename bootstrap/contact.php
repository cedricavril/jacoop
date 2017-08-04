<?php
	session_start();
	if (!isset($_SESSION['nick'])) header('location: index.php');
	include "../bdd/pdoManager.php";

	$page = "compte de ".$_SESSION['nick'];

	// initialisation
	$texte = isset($_POST['texte']) ? $_POST['texte'] : null;
	$email = isset($_POST['email']) ? $_POST['email'] : null;
	$topic = isset($_POST['topic']) ? $_POST['topic'] : null;
	$id = $_SESSION['id'];

	if ($texte && $email && $topic) {
		if (!requeteSql("INSERT INTO mails (content,topic,destinataire,id_user) VALUES ('$texte','$topic','$email',$id)", "erreur insertion bdd"))
	      	$errorFlash = "erreur insertion dans bdd";
    	else // sinon, message d'invitation à valider son email contenant le lien avec le mdp crypté
			$flash = "Mail envoyé";
	} elseif (isset($_POST['SoumettreMail'])) {
		$errorFlash = "Des champs sont manquants";
	}

    $mails = requeteSql("SELECT *,LEFT(content,50) as preview FROM mails WHERE (id_user=$id)", "erreur lecture bdd mails")->fetchAll(PDO::FETCH_OBJ);
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
  <h2>Mails envoyés</h2>
  <div class="table-responsive">          
  <table class="table table-hover">
    <thead>
      <tr>
        <th class="col-xs-2 col-sm-2 col-lg-2">email</th>
        <th class="col-xs-2 col-sm-2 col-lg-2">sujet</th>
        <th class="col-xs-8 col-sm-8 col-lg-8">message</th>
      </tr>
    </thead>
    <tbody>
<?php foreach ($mails as $mail) {
echo "      
		<tr>
	        <td>$mail->destinataire</td>
	        <td>$mail->topic</td>
	        <td>  <a href='#' data-toggle='tooltip' title='$mail->content'>$mail->preview</a> <!-- si pb, Cf. https://www.w3schools.com/bootstrap/bootstrap_tooltip.asp pour ajouter le script jquery obligatoire d'initialisation -->
			</td>
		</tr>";
} ?>
    </tbody>
  </table>
</div>

<fieldset class="form-group col-xs-12 col-sm-12 col-lg-12">
    <legend>Envoyer un mail</legend>

<!-- affichage d'une notification éventuelle -->
<?php if(isset($flash)) { ?>
  <div class="alert alert-success alert-dismissible fade in col-xs-4 col-sm-4 col-lg-4 col-xs-offset-4 col-sm-offset-4 col-lg-offset-4">
    <strong>Opération effectuée</strong> <?= $flash; ?>
	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
  </div>
<?php } elseif (isset($errorFlash)) { ?>
  <div class="alert alert-danger alert-dismissible fade in col-xs-4 col-sm-4 col-lg-4 col-xs-offset-4 col-sm-offset-4 col-lg-offset-4 alert-dismissible">
    <strong>Erreur</strong> <?= $errorFlash; ?>
	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
  </div>
<?php } ?>

    <form method="post">
	    <div class="form-group col-xs-4 col-sm-4 col-lg-4 col-xs-offset-4 col-sm-offset-4 col-lg-offset-4">
	      <label for="email">Email :</label>
	      <input type="email" class="form-control" id="email" name="email" placeholder="Saisir l'email">
	    </div>
	    <div class="form-group col-xs-4 col-sm-4 col-lg-4 col-xs-offset-4 col-sm-offset-4 col-lg-offset-4">
	      <label for="topic">Sujet :</label>
	      <input type="text" class="form-control" id="topic" name="topic" placeholder="Saisir le topic">
	    </div>
	    <div class="form-group col-xs-4 col-sm-4 col-lg-4 col-xs-offset-4 col-sm-offset-4 col-lg-offset-4">
	      	<label for="texte">Texte :</label>
			<textarea class="form-control" id="texte" name="texte" rows="3" placeholder="Saisir le message"></textarea>
	    </div>
	      <button class="form-group col-xs-2 col-sm-2 col-lg-2 col-xs-offset-5 col-sm-offset-5 col-lg-offset-5" type="submit" class="btn btn-default" id="SoumettreMail" name="SoumettreMail">Soumettre</button>
	</form>
</fieldset>
</div>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

</body>
</html>