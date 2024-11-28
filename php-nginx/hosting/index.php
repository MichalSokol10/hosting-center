<?php 
session_start(); 
include 'dbconnect.php';

function generateRandomPassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    
    // Add at least one lowercase letter, one uppercase letter, and one digit
    $password .= $characters[rand(10, 35)]; // Lowercase letter
    $password .= $characters[rand(36, 61)]; // Uppercase letter
    $password .= $characters[rand(0, 9)];   // Digit
    
    // Add more random characters
    for ($i = 0; $i < $length - 3; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    // Shuffle the password randomly
    $password = str_shuffle($password);
    
    return $password;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webhosting</title>
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
            <a class="navbar-brand" href="#">Webhosting</a>
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
        <h1 class="mb-4">Zjistěte dostupnost domény</h1>
        <form method="post" action="">
            <div class="form-group">
                <label for="domain">Jméno domény:</label>
                <input type="text" class="form-control" id="domain" name="domain"  pattern="[a-zA-Z0-9]+(?:\.[a-zA-Z0-9]+)*$" required>
            </div>
            <div class="form-group">
                <label for="extension">Přípona:</label>
                <select class="form-control" id="extension" name="extension">
                    <option value="cz">.cz</option>
                    <option value="com">.com</option>
                    <option value="org">.org</option>
                    <!-- Add more extensions as needed -->
                </select>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Zjistit dostupnost</button>
        </form>

        <?php
        if(isset($_POST['submit'])) {
            $domain = $_POST['domain'] . "__" . $_POST['extension'];
            $domain = str_replace(".", "_", $domain);
            $domainWithDot = $_POST['domain'] . "." . $_POST['extension'];
            $query = $dbh->query("SELECT 1 FROM pg_database WHERE datname = '$domain'");
            $dbExists = $query->fetch();
            if($dbExists > 0 || $domainWithDot == "hosting.com") {
                echo "<p class='mt-3 alert alert-danger'>Domena $domainWithDot není dostupná.</p>";
            } else {
                echo "<p class='mt-3 alert alert-success'>Domena $domainWithDot je dostupná.</p>";
                $password = generateRandomPassword();
                echo '<form method="post" action="submit.php">';
                echo '<input type="hidden" name="domain" value="' . htmlspecialchars($domain) . '">';
                echo '<input type="hidden" name="domainWithDot" value="' . $domainWithDot . '">';
                echo '<input type="hidden" name="password" value="' . htmlspecialchars($password) . '">';
                echo '<div class="form-group">';
                echo '<label for="user">Email:</label>';
                echo '<input type="email" id="user" name="user" class="form-control" required>';
                echo '</div>';
                echo '<button type="submit" name="buy" class="btn btn-success">Koupit doménu</button>';
                echo '</form>';
            }
        }   
        ?>
    </div>

    <!-- Bootstrap JS (optional) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
