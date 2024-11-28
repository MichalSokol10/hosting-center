<?php 
session_start(); 
include 'dbconnect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smazání hostingu</title>
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
            <h1 class="navbar-brand">Smazání hostingu</h1>
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
        <p>Provedeno smazání hostingu pro doménu </p>
    </div>

    <!-- Bootstrap JS (optional) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Kontrola, zda je uživatel přihlášen
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Pokud uživatel není přihlášen, přesměrujeme ho na stránku přihlášení
    header('Location: login.php');
    exit;
}

// Získání uživatelského jména z session
$userDomain = $_SESSION['user'];

// Nahradí jedno nebo více podtržítek jednou tečkou
$domainWithDot = preg_replace('/_+/', '.', $userDomain);


try {
    // Získání vlastníka databáze
    $stmt = $dbh->prepare("SELECT pg_catalog.pg_get_userbyid(d.datdba) AS db_owner
                           FROM pg_catalog.pg_database d
                           WHERE datname = :dbName");
    $stmt->bindParam(':dbName', $userDomain);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $dbOwner = $result['db_owner'];
    
    // Ukonečení všech aktivních připojení k databázi
    $stmt = $dbh->prepare("SELECT pg_terminate_backend(pg_stat_activity.pid)
                       FROM pg_stat_activity
                       WHERE pg_stat_activity.datname = :dbName
                         AND pid <> pg_backend_pid();");
    $stmt->bindParam(':dbName', $userDomain);
    $stmt->execute();

    // Smazání záznamu z tabulky users
    $stmt = $dbh->prepare("DELETE FROM users WHERE domain = :domain");
    $stmt->bindParam(':domain', $userDomain);
    $stmt->execute();
    echo "Záznam byl úspěšně smazán z tabulky users.<br>";

    // Smazání databáze
    $stmt = $dbh->prepare("DROP DATABASE IF EXISTS $userDomain");
    $stmt->execute();
    echo "Databáze $userDomain byla úspěšně smazána.<br>";

    // Smazání uživatele z PostgreSQL
    $stmt = $dbh->prepare("DROP USER IF EXISTS $dbOwner");
    $stmt->execute();
    echo "Uživatel $dbOwner byl úspěšně smazán z PostgreSQL.";

    //smazání uživatele a prostoru na nginx a ftp
    exec( "nohup sudo /usr/local/etc/delete-site " . $domainWithDot . " > /dev/null 2>/dev/null &");
} catch (PDOException $e) {
    echo "Chyba: " . $e->getMessage();
    exit;
}

//odhlášení
$_SESSION = array();
// Zrušení session
session_destroy();
?>
