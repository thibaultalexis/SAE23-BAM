<?php
// consultation.php - Main consultation dashboard
session_start();

// Check user authentication
if (!isset($_SESSION['username'])) {
header('Location: login.php');
exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Consultation</title>
    <link rel="stylesheet" href="../styles/feuille_de_style.css">
</head>
<body>
<header>
    <h1>Consultation des données</h1>
    <nav>
        <ul>
            <li><a href="../index.html">Accueil</a></li>
            <li><a href="logout.php">Déconnexion</a></li>
        </ul>
    </nav>
</header>

<main>
    <div class="building-buttons">
        <!-- Show building buttons based on user permissions -->
        <?php if (in_array('A', $_SESSION['allowed_buildings'])): ?>
            <form method="post" action="consa.php">
                <button type="submit" name="building" value="A">Bâtiment A (Administratif)</button>
            </form>
        <?php endif; ?>

        <?php if (in_array('B', $_SESSION['allowed_buildings'])): ?>
            <form method="post" action="consb.php">
                <button type="submit" name="building" value="B">Bâtiment B (Info)</button>
            </form>
        <?php endif; ?>

        <?php if (in_array('C', $_SESSION['allowed_buildings'])): ?>
            <form method="post" action="consc.php">
                <button type="submit" name="building" value="C">Bâtiment C (CS)</button>
            </form>
        <?php endif; ?>

        <?php if (in_array('E', $_SESSION['allowed_buildings'])): ?>
            <form method="post" action="conse.php">
                <button type="submit" name="building" value="E">Bâtiment E (RT)</button>
            </form>
        <?php endif; ?>
    </div>
</main>

<footer>
    <ul>
        <li>ALEXIS BISMUTH MONROUZIES BUT1 R&T</li>
    </ul>
</footer>
</body>
</html>
