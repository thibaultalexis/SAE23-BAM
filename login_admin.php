<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
   <meta charset="UTF-8">
   <title>Login Administrateur</title>
   <meta name="viewport" content="width=device-width, initial-scale=1"/>
   <link rel="stylesheet" type="text/css" href="../feuille_de_style.css" media="screen"/>
</head>
<body>
  <h2>Connexion Administrateur</h2>
  <form method="post" action="verif_admin.php">
    <input type="text" name="login" placeholder="Login" obligatoire>
    <input type="password" name="mdp" placeholder="Mot de passe" obligatoire>
    <input type="submit" value="Connexion">
  </form>

<footer>
      <ul>
        <li><p>ALEXIS BISMUTH MONROUZIES BUT1 R&T</p></li>
        <li><p></p></li>
      </ul>
  </footer>

</body>
</html>



