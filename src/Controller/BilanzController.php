<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Kontenplan;

use App\Service\Kontostand;

class BilanzController extends AbstractController
{
    /**
     * @Route("/bilanz", name="bilanz")
     */
    public function index(Kontostand $kontostand) {
        $umlaufvermoegen = $this->getDoctrine()
                                ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                                ->where('p.id2 = 10')->andWhere('p.id4 != 0')
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
                                ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                                ->getQuery()->getResult();

                                $ek = array();

                                foreach($eigenkapital as $i) {
                                    $ek[$i->getName()] = $kontostand->get($i->getId4(),$this->getDoctrine());
                                }

                                $ek['sum'] = array_sum($ek);

        $aktiven = $uv['sum'] + $av['sum'];
        $passiven = $fk['sum'] + $ek['sum'];

        if($aktiven > $passiven) {
            // Gewinn
            $abschluss = 'Gewinn';
            $abschluss_color = 'success';
        } else {
            // Verlust
            $abschluss = 'Verlust';
            $abschluss_color = 'danger';
        }

        $sum = $ek['sum'];
        unset($ek['sum']);
        $differenz = $aktiven - $passiven;
        $ek[$abschluss] = $differenz;
        $ek['sum'] = $sum + $differenz;
        $passiven = $fk['sum'] + $ek['sum'];


        return $this->render('bilanz/index.html.twig', [
            'umlaufvermoegen' => $uv,
            'anlagevermoegen' => $av,
            'fremdkapital' => $fk,
            'eigenkapital' => $ek,
            'aktiven' => $aktiven,
            'passiven' => $passiven,
            'abschluss' => $abschluss,
            'abschlussColor' => $abschluss_color,
        ]);
    }
}
