<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Účet</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Účet</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
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

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>


<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = 'postgres'; // Use the service name as the host
    $db   = 'postgres'; // Your database name
    $user = 'postgres'; // Default PostgreSQL user
    $pass = 'pepa'; // Password you defined in docker-compose.yml
    $charset = 'utf8mb4';

    // Získání dat z formuláře v index.php
    $domain = $_POST['domain'];
    $domainWithDot = $_POST['domainWithDot'];
    $userFromForm = $_POST['user']; // Uživatel z formuláře
    $email = $userFromForm;
    $userFromForm = str_replace('@', '_', $userFromForm); // Nahrazení znaku '@' za pomlčku '-'
    $userFromForm = str_replace('.', '_', $userFromForm); 
    $password = $_POST['password'];
    $hashedPass = password_hash($password, PASSWORD_DEFAULT);

    
  
    $dsn = "pgsql:host=$host;dbname=$db;user=$user;password=$pass";
    try {
        $pdo = new PDO($dsn);
        //echo "Connected successfully";
      
        // Získání seznamu vlastníků databází
        $stmt = $pdo->query("SELECT usename FROM pg_user");
        $existingUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Kontrola, zda hodnota v $userFromForm není obsažena v seznamu vlastníků
        if (in_array($userFromForm, $existingUsers)) {
            echo "Uživatel s názvem $userFromForm již existuje jako vlastník databáze.";
        } else {
      
        // Vytvoření databáze s názvem získaným z formuláře
        $pdo->exec("CREATE DATABASE $domain;");

        // Vytvoření uživatele s heslem
        $pdo->exec("CREATE USER $userFromForm WITH ENCRYPTED PASSWORD '$password';");
        
        // Nastavení vlastnictví databáze na uživatele
        $pdo->exec("ALTER DATABASE $domain OWNER TO $userFromForm;");
        
        // Přidělení práv uživateli na databázi
        $pdo->exec("GRANT ALL PRIVILEGES ON DATABASE $domain TO $userFromForm;");
        
        // Zamezení přístupu public uživateli k databázi
        $pdo->exec("REVOKE ALL PRIVILEGES ON DATABASE $domain FROM public;");

        exec( "nohup sudo /usr/local/etc/create-site " . $domainWithDot . " " . $password . " > /dev/null 2>/dev/null &");

        $stmt = $pdo->prepare("CALL create_user(?, ?)");
        $stmt->execute([$domain, $hashedPass]);
          
        // Zobrazení zprávy s informacemi o vytvořené databázi a uživateli
        echo "<p><h2>PostgreSQL Databáze</h2>";
        echo "Databáze: $domain<br>";
        echo "Uživatel: $userFromForm<br>";
        echo "Heslo: $password</p>";

        echo "<p><br><h2>FTP</h2>";
        echo "Uživatelské jméno: $domainWithDot<br>";
        echo "Heslo: $password</p>";

        echo "<p><br><h2>Webové rozhraní</h2>";
        echo "Uživatelské jméno: $domain<br>";
        echo "Heslo: $password</p>";
        
        }
          
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
  
}
?>