<?php 
session_start(); 
include 'dbconnect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Účet</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom Styles */
        body {
            padding-top: 70px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
        }
        .mt-md-5 {
            margin-top: 3rem !important;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <h1 class="navbar-brand">Účet</h1>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Domů</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="AboutUs.php">O nás</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Kontakt</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Účet</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-md-5">
        <?php
        // Kontrola, zda je uživatel již přihlášen
        if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true){
            // Pokud je již přihlášený, přesměrujeme ho na jinou stránku nebo zobrazíme příslušnou zprávu
            echo "<p>Přihlášen jako: " . $_SESSION['user'] . "</p>";

            // Tlačítko pro odhlášení
            echo '<form method="post" action="logout.php">';
            echo '<input type="submit" name="logout" value="Odhlásit se" class="btn btn-danger">';
            echo '</form>';

            // Tlačítko pro smazání hostingu
            echo '<form method="post" action="deleteHosting.php">';
            echo '<input type="submit" name="deleteHosting" value="Smazat hosting" class="btn btn-warning mt-3">';
            echo '</form>';
            
            exit;
        }

        // Kontrola, zda byl formulář odeslán
        if(isset($_POST['submit'])) {
          

            // Získání zadané domeny a hesla
            $domena = $_POST['domena'];
            $password = $_POST['password'];
            

            // Připojení k databázi
            try {
                $user = 'postgres';
                $pass = 'pepa';
                $dbh = new PDO($dsn, $user, $pass);

                // Příprava dotazu SQL pro kontrolu uživatele a hesla
                $stmt = $dbh->prepare("SELECT password FROM users WHERE domain = :domain");
                $stmt->bindParam(':domain', $domena);
                $stmt->execute();

                // Získání výsledků dotazu
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Pokud existuje uživatel s danou doménou a heslem, provede se přihlášení
                if ($user) {
                    $hashedPass = $user['password'];
                    if (password_verify($password, $hashedPass)) {
                        // Přihlášení úspěšné
                        $_SESSION['logged_in'] = true;
                        $_SESSION['user'] = $domena;
                        echo '<script>window.location.href = "login.php";</script>';
                    } else {
                        // Přihlášení neúspěšné
                        echo '<p class="text-danger">Neplatné uživatelské jméno nebo heslo.</p>';
                    }
                } else {
                    // Přihlášení neúspěšné
                    echo '<p class="text-danger">Neplatné uživatelské jméno nebo heslo.</p>';
                }
            } catch (PDOException $e) {
                echo "Chyba při připojení k databázi: " . $e->getMessage();
            }
        }
        ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="domena">Doména:</label>
                <input type="text" class="form-control" id="domena" name="domena" required>
            </div>
            <div class="form-group">
                <label for="password">Heslo:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Přihlásit se</button>
        </form>
    </div>

    <!-- Bootstrap JS (optional) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
