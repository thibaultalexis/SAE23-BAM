<?php
// admin.php - Administrative interface for room management
session_start();

// Check admin session and timeout (10 minutes)
if (!isset($_SESSION['admin']) || (time() - $_SESSION['last_activity'] > 600)) {
    header('Location: login_admin.php');
    exit;
}
$_SESSION['last_activity'] = time();

// Database connection
$conn = mysqli_connect('localhost', 'bam', 'PassRoot', 'sae23bam');

// Handle form submissions for room updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_salle'])) {
        // Sanitize input data
        $nomSalle = mysqli_real_escape_string($conn, $_POST['NOM_salle']);
        $type = mysqli_real_escape_string($conn, $_POST['type']);
        $capacite = mysqli_real_escape_string($conn, $_POST['capacité']);
        $idBat = mysqli_real_escape_string($conn, $_POST['ID_bat']);

        // Update room information
        $sql = "UPDATE salle SET type='$type', capacité='$capacite', 
                ID_bat='$idBat' WHERE NOM_salle='$nomSalle'";
        mysqli_query($conn, $sql);
    }
}

// Get room data with latest sensor readings
$sql = "SELECT s.NOM_salle, s.type, s.capacité, s.ID_bat, b.nom as nom_batiment,
        MAX(CASE WHEN c.type = 'température' THEN m.valeur END) as temperature,
        MAX(CASE WHEN c.type = 'CO2' THEN m.valeur END) as co2,
        MAX(CASE WHEN c.type = 'humidité' THEN m.valeur END) as humidite
        FROM salle s
        LEFT JOIN batiment b ON s.ID_bat = b.ID_bat
        LEFT JOIN capteur c ON s.NOM_salle = c.NOM_salle
        LEFT JOIN mesure m ON c.NOM_capteur = m.NOM_capteur
        WHERE m.date = CURDATE() OR m.date IS NULL
        GROUP BY s.NOM_salle, s.type, s.capacité, s.ID_bat, b.nom";
$result = mysqli_query($conn, $sql);
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
            <th>CO2 (ppm)</th>
            <th>Humidité (%)</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <!-- Each row is a form for editing room data -->
                <form method="post">
                    <input type="hidden" name="NOM_salle" value="<?php echo htmlspecialchars($row['NOM_salle']); ?>">
                    <td><?php echo htmlspecialchars($row['NOM_salle']); ?></td>
                    <!-- Editable fields -->
                    <td><input type="text" name="type" value="<?php echo htmlspecialchars($row['type']); ?>"></td>
                    <td><input type="text" name="capacité" value="<?php echo htmlspecialchars($row['capacité']); ?>"></td>
                    <td>
                        <!-- Building dropdown -->
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
                    <!-- Read-only sensor data -->
                    <td><?php echo htmlspecialchars($row['temperature']); ?></td>
                    <td><?php echo htmlspecialchars($row['co2']); ?></td>
                    <td><?php echo htmlspecialchars($row['humidite']); ?></td>
                    <td>
                        <input type="submit" name="update_salle" value="Modifier">
                    </td>
                </form>
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