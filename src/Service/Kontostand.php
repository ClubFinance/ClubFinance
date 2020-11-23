<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Hauptbuch;
use App\Entity\Kontenplan;

class Kontostand {
    public function get($id4, $doctrine) {
        // -- Globale Funktion für Berechnung des Kontostandes
        // Lese Kontodaten für spezifisches Konto aus DB aus
        $konto = $doctrine->getRepository(Kontenplan::class)->findBy(array("id4" => $id4))[0];

        // Hole die Kontogruppe von Konto aus DB
        $id1 = $konto->getId1();

        // Kontogruppe von Konti, bei denen Soll => Addition und Haben => Subtraktion
        $sollPlus = array(1,4,5,6);

        // Basis für Kontostand
        $kontostand = 0;

        if(in_array($id1, $sollPlus)) {
            // Soll +
            // SOLL-Buchungen aus DB auslesen
            $buchungen = $doctrine->getRepository(Hauptbuch::class)->findBy(array("soll" => $id4));
            // Kontostand anpassen
            foreach($buchungen as $buchung) {
                $kontostand = $kontostand + $buchung->getBetrag();
            }

            // HABEN-Buchungen aus DB auslesen
            $buchungen = $doctrine->getRepository(Hauptbuch::class)->findBy(array("haben" => $id4));
            // Kontostand anpassen
            foreach($buchungen as $buchung) {
                $kontostand = $kontostand - $buchung->getBetrag();
            }
        } else {
            // Haben +
            // SOLL-Buchungen aus DB auslesen
            $buchungen = $doctrine->getRepository(Hauptbuch::class)->findBy(array("soll" => $id4));
            // Kontostand anpassen
            foreach($buchungen as $buchung) {
                $kontostand = $kontostand - $buchung->getBetrag();
            }

            // HABEN-Buchungen aus DB auslesen
            $buchungen = $doctrine->getRepository(Hauptbuch::class)->findBy(array("haben" => $id4));
            // Kontostand anpassen
            foreach($buchungen as $buchung) {
                $kontostand = $kontostand + $buchung->getBetrag();
            }
        }

        // Kontostand /100 teilen, da er in DB als Integer gespeichert ist -> keine Kommastellen
        $kontostand = $kontostand/100;

        // Kontostand weiterleiten
        return $kontostand;
    }
}