<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Check authorizations
$batiment = 'A'; 
$allowedBuildings = $_SESSION['allowed_buildings'] ?? [];
if (!in_array($batiment, $allowedBuildings)) {
    header('Location: errcons.php');
    exit;
}

// Connect to database
$conn = mysqli_connect('localhost', 'bam', 'PassRoot', 'sae23bam');

// Get selected period
$period = $_POST['period'] ?? '24h';
$periods = [
    '2w' => 'INTERVAL 14 DAY',
    '1w' => 'INTERVAL 7 DAY',
    '3d' => 'INTERVAL 3 DAY',
    '24h' => 'INTERVAL 24 HOUR',
    '12h' => 'INTERVAL 12 HOUR',
    '1h' => 'INTERVAL 1 HOUR'
];

$interval = $periods[$period];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Consultation Bâtiment <?php echo $batiment; ?></title>
    <link rel="stylesheet" href="../feuille_de_style.css">
</head>
<body>
    <header>
        <h1>Données du Bâtiment <?php echo $batiment; ?></h1>
        <nav>
            <ul>
                <li><a href="consultation.php">Retour</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <form method="post" class="period-selector">
            <select name="period" onchange="this.form.submit()">
                <option value="2w" <?php if($period === '2w') echo 'selected'; ?>>2 dernières semaines</option>
                <option value="1w" <?php if($period === '1w') echo 'selected'; ?>>1 semaine</option>
                <option value="3d" <?php if($period === '3d') echo 'selected'; ?>>3 derniers jours</option>
                <option value="24h" <?php if($period === '24h') echo 'selected'; ?>>Dernières 24h</option>
                <option value="12h" <?php if($period === '12h') echo 'selected'; ?>>Dernières 12h</option>
                <option value="1h" <?php if($period === '1h') echo 'selected'; ?>>Dernière heure</option>
            </select>
        </form>

        <?php
        $sql = "SELECT m.Date, m.Horaire, m.Valeur, c.TypeCapteur, s.NomSalle
                FROM Mesure m
                JOIN Capteur c ON m.NomCapteur = c.NomCapteur
                JOIN Salle s ON c.NomSalle = s.NomSalle
                WHERE s.BatID = '$batiment'
                AND m.Date >= DATE_SUB(CURRENT_DATE, $interval)
                ORDER BY m.Date DESC, m.Horaire DESC";
        
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            echo "<table>
                    <tr>
                        <th>Salle</th>
                        <th>Type Capteur</th>
                        <th>Valeur</th>
                        <th>Date</th>
                        <th>Heure</th>
                    </tr>";
            
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$row['NomSalle']}</td>
                        <td>{$row['TypeCapteur']}</td>
                        <td>{$row['Valeur']}</td>
                        <td>{$row['Date']}</td>
                        <td>{$row['Horaire']}</td>
                    </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Aucune donnée disponible pour cette période.</p>";
        }
        ?>
    </main>

    <footer>
        <ul>
            <li>ALEXIS BISMUTH MONROUZIES BUT1 R&T</li>
        </ul>
    </footer>
</body>
</html>
