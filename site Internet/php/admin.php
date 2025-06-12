<?php 
session_start(); 
if (!isset($_SESSION['admin']) || (time() - $_SESSION['last_activity'] > 600)) { 
    header('Location: login_admin.php'); 
    exit; 
} 
$_SESSION['last_activity'] = time();

$conn = mysqli_connect('localhost', 'bam', 'PassRoot', 'sae23bam');

// Processing changes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_salle'])) {
        $nomSalle = mysqli_real_escape_string($conn, $_POST['NOM_salle']);
        $type = mysqli_real_escape_string($conn, $_POST['type']);
        $capacite = mysqli_real_escape_string($conn, $_POST['capacité']);
        $idBat = mysqli_real_escape_string($conn, $_POST['ID_bat']);
        
        $sql = "UPDATE salle SET type='$type', capacité='$capacite', 
                ID_bat='$idBat' WHERE NOM_salle='$nomSalle'";
        mysqli_query($conn, $sql);
    }
}

$sql = "SELECT s.NOM_salle, s.type, s.capacite, s.ID_bat, 
        b.nom as nom_batiment,
        MAX(CASE WHEN c.type = 'temp' THEN m.valeur END) as temperature,
        MAX(CASE WHEN c.type = 'co2' THEN m.valeur END) as co2,
        MAX(CASE WHEN c.type = 'humi' THEN m.valeur END) as humidite,
        MAX(CASE WHEN c.type = 'lumi' THEN m.valeur END) as luminosite
        FROM Salle s
        LEFT JOIN Batiment b ON s.ID_bat = b.ID_bat
        LEFT JOIN Capteur c ON s.NOM_salle = c.NOM_salle
        LEFT JOIN Mesure m ON c.NOM_capteur = m.NOM_capteur
        WHERE m.date = CURDATE() OR m.date IS NULL
        GROUP BY s.NOM_salle, s.type, s.capacite, s.ID_bat, b.nom";

$result = mysqli_query($conn, $sql);

// Check query
if (!$result) {
    die("Erreur de requête : " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration</title>
    <link rel="stylesheet" href="../styles/feuille_de_style.css">
</head>
<body>
    <header>
        <h1>Administration des Salles</h1>
        <nav>
            <ul>
                <li><a href="../index.html">Accueil</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <table>
            <tr>
                <th>Salle</th>
                <th>Type</th>
                <th>Capacité</th>
                <th>Bâtiment</th>
                <th>Température (°C)</th>
                <th>Humidité (%)</th>
                <th>Luminosité (lux)</th>
                <th>CO2 (ppm)</th>
                <th>Actions</th>
            </tr>
            <?php 
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <form method="post">
                            <input type="hidden" name="NOM_salle" value="<?php echo htmlspecialchars($row['NOM_salle']); ?>">
                            <td><?php echo htmlspecialchars($row['NOM_salle']); ?></td>
                            <td><input type="text" name="type" value="<?php echo htmlspecialchars($row['type']); ?>"></td>
                            <td><?php echo htmlspecialchars($row['luminosite']); ?></td>
                            <td><input type="text" name="capacité" value="<?php echo htmlspecialchars($row['capacité']); ?>"></td>
                            <td>
                                <select name="ID_bat">
                                    <?php
                                    $batiments = mysqli_query($conn, "SELECT ID_bat, nom FROM batiment");
                                    while($bat = mysqli_fetch_assoc($batiments)) {
                                        $selected = ($bat['ID_bat'] === $row['ID_bat']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($bat['ID_bat']) . "' $selected>" . 
                                             htmlspecialchars($bat['nom']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><?php echo htmlspecialchars($row['temperature']); ?></td>
                            <td><?php echo htmlspecialchars($row['co2']); ?></td>
                            <td><?php echo htmlspecialchars($row['humidite']); ?></td>
                            <td>
                                <input type="submit" name="update_salle" value="Modifier">
                            </td>
                        </form>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="8">Aucune donnée disponible</td>
                </tr>
            <?php } ?>
        </table>
    </main>

    <footer>
        <ul>
            <li>ALEXIS BISMUTH MONROUZIES BUT1 R&T</li>
        </ul>
    </footer>
</body>
</html>
