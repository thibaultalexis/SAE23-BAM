<!DOCTYPE html>
<html lang="fr">
<head>
   <meta charset="UTF-8">
   <title>Consultation</title>
   <meta name="viewport" content="width=device-width, initial-scale=1"/>
   <link rel="stylesheet" type="text/css" href="../feuille_de_style.css" media="screen"/>
</head>

<body>
  <h2>Dernières mesures des capteurs</h2>
  <?php
  $conn = mysqli_connect('localhost', 'bam', 'PassRoot', 'sae23');
  $res = mysqli_query($conn, "SELECT * FROM mesures ORDER BY date DESC, heure DESC LIMIT 10");
  echo "<table border='1'><tr><th>Capteur</th><th>Date</th><th>Heure</th><th>Valeur</th></tr>";
  while ($row = mysqli_fetch_assoc($res)) {
    echo "<tr><td>{$row['capteur']}</td><td>{$row['date']}</td><td>{$row['heure']}</td><td>{$row['valeur']}</td></tr>";
  }
  echo "</table>";
  ?>
<footer>
      <ul>
        <li><p>ALEXIS BISMUTH MONROUZIES BUT1 R&T</p></li>
        <li><p></p></li>
      </ul>
  </footer>

</body>
</html>


