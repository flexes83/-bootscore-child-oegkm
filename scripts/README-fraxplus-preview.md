# FRAXplus Mitglieder-Vorschau

Direktlink: `/mitgliederbereich/fraxplus-vorschau/`. Die virtuelle Route wird in `inc/fraxplus.php` vor der normalen WordPress-Templateauswahl gerendert. Kein Rewrite-Flush und kein Seiten-Datensatz nötig. Keine Menü-/Dashboard-/Sitemap-Einträge. Login und bestehende Mitgliedsrolle erforderlich; authentifizierte Nichtmitglieder erhalten 403. HTTP- und Robots-Header verhindern Caching und Indexierung. Der Direktlink ist kein Ersatz für die Zugriffsprüfung.

## Stand 29.10.2026

- Fragebogen, BMD-Eingabe, zehn einzelne Anpassungen und Ergebnisvergleich im ÖGKM-Design.
- API-Freischaltung fehlt. Live-Berechnung ist standardmäßig deaktiviert.
- „Beispiel laden“ verwendet ausschließlich den von Felix gezeigten Fall: 50 Jahre, weiblich, 70 kg, 160 cm, frühere Fraktur ja, elterliche Hüftfraktur ja, restliche Faktoren nein, ohne BMD. Anzeige FRAX 16 % / 1,6 %; feste NPF-Anpassung für zwei frühere Frakturen 18 % / 1,8 %. Keine eigenen medizinischen Formeln. Jede Änderung der Basisdaten verwirft das Ergebnis. Andere Beispielkombinationen werden nicht berechnet.
- Keine Patientenakten, Ergebnis-Historie oder Browser-Speicherung. Eingaben bleiben bis zur bewussten Live-Berechnung im Browser-Arbeitsspeicher. Keine personenbezogenen Kennungen erforderlich.

## Nach Anbieterfreischaltung

API-Key ausschließlich außerhalb des Themes in der Serverumgebung als `OEGKM_FRAXPLUS_API_KEY` oder als gleichnamige Konstante in `wp-config.php` hinterlegen. Er wird nie an den Browser geliefert. Zusätzlich muss `OEGKM_FRAXPLUS_ENABLED` ausdrücklich auf `true` gesetzt sein. Vor Aktivierung die konkrete Lizenz, Anbieter-Datenschutzhinweise und Ergebnisse mit synthetischen Fällen prüfen; die Live-Anbindung konnte ohne Freischaltung noch nicht verifiziert werden.

REST: `POST /wp-json/oegkm/v1/fraxplus/calculate`, authentifizierte WP-Sitzung + `X-WP-Nonce` + Mitgliedsrolle. Basiswerte werden serverseitig normalisiert und validiert. API-Aufrufe laufen ausschließlich zu `https://www.fraxplus.org/api/v1/current-user`, `/frax` und `/frax/adjust`, mit API-Key, CSRF-Token und ggf. Sitzungscookies. Keine Patientendaten in GET-URLs oder Transients; der einzige Transient zählt Benutzeranfragen. Fehler-/Teilantworten werden nicht als bestätigte Anpassung dargestellt.

Quelle: https://www.fraxplus.org/api-docs, Swagger-Version 1.4.9-production-968dd79d. Eine Anpassung pro Anfrage. Österreich ist vorerst fest auf Ländermodell 16 gesetzt. BMD-Voraussetzungen, TBS-BMI und Messgeräte werden geprüft. PHPT-Schema enthält eine Unstimmigkeit (`required: phpt`, dokumentierte Eigenschaft `phptWithoutPtx`); vor Live-Aktivierung insbesondere diesen Fall mit dem Anbieter prüfen. Die API enthält außerdem abweichende Schreibweisen für BMI `unit`/Beispiel `units`; die vorbereitete Anfrage folgt dem offiziellen Beispiel mit `units: metric` und muss live bestätigt werden. UI-Eingabegrenzen ergänzen eine defensive Plausibilitätsprüfung; die medizinische Ergebnisvalidierung bleibt beim Anbieter.

## Checks

`php tests/fraxplus-test.php` prüft Zugriff, Nonce, Basis-/Zusatzvalidierung, keine Übertragung ohne Aktivierung, Schlüssel-/Sitzungshandling, Teilantworten und Rate-Limit mit gemocktem HTTP. PHP- und JS-Syntaxprüfungen ergänzen die Checks. Lokaler Browsercheck bestätigt Beispielauswahl, feste Ergebniswerte und Ablehnung veränderter Beispielkombinationen. Authentifizierte Live-Berechnung erst nach Freischaltung testen.
