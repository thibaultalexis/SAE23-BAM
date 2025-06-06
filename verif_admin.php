<?php
session_start();
$conn = mysqli_connect('localhost', 'bam', 'PassRoot', 'sae23');
$login = $_POST['login'];
$mdp = $_POST['mdp'];

$sql = "SELECT * FROM Administration WHERE login='$login' AND mdp='$mdp'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) == 1) {
  $_SESSION['admin'] = $login;
  header('Location: admin.php');
} else {
  echo "<p>Identifiants incorrects</p><a href='login_admin.php'>Retour</a>";
}
?>
