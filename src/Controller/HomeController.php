<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Plugins;

use App\Entity\Kontenplan;
use App\Entity\Hauptbuch;

use App\Service\Kontostand;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(Plugins $plugins, Kontostand $kontostand) {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // -- Dashboard
        // Kapitalverteilung (Bilanz)
        $umlaufvermoegen = $this->getDoctrine()
                                ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                                ->where('p.id2 = 10')->andWhere('p.id4 != 0')
                                ->andWhere('p.status != 0')
                                ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                                ->getQuery()->getResult();

                                $uv = array();

                                // berechne Kontostände
                                foreach($umlaufvermoegen as $i) {
                                    $uv[$i->getName()] = $kontostand->get($i->getId4(),$this->getDoctrine());
                                }

                                // Summe der Konti
                                $uv['sum'] = array_sum($uv);

        $anlagevermoegen = $this->getDoctrine()
                                ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                                ->where('p.id2 = 14')->andWhere('p.id4 != 0')
                                ->andWhere('p.status != 0')
                                ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                                ->getQuery()->getResult();

                                $av = array();

                                // berechne Kontostände
                                foreach($anlagevermoegen as $i) {
                                    $av[$i->getName()] = $kontostand->get($i->getId4(),$this->getDoctrine());
                                }

                                // Summe der Konti
                                $av['sum'] = array_sum($av);

        $fremdkapital = $this->getDoctrine()
                                ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                                ->where('p.id2 = 20')->orWhere('p.id2 = 24')->andWhere('p.id4 != 0')
                                ->andWhere('p.status != 0')
                                ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                                ->getQuery()->getResult();

                                $fk = array();

                                // berechne Kontostände
                                foreach($fremdkapital as $i) {
                                    $fk[$i->getName()] = $kontostand->get($i->getId4(),$this->getDoctrine());
                                }

                                // Summe der Konti
                                $fk['sum'] = array_sum($fk);

        $eigenkapital = $this->getDoctrine()
                                ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                                ->where('p.id2 = 28')->andWhere('p.id4 != 0')
                                ->andWhere('p.status != 0')
                                ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                                ->getQuery()->getResult();

                                $ek = array();

                                // berechne Kontostände
                                foreach($eigenkapital as $i) {
                                    $ek[$i->getName()] = $kontostand->get($i->getId4(),$this->getDoctrine());
                                }

                                // Summe der Konti
                                $ek['sum'] = array_sum($ek);

        // Erfolgsrechnung
        $aufwand = $this->getDoctrine()
                        ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                        ->where('p.id1 = 4')->orWhere('p.id1 = 5')->orWhere('p.id1 = 6')
                        ->andWhere('p.status != 0')
                        ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                        ->getQuery()->getResult();

                        $aw = array();

                        // berechne Kontostände
                        foreach($aufwand as $i) {
                            $aw[$i->getName()] = $kontostand->get($i->getId4(),$this->getDoctrine());
                        }

                        // Summe der Konti
                        $aw['sum'] = array_sum($aw);

        $ertrag = $this->getDoctrine()
                        ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                        ->where('p.id1 = 3')->orWhere('p.id1 = 7')->orWhere('p.id1 = 8')
                        ->andWhere('p.status != 0')
                        ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                        ->getQuery()->getResult();

                        $et = array();

                        // berechne Kontostände
                        foreach($ertrag as $i) {
                            $et[$i->getName()] = $kontostand->get($i->getId4(),$this->getDoctrine());
                        }

                        // Summe der Konti
                        $et['sum'] = array_sum($et);

        // Gewinn/Verlust
        $aktiven = $uv['sum'] + $av['sum'];
        $passiven = $fk['sum'] + $ek['sum'];

        if($aktiven > $passiven) {
            // Gewinn
            $abschluss = 'Gewinn';
            $abschluss_color = 'success';
            $abschluss_color_hex = '#1cc88a';
            $abschluss_color_hex_hover = '#15a16d';
        } else {
            // Verlust
            $abschluss = 'Verlust';
            $abschluss_color = 'danger';
            $abschluss_color_hex = '#e74a3b';
            $abschluss_color_hex_hover = '#b5392d';
        }

        // Gewinn/Verlust berechnen
        $differenz = $aktiven - $passiven;

        // Ausgabe Seite
        return $this->render('home/index.html.twig', [
            'plugins' => $plugins->get(),
            'uv' => $uv,
            'av' => $av,
            'fk' => $fk,
            'ek' => $ek,
            'aw' => $aw,
            'et' => $et,
            'abschluss' => $abschluss,
            'abschlussNum' => $differenz,
            'abschlussColor' => $abschluss_color,
            'abschlussColorHex' => $abschluss_color_hex,
            'abschlussColorHexHover' => $abschluss_color_hex_hover,
        ]);
    }

    /**
     * @Route("/", name="home_forward")
     */
    public function index_forward() {
        // Weiterleitung auf Dashboard wenn keine Seite definiert
        return $this->redirectToRoute('home');
    }
}