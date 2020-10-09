<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Kontenplan;
use App\Entity\Hauptbuch;

use App\Service\Kontostand;

class ErfolgsrechnungController extends AbstractController
{
    /**
     * @Route("/erfolgsrechnung", name="erfolgsrechnung")
     */
    public function index(Kontostand $kontostand) {
        $aufwand = $this->getDoctrine()
                        ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                        ->where('p.id1 = 4')->orWhere('p.id1 = 5')->orWhere('p.id1 = 6')
                        ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                        ->getQuery()->getResult();

                        $aw = array();

                        foreach($aufwand as $i) {
                            $aw[$i->getName()] = $kontostand->get($i->getId4(),$this->getDoctrine());
                        }

                        $aw['sum'] = array_sum($aw);

        $ertrag = $this->getDoctrine()
                        ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                        ->where('p.id1 = 3')->orWhere('p.id1 = 7')
                        ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                        ->getQuery()->getResult();

                        $et = array();

                        foreach($ertrag as $i) {
                            $et[$i->getName()] = $kontostand->get($i->getId4(),$this->getDoctrine());
                        }

                        $et['sum'] = array_sum($et);

        // Letzter Buchungssatz (für Stichdatum Bilanz)
        $stichtag = $this->getDoctrine()->getRepository(Hauptbuch::class)->findBy(array(), array('datum' => 'DESC'))[0];


        return $this->render('erfolgsrechnung/index.html.twig', [
            'stichtag' => $stichtag,
            'aufwand' => $aw,
            'ertrag' => $et,
        ]);
    }
}
