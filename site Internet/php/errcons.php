<?php
// errcons.php - Access denied error page
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Erreur d'accès</title>
    <link rel="stylesheet" href="../styles/feuille_de_style.css">
</head>
<body>
<header>
    <h1>Accès refusé</h1>
    <nav>
        <ul>
            <li><a href="consultation.php">Retour</a></li>
        </ul>
    </nav>
</header>

<main>
    <!-- Error message display -->
    <div class="error-message">
        <h2>Accès non autorisé</h2>
        <p>Vous n'avez pas les autorisations nécessaires pour accéder aux données de ce bâtiment.</p>
        <p>Veuillez contacter votre administrateur si vous pensez qu'il s'agit d'une erreur.</p>
    </div>
</main>

<footer>
    <ul>
        <li>ALEXIS BISMUTH MONROUZIES BUT1 R&T</li>
    </ul>
</footer>
</body>
</html>