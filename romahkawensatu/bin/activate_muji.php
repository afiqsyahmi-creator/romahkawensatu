<?php
$pdo = new PDO('mysql:host=localhost;dbname=romahkawensatu', 'root', '');
$pdo->exec("UPDATE studio SET status='active' WHERE studio_id=7");
echo 'Done. Muji Studio is now active.' . "\n";
