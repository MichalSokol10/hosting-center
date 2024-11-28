<?php
// Funkce exec() spustí příkaz a vrátí výstup jako string
$output = shell_exec("nohup sudo /usr/local/etc/create-site site2.com dement > /dev/null 2>/dev/null &");

echo "ok";

?>