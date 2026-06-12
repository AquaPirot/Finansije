# AG GROUP · Transakcije — uputstvo za postavljanje

Aplikacija radi na vašem hostingu (unlimited.rs / cPanel) sa MySQL bazom.
Adresa: **https://transakcije.aggroup.rs**

## 1. Subdomen (cPanel → Domains / Subdomains)

1. U cPanel-u otvorite **Domains** → **Create A New Domain** (ili **Subdomains**)
2. Napravite subdomen: `transakcije.aggroup.rs`
3. Document Root: npr. `/home/NALOG/transakcije.aggroup.rs`
4. Ako hosting nudi AutoSSL (Let's Encrypt), sertifikat se izdaje automatski —
   proverite u **SSL/TLS Status** da je subdomen pokriven.

## 2. MySQL baza (cPanel → MySQL® Databases)

1. **Create New Database**: npr. `transakcije` (dobićete ime tipa `nalog_transakcije`)
2. **Add New User**: npr. `trans` + jaka lozinka (zapišite je!)
3. **Add User To Database** → izaberite korisnika i bazu → **ALL PRIVILEGES**

## 3. Uvoz tabela (cPanel → phpMyAdmin)

1. Otvorite **phpMyAdmin** i kliknite na novu bazu (levo)
2. Tab **Import** → izaberite fajl `schema.sql` iz ovog repoa → **Go**
3. Trebalo bi da vidite tabele: `transakcije`, `dugovi`, `podesavanja`
   (početno stanje **−104.635 €** je već upisano u `podesavanja`)

## 4. Postavljanje fajlova

U Document Root subdomena postavite (File Manager ili FTP):

```
index.html
api/api.php
api/config.php   ← napravljen od config.example.php, vidi korak 5
```

> Alternativa: cPanel **Git™ Version Control** → Clone ovog repoa
> (https://github.com/AquaPirot/Finansije) direktno u folder subdomena,
> pa samo dodajte `api/config.php` ručno.

## 5. Konfiguracija (api/config.php)

1. Kopirajte `api/config.example.php` kao `api/config.php` **na serveru**
2. Upišite ime baze, korisnika i lozinku iz koraka 2
3. Postavite PIN-ove — svako svoj, 4–8 cifara:

```php
$USERS = [
    '4827' => 'Aleksandar',
    '9153' => 'Daniel',
];
```

> **VAŽNO:** `config.php` sa pravim podacima NIKAD ne šaljite na GitHub
> (repo je javan). On postoji samo na serveru — `.gitignore` ga već isključuje.

## 6. Provera

1. Otvorite **https://transakcije.aggroup.rs**
2. Unesite svoj PIN → treba da vidite stanje **−104.635,00 €**
3. Dodajte probnu transakciju, pa je obrišite
4. Daniel se prijavljuje svojim PIN-om sa svog telefona — vidi iste podatke

## Kako aplikacija radi

- **Saldo** = početno stanje (−104.635 €) + svi prilivi − svi troškovi
- Svaki unos se automatski potpisuje imenom onoga ko je prijavljen
- **Transakcije**: troškovi/plaćanja i prilivi/naplate, sa kategorijama,
  u RSD ili EUR (kurs podesiv u zaglavlju)
- **Dugovi**: posebna evidencija — kome dugujemo / ko nama duguje,
  sa statusom aktivan/izmiren (ne ulaze u saldo dok se ne plate —
  kada platite dug, unesite to kao trošak i označite dug kao izmiren)
- **Izvoz CSV** otvara se u Excelu

## Izmena početnog stanja ili PIN-ova

- Početno stanje: phpMyAdmin → tabela `podesavanja` → `pocetno_stanje_eur`
- PIN-ovi: izmenite `api/config.php` na serveru
