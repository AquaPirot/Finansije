<?php
// =====================================================
// AG GROUP - Transakcije: API
// Sve ide preko POST JSON: { "action": "...", ... }
// =====================================================

session_start();
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (!file_exists(__DIR__ . '/config.php')) {
    http_response_code(500);
    echo json_encode(['error' => 'Server nije podešen: nedostaje api/config.php (kopirajte config.example.php i popunite podatke).'], JSON_UNESCAPED_UNICODE);
    exit;
}
require __DIR__ . '/config.php';

function db() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}

function out($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function requireAuth() {
    if (empty($_SESSION['user'])) out(['error' => 'Niste prijavljeni.'], 401);
    return $_SESSION['user'];
}

$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) $in = [];
$action = $in['action'] ?? '';

try {
    switch ($action) {

        case 'login': {
            $_SESSION['attempts'] = ($_SESSION['attempts'] ?? 0) + 1;
            if ($_SESSION['attempts'] > 10) {
                out(['error' => 'Previše pogrešnih pokušaja. Sačekajte pa pokušajte ponovo.'], 429);
            }
            $pin = trim((string)($in['pin'] ?? ''));
            if ($pin !== '' && isset($USERS[$pin])) {
                session_regenerate_id(true);
                $_SESSION['user'] = $USERS[$pin];
                $_SESSION['attempts'] = 0;
                out(['user' => $USERS[$pin]]);
            }
            sleep(1); // usporava pogađanje PIN-a
            out(['error' => 'Pogrešan PIN.'], 401);
        }

        case 'logout': {
            session_destroy();
            out(['ok' => true]);
        }

        case 'me': {
            out(['user' => $_SESSION['user'] ?? null]);
        }

        case 'data': {
            requireAuth();
            $trans = db()->query('SELECT * FROM transakcije ORDER BY datum DESC, id DESC')->fetchAll();
            $dugovi = db()->query('SELECT * FROM dugovi ORDER BY status ASC, datum DESC, id DESC')->fetchAll();
            $pod = [];
            foreach (db()->query('SELECT kljuc, vrednost FROM podesavanja') as $r) {
                $pod[$r['kljuc']] = $r['vrednost'];
            }
            out(['transakcije' => $trans, 'dugovi' => $dugovi, 'podesavanja' => $pod]);
        }

        case 'add_trans': {
            $user = requireAuth();
            $tip = $in['tip'] ?? '';
            $opis = trim((string)($in['opis'] ?? ''));
            $iznos = (float)($in['iznos'] ?? 0);
            $valuta = $in['valuta'] ?? 'RSD';
            $kategorija = trim((string)($in['kategorija'] ?? 'Ostalo'));
            $datum = $in['datum'] ?? '';
            $napomena = trim((string)($in['napomena'] ?? ''));

            if (!in_array($tip, ['trosak', 'priliv'], true)) out(['error' => 'Neispravan tip.'], 400);
            if ($opis === '') out(['error' => 'Unesite opis.'], 400);
            if ($iznos <= 0) out(['error' => 'Unesite ispravan iznos.'], 400);
            if (!in_array($valuta, ['RSD', 'EUR'], true)) out(['error' => 'Neispravna valuta.'], 400);
            if ($datum === '') out(['error' => 'Unesite datum.'], 400);

            $st = db()->prepare('INSERT INTO transakcije (tip, opis, iznos, valuta, kategorija, datum, napomena, uneo) VALUES (?,?,?,?,?,?,?,?)');
            $st->execute([$tip, $opis, $iznos, $valuta, $kategorija, str_replace('T', ' ', $datum), $napomena, $user]);
            out(['ok' => true, 'id' => db()->lastInsertId()]);
        }

        case 'update_trans': {
            requireAuth();
            $id = (int)($in['id'] ?? 0);
            $tip = $in['tip'] ?? '';
            $opis = trim((string)($in['opis'] ?? ''));
            $iznos = (float)($in['iznos'] ?? 0);
            $valuta = $in['valuta'] ?? 'RSD';
            $kategorija = trim((string)($in['kategorija'] ?? 'Ostalo'));
            $datum = $in['datum'] ?? '';
            $napomena = trim((string)($in['napomena'] ?? ''));

            if ($id <= 0) out(['error' => 'Neispravan ID.'], 400);
            if (!in_array($tip, ['trosak', 'priliv'], true)) out(['error' => 'Neispravan tip.'], 400);
            if ($opis === '' || $iznos <= 0 || $datum === '') out(['error' => 'Popunite sva obavezna polja.'], 400);

            $st = db()->prepare('UPDATE transakcije SET tip=?, opis=?, iznos=?, valuta=?, kategorija=?, datum=?, napomena=? WHERE id=?');
            $st->execute([$tip, $opis, $iznos, $valuta, $kategorija, str_replace('T', ' ', $datum), $napomena, $id]);
            out(['ok' => true]);
        }

        case 'del_trans': {
            requireAuth();
            $id = (int)($in['id'] ?? 0);
            if ($id <= 0) out(['error' => 'Neispravan ID.'], 400);
            db()->prepare('DELETE FROM transakcije WHERE id=?')->execute([$id]);
            out(['ok' => true]);
        }

        case 'add_dug': {
            $user = requireAuth();
            $tip = $in['tip'] ?? '';
            $kome = trim((string)($in['kome'] ?? ''));
            $opis = trim((string)($in['opis'] ?? ''));
            $iznos = (float)($in['iznos'] ?? 0);
            $valuta = $in['valuta'] ?? 'EUR';
            $datum = $in['datum'] ?? date('Y-m-d');
            $napomena = trim((string)($in['napomena'] ?? ''));

            if (!in_array($tip, ['dugujemo', 'nama_duguju'], true)) out(['error' => 'Neispravan tip duga.'], 400);
            if ($kome === '') out(['error' => 'Unesite ime (kome / ko).'], 400);
            if ($iznos <= 0) out(['error' => 'Unesite ispravan iznos.'], 400);
            if (!in_array($valuta, ['RSD', 'EUR'], true)) out(['error' => 'Neispravna valuta.'], 400);

            $st = db()->prepare('INSERT INTO dugovi (tip, kome, opis, iznos, valuta, datum, napomena, uneo) VALUES (?,?,?,?,?,?,?,?)');
            $st->execute([$tip, $kome, $opis, $iznos, $valuta, $datum, $napomena, $user]);
            out(['ok' => true, 'id' => db()->lastInsertId()]);
        }

        case 'toggle_dug': {
            requireAuth();
            $id = (int)($in['id'] ?? 0);
            if ($id <= 0) out(['error' => 'Neispravan ID.'], 400);
            db()->prepare("UPDATE dugovi SET status = IF(status='aktivan','izmiren','aktivan') WHERE id=?")->execute([$id]);
            out(['ok' => true]);
        }

        case 'del_dug': {
            requireAuth();
            $id = (int)($in['id'] ?? 0);
            if ($id <= 0) out(['error' => 'Neispravan ID.'], 400);
            db()->prepare('DELETE FROM dugovi WHERE id=?')->execute([$id]);
            out(['ok' => true]);
        }

        case 'set_podesavanje': {
            requireAuth();
            $kljuc = $in['kljuc'] ?? '';
            $vrednost = trim((string)($in['vrednost'] ?? ''));
            if (!in_array($kljuc, ['kurs', 'pocetno_stanje_eur'], true)) out(['error' => 'Nepoznato podešavanje.'], 400);
            if ($vrednost === '' || !is_numeric($vrednost)) out(['error' => 'Neispravna vrednost.'], 400);
            $st = db()->prepare('INSERT INTO podesavanja (kljuc, vrednost) VALUES (?,?) ON DUPLICATE KEY UPDATE vrednost=?');
            $st->execute([$kljuc, $vrednost, $vrednost]);
            out(['ok' => true]);
        }

        default:
            out(['error' => 'Nepoznata akcija.'], 400);
    }
} catch (PDOException $e) {
    out(['error' => 'Greška baze podataka. Proverite podešavanja u config.php i da li je schema.sql uvezen.'], 500);
}
