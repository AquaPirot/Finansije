-- =====================================================
-- AG GROUP - Transakcije (transakcije.aggroup.rs)
-- Uvezite ovaj fajl u phpMyAdmin (tab "Import")
-- =====================================================

CREATE TABLE IF NOT EXISTS transakcije (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tip ENUM('trosak','priliv') NOT NULL,
  opis VARCHAR(500) NOT NULL,
  iznos DECIMAL(14,2) NOT NULL,
  valuta ENUM('RSD','EUR') NOT NULL DEFAULT 'RSD',
  kategorija VARCHAR(60) NOT NULL DEFAULT 'Ostalo',
  datum DATETIME NOT NULL,
  napomena VARCHAR(500) NOT NULL DEFAULT '',
  uneo VARCHAR(30) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dugovi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tip ENUM('dugujemo','nama_duguju') NOT NULL,
  kome VARCHAR(120) NOT NULL,
  opis VARCHAR(500) NOT NULL DEFAULT '',
  iznos DECIMAL(14,2) NOT NULL,
  valuta ENUM('RSD','EUR') NOT NULL DEFAULT 'EUR',
  datum DATE NOT NULL,
  status ENUM('aktivan','izmiren') NOT NULL DEFAULT 'aktivan',
  napomena VARCHAR(500) NOT NULL DEFAULT '',
  uneo VARCHAR(30) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS podesavanja (
  kljuc VARCHAR(50) PRIMARY KEY,
  vrednost VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pocetne vrednosti: kurs i pocetno stanje -104.635 EUR
INSERT INTO podesavanja (kljuc, vrednost) VALUES
  ('kurs', '117.20'),
  ('pocetno_stanje_eur', '-104635')
ON DUPLICATE KEY UPDATE vrednost = vrednost;
