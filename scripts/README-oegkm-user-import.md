# ÖGKM User-/Mitgliederimport

Dieses Script importiert die exportierten alten WordPress-User inkl. Mitgliedsdaten in die neue User-Struktur.

## Dry-Run

```bash
php wp-content/themes/bootscore-child-oegkm-v1.8/scripts/import-oegkm-users.php --file=$(pwd)/data/user-export-378-6a056d8a3a98e.csv --dry-run
```

## Import

```bash
php wp-content/themes/bootscore-child-oegkm-v1.8/scripts/import-oegkm-users.php --file=$(pwd)/data/user-export-378-6a056d8a3a98e.csv --write
```

## Import mit Limit zum Testen

```bash
php wp-content/themes/bootscore-child-oegkm-v1.8/scripts/import-oegkm-users.php --file=$(pwd)/data/user-export-378-6a056d8a3a98e.csv --write --limit=10
```

## Mapping

- `titel_vor` → `oegkm_member_title`
- `titel_nach` → `oegkm_member_title_after`
- `krankenhaus` → `oegkm_member_institution`
- `zusatz` → `oegkm_member_addition`
- `abteilung` → `oegkm_member_department`
- `strasse` → `oegkm_member_street`
- `plz` → `oegkm_member_zip`
- `ort` → `oegkm_member_city`
- `land` → `oegkm_member_country`
- `website` → `oegkm_member_website`
- `mitgliedsart` → `oegkm_member_type`
- `privacy=on` → `oegkm_member_hide_directory=1`

Passwörter werden **nicht** aus der CSV übernommen. Neue User bekommen ein zufälliges Passwort und sollen später über Passwort-zurücksetzen aktiviert werden.
