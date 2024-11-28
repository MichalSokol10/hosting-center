<?php
// Zahájení session
session_start();

// Odstranění všech proměnných relace
$_SESSION = array();

// Zrušení session
session_destroy();

// Přesměrování uživatele na přihlašovací stránku nebo jinou požadovanou stránku
header("Location: login.php");
exit;
?>
