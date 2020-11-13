<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Plugins;

use App\Entity\Kontenplan;
use App\Entity\Hauptbuch;

use App\Service\Kontostand;

class KontoauszugController extends AbstractController
{
    /**
     * @Route("/kontoauszug", name="kontoauszug")
     */
    public function index(Plugins $plugins) {
        $konti = $this->getDoctrine()
                        ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                        ->where('p.id4 IS NOT null')
                        ->andWhere('p.status != 0')
                        ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                        ->getQuery()->getResult();


        return $this->render('kontoauszug/index.html.twig', [
            'plugins' => $plugins->get(),
            'konto' => false,
            'konti' => $konti,
            'buchungen' => false,
        ]);
    }

    /**
     * @Route("/kontoauszug/show/{id4}", name="kontoauszug_show")
     */
    public function show(Plugins $plugins, $id4) {
        $konti = $this->getDoctrine()
                        ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                        ->where('p.id4 IS NOT null')
                        ->andWhere('p.status != 0')
                        ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                        ->getQuery()->getResult();

        $konto = $this->getDoctrine()->getRepository(Kontenplan::class)->findBy(array('id4' => $id4))[0];

        $buchungen = $this->getDoctrine()
                        ->getRepository(Hauptbuch::class)->createQueryBuilder('p')
                        ->where('p.soll = '.$id4)->orWhere('p.haben = '.$id4)
                        ->orderBy('p.datum', 'ASC')
                        ->getQuery()->getResult();

        return $this->render('kontoauszug/index.html.twig', [
            'plugins' => $plugins->get(),
            'konto' => $konto,
            'konti' => $konti,
            'buchungen' => $buchungen,
        ]);
    }
}
