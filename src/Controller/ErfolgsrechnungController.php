<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Plugins;

use App\Entity\Kontenplan;
use App\Entity\Hauptbuch;

use App\Service\Kontostand;

// Include Dompdf required namespaces
use Dompdf\Dompdf;
use Dompdf\Options;

class ErfolgsrechnungController extends AbstractController
{
    /**
     * @Route("/erfolgsrechnung", name="erfolgsrechnung")
     */
    public function index(Plugins $plugins, Kontostand $kontostand) {
        // -- ER anzeigen
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

        if($et['sum'] > $aw['sum']) {
            // Gewinn
            $abschluss = 'Gewinn';
            $abschluss_color = 'success';
        } else {
            // Verlust
            $abschluss = 'Verlust';
            $abschluss_color = 'danger';
        }

        // Gewinn/Verlust berechnen und bei Ertrag einfügen
        $sum = $aw['sum'];
        unset($aw['sum']);
        $differenz = $et['sum'] - $sum;
        $aw[$abschluss] = $differenz;
        $aw['sum'] = $sum + $differenz;

        // Letzter Buchungssatz (für Stichdatum Erfolgsrechnung)
        $stichtag = $this->getDoctrine()->getRepository(Hauptbuch::class)->findBy(array(), array('datum' => 'DESC'))[0];

        // Seite ausgeben
        return $this->render('erfolgsrechnung/index.html.twig', [
            'plugins' => $plugins->get(),
            'aufwand' => $aw,
            'ertrag' => $et,
            'abschluss' => $abschluss,
            'abschlussColor' => $abschluss_color,
            'stichtag' => $stichtag,
        ]);
    }

    /**
     * @Route("/erfolgsrechnung/export/pdf", name="erfolgsrechnung_export_pdf")
     */
    public function export_pdf(Kontostand $kontostand) {
        // -- Erfolgsrechnung als PDF exportieren
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

        if($et['sum'] > $aw['sum']) {
            // Gewinn
            $abschluss = 'Gewinn';
            $abschluss_color = 'success';
        } else {
            // Verlust
            $abschluss = 'Verlust';
            $abschluss_color = 'danger';
        }

        // Gewinn/Verlust berechnen und bei Ertrag einfügen
        $sum = $aw['sum'];
        unset($aw['sum']);
        $differenz = $et['sum'] - $sum;
        $aw[$abschluss] = $differenz;
        $aw['sum'] = $sum + $differenz;

        // Letzter Buchungssatz (für Stichdatum Erfolgsrechnung)
        $stichtag = $this->getDoctrine()->getRepository(Hauptbuch::class)->findBy(array(), array('datum' => 'DESC'))[0];

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', TRUE);
        
        $dompdf = new Dompdf($pdfOptions);

        // PDF Design
        $html = $this->renderView('erfolgsrechnung/pdf.html.twig', [
            'aufwand' => $aw,
            'ertrag' => $et,
            'abschluss' => $abschluss,
            'abschlussColor' => $abschluss_color,
            'stichtag' => $stichtag,
        ]);
        
        $dompdf->loadHtml($html);
        
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        // PDF ausgeben
        $dompdf->stream(date("Y-m-d").'-Erfolgsrechnung.pdf', [
            "Attachment" => false
        ]);
    }
}
