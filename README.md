# ClubFinance

ClubFinance ist eine Buchhaltungssoftware für Vereine nach Schweizer Recht.

Die Software ist als Maturitätsarbeit entstanden und steht unter der MIT-Lizenz zur verfügung. Der Urheberrechtsnachweis lautet dabei *Copyright © 2021 Claudio Fleischmann*.

Eine gehostete Version dieser Software steht ausserdem gegen Entgeld zur Verfügung. Weitere Infos finden Sie [hier](https://clubfinance.ch/).

## Installation

1. NGINX oder Apache Webserver mit MySQL und PHP aufsetzen.
2. Composer und Symfony installieren.
3. Ein neues Symfony-Projekt erstellen.
4. Dieses Git-Repo mit dem Symfony-Projekt zusammenführen. Die `composer.json` Datei muss dabei überschrieben werden.
5. Symfony updaten (via `composer.json`) und anschliessend die `composer.lock` generieren.
6. Datenbankverbindung (vorzugsweise MySQL) in der `.env` Datei definieren.
7. Die Datenbank migrieren (mit dem Befehl `php bin/console make:migration`)
8. Die SQL-Abfrage in `_installation/KontenplanEinlesen.sql` ausführen.
9. Ein Admin-Benutzer via `src/Controller/SecurityController.php` definieren.

