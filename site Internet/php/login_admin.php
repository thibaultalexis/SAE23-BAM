<?php 
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = mysqli_connect('localhost', 'bam', 'PassRoot', 'sae23bam');
    
    $login = mysqli_real_escape_string($conn, $_POST['login']);
    $mdp = mysqli_real_escape_string($conn, $_POST['motdepasse']);
    
    $sql = "SELECT * FROM Administration WHERE Login = '$login' AND MotDePasse = '$mdp'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) === 1) {
        $_SESSION['admin'] = true;
        $_SESSION['last_activity'] = time();
        header('Location: admin.php');
        exit;
    } else {
        $error = "Login ou mot de passe incorrect";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Login Administrateur</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="stylesheet" type="text/css" href="../styles/feuille_de_style.css" media="screen"/>
</head>
<body>
    <header>
        <h1>Connexion Administrateur</h1>
        <nav>
            <ul>
                <li><a href="../index.html">Accueil</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <input type="text" name="login" placeholder="Login" required>
            <input type="password" name="motdepasse" placeholder="Mot de passe" required>
            <input type="submit" value="Connexion">
        </form>
    </main>

    <footer>
        <ul>
            <li>ALEXIS BISMUTH MONROUZIES BUT1 R&T</li>
        </ul>
    </footer>
</body>
</html>
