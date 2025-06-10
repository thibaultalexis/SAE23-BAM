<?php
// login.php - Regular user login page
session_start();

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Database connection
    $conn = new mysqli('localhost', 'bam', 'PassRoot', 'sae23bam');

    if ($conn->connect_error) {
        die("La connexion a échoué: " . $conn->connect_error);
    }

    // Sanitize input
    $username = $conn->real_escape_string($username);
    $password = $conn->real_escape_string($password);

    // Check user credentials using prepared statement
    $sql = "SELECT * FROM batiment WHERE Login = ? AND MotDePasse = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Set user session with building permissions
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $username;
        $_SESSION['allowed_buildings'] = [$user['batiment']];
        $_SESSION['last_activity'] = time();
        header("Location: consultation.php");
        exit();
    } else {
        // Redirect to error page on failed login
        header("Location: errcons.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="../styles/feuille_de_style.css">
</head>
<body>
<header>
    <h1>Connexion</h1>
    <nav>
        <ul>
            <li><a href="../index.html">Accueil</a></li>
        </ul>
    </nav>
</header>

<main>
    <!-- User login form -->
    <form method="post" class="login-form">
        <input type="text" name="username" placeholder="Identifiant" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <input type="submit" value="Se connecter">
    </form>
</main>

<footer>
    <ul>
        <li>ALEXIS BISMUTH MONROUZIES BUT1 R&T</li>
    </ul>
</footer>
</body>
</html>