<?php session_start(); if (!isset($_SESSION['admin'])) { header('Location: login_admin.php'); exit; } ?>
<!DOCTYPE html>
<html lang="fr">
<head>
   <meta charset="UTF-8">
   <title>Administration</title>
   <meta name="viewport" content="width=device-width, initial-scale=1"/>
   <link rel="stylesheet" type="text/css" href="/../feuille_de_style.css" media="screen"/>
</head>
<body>
  <h2>Page Administrateur</h2>
  
/*je me souviens plus quoi mettre ici*/  
  
  
  <a href="logout.php">Déconnexion</a>
<footer>
      <ul>
        <li><p>ALEXIS BISMUTH MONROUZIES BUT1 R&T</p></li>
        <li><p></p></li>
      </ul>
  </footer>

</body>
</html>


