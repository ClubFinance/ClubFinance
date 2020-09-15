<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Hauptbuch;
use App\Entity\Kontenplan;

class Kontostand {
    public function get($id4, $doctrine) {
        $konto = $doctrine->getRepository(Kontenplan::class)->findBy(array("id4" => $id4))[0];
        $id1 = $konto->getId1();

        // Konti, bei denen Soll = Addition und Haben = Subtraktion
        $sollPlus = array(1,4,5,6);

        $kontostand = 0;

        if(in_array($id1, $sollPlus)) {
            // Soll +
            $buchungen = $doctrine->getRepository(Hauptbuch::class)->findBy(array("soll" => $id4));
            foreach($buchungen as $buchung) {
                $kontostand = $kontostand + $buchung->getBetrag();
            }

            $buchungen = $doctrine->getRepository(Hauptbuch::class)->findBy(array("haben" => $id4));
            foreach($buchungen as $buchung) {
                $kontostand = $kontostand - $buchung->getBetrag();
            }
        } else {
            // Haben +
            $buchungen = $doctrine->getRepository(Hauptbuch::class)->findBy(array("soll" => $id4));
            foreach($buchungen as $buchung) {
                $kontostand = $kontostand - $buchung->getBetrag();
            }

            $buchungen = $doctrine->getRepository(Hauptbuch::class)->findBy(array("haben" => $id4));
            foreach($buchungen as $buchung) {
                $kontostand = $kontostand + $buchung->getBetrag();
            }
        }

        $kontostand = $kontostand/100;

        return $kontostand;
    }
}