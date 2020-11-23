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
        // Kapitalverteilung
        $umlaufvermoegen = $this->getDoctrine()
                                ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                                ->where('p.id2 = 10')->andWhere('p.id4 != 0')
                                ->andWhere('p.status != 0')
                                ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                                ->getQuery()->getResult();

                                $uv = array();

                                foreach($umlaufvermoegen as $i) {
                                    $uv[$i->getName()] = $kontostand->get($i->getId4(),$this->getDoctrine());
                                }

                                $uv['sum'] = array_sum($uv);

        $anlagevermoegen = $this->getDoctrine()
                                ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                                ->where('p.id2 = 14')->andWhere('p.id4 != 0')
                                ->andWhere('p.status != 0')
                                ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                                ->getQuery()->getResult();

                                $av = array();

                                foreach($anlagevermoegen as $i) {
                                    $av[$i->getName()] = $kontostand->get($i->getId4(),$this->getDoctrine());
                                }

                                $av['sum'] = array_sum($av);

        $fremdkapital = $this->getDoctrine()
                                ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                                ->where('p.id2 = 20')->orWhere('p.id2 = 24')->andWhere('p.id4 != 0')
                                ->andWhere('p.status != 0')
                                ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                                ->getQuery()->getResult();

                                $fk = array();

                                foreach($fremdkapital as $i) {
                                    $fk[$i->getName()] = $kontostand->get($i->getId4(),$this->getDoctrine());
                                }

                                $fk['sum'] = array_sum($fk);

        $eigenkapital = $this->getDoctrine()
                                ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                                ->where('p.id2 = 28')->andWhere('p.id4 != 0')
                                ->andWhere('p.status != 0')
                                ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                                ->getQuery()->getResult();

                                $ek = array();

                                foreach($eigenkapital as $i) {
                                    $ek[$i->getName()] = $kontostand->get($i->getId4(),$this->getDoctrine());
                                }

                                $ek['sum'] = array_sum($ek);

        // Erfolgsrechnung
        $aufwand = $this->getDoctrine()
                        ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                        ->where('p.id1 = 4')->orWhere('p.id1 = 5')->orWhere('p.id1 = 6')
                        ->andWhere('p.status != 0')
                        ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                        ->getQuery()->getResult();

                        $aw = array();

                        foreach($aufwand as $i) {
                            $aw[$i->getName()] = $kontostand->get($i->getId4(),$this->getDoctrine());
                        }

                        $aw['sum'] = array_sum($aw);

        $ertrag = $this->getDoctrine()
                        ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                        ->where('p.id1 = 3')->orWhere('p.id1 = 7')->orWhere('p.id1 = 8')
                        ->andWhere('p.status != 0')
                        ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                        ->getQuery()->getResult();

                        $et = array();

                        foreach($ertrag as $i) {
                            $et[$i->getName()] = $kontostand->get($i->getId4(),$this->getDoctrine());
                        }

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
        return $this->redirectToRoute('home');
    }
}