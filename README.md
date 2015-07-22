READ ME!

Feedreader liest RSS Feeds 1.0, 2.0, Atom ein.
Hat Ordner und Filterkriterien.

1.) In PHP MyAdmin einen Nutzer der Rechte auf Datenbank sowie auf Localhost besitzt, erstellen. 
2.) (Unter Linux): Webserver benötigt Schreibrechte im Feedreader Verzeichnis
3.) Mit dem Browser auf Localhost/Feedreader gehen. Weiterleitung auf Setup.php. Benutzer, Datenbank und Passwort eintragen - alle Tabellen werden automatisch erstellt.
4.) Weiterleitung auf Registrierung - User anlegen
5.) Einloggen

Optional:
6.) Bei HTTPS Feeds muss "extension=php_openssl.dll" in php.ini aktiviert sein (darf nicht auskommentiert sein)

Funktionen:
- Feeds einfügen, löschen
- Ordner anlegen, löschen
- Alle Feeds anzeigen, nur Favoriten anzeigen, gelesen/ungelesen anzeigen, mit Bilder/ohne Bilder anzeigen
- Klick auf Ordner: nur Ordnerinhalt wird angezeigt
- Klick auf Feed: nur Artikel des Feeds werden angezeigt
- Feeds als Favoriten, gelesen markieren
- alle angezeigten Artikel als gelesen markieren
- alle Feeds aktualisieren
- Time to live: Zeit, die Artikel in der Datenbank gespeichert bleiben
- Anzahl Artikel die auf der Seite angezeigt werden sollen
- automatischer Login mit Cookie
- Passwort wechseln
- Drap and Drop von Feeds und Ordnern 
- Logout



	
