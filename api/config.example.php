<?php
// =====================================================
// AG GROUP - Transakcije: konfiguracija
//
// 1. Kopirajte ovaj fajl kao config.php (u istom folderu)
// 2. Upišite podatke MySQL baze iz cPanel-a
// 3. Postavite PIN-ove (4-8 cifara, različite za svakog)
//
// VAŽNO: config.php sa pravim podacima NIKAD ne ide na
// GitHub - ostaje samo na serveru.
// =====================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'IME_BAZE');        // npr. aggroup_transakcije
define('DB_USER', 'KORISNIK_BAZE');   // npr. aggroup_trans
define('DB_PASS', 'LOZINKA_BAZE');

// PIN => ime (unos se automatski potpisuje ovim imenom)
$USERS = [
    '0000' => 'Aleksandar',   // PROMENITI!
    '1111' => 'Daniel',       // PROMENITI!
];
