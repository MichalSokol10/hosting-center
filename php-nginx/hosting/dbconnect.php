<?php
$dsn = 'pgsql:host=postgres;port=5432;dbname=postgres;user=postgres;password=pepa';
try {
    $dbh = new PDO($dsn);
    //echo 'Připojení k databázi bylo úspěšné!';
} catch (PDOException $e) {
    echo 'Připojení selhalo: ' . $e->getMessage();
}
?>