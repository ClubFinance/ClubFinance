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

// Include PhpSpreadsheet required namespaces
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
    public function show(Plugins $plugins, Kontostand $kontostand, $id4) {
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

        $saldo = $kontostand->get($id4,$this->getDoctrine());

        return $this->render('kontoauszug/index.html.twig', [
            'plugins' => $plugins->get(),
            'konto' => $konto,
            'konti' => $konti,
            'buchungen' => $buchungen,
            'saldo' => $saldo,
        ]);
    }

    /**
     * @Route("/kontoauszug/export/pdf/{id4}/{orderby}/{order}", name="kontoauszug_export_pdf")
     */
    public function export_pdf(Kontostand $kontostand, $id4, $orderby, $order) {
        $konto = $this->getDoctrine()->getRepository(Kontenplan::class)->findBy(array('id4' => $id4))[0];

        $buchungen = $this->getDoctrine()
                        ->getRepository(Hauptbuch::class)->createQueryBuilder('p')
                        ->where('p.soll = '.$id4)->orWhere('p.haben = '.$id4)
                        ->orderBy('p.'.$orderby, $order)
                        ->getQuery()->getResult();

        foreach($buchungen as $satz) {
            $sql = $this->getDoctrine()->getRepository(Kontenplan::class)->findBy(array("id4" => $satz->getSoll()))[0];
            $satz->setSollT($satz->getSoll().' - '.$sql->getName());

            $sql = $this->getDoctrine()->getRepository(Kontenplan::class)->findBy(array("id4" => $satz->getHaben()))[0];
            $satz->setHabenT($satz->getHaben().' - '.$sql->getName());
        }

        $saldo = $kontostand->get($id4,$this->getDoctrine());

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', TRUE);
        
        $dompdf = new Dompdf($pdfOptions);

        $html = $this->renderView('kontoauszug/pdf.html.twig', [
            'konto' => $konto,
            'buchungen' => $buchungen,
            'saldo' => $saldo,
        ]);
        
        $dompdf->loadHtml($html);
        
        $dompdf->setPaper('A4', 'landscape');

        $dompdf->render();

        $dompdf->stream(date("Y-m-d").'-Kontoauszug-'.$konto->getId4().'.pdf', [
            "Attachment" => false
        ]);
    }

    /**
     * @Route("/kontoauszug/export/xlsx/{id4}", name="kontoauszug_export_xlsx")
     */
    public function export_xlsx($id4) {
        $konto = $this->getDoctrine()->getRepository(Kontenplan::class)->findBy(array('id4' => $id4))[0];

        $buchungen = $this->getDoctrine()
                        ->getRepository(Hauptbuch::class)->createQueryBuilder('p')
                        ->where('p.soll = '.$id4)->orWhere('p.haben = '.$id4)
                        ->orderBy('p.datum', 'ASC')
                        ->getQuery()->getResult();

        // Definiere Headline
        $header = array("ID", "Datum", "Beschreibung", "Soll", "Haben", "Betrag", "Beleg");
        $data = array($header);

        foreach($buchungen as $satz) {
            // definiere Felder
            $array = array(
                $satz->getId(),
                $satz->getDatum(),
                $satz->getBeschreibung(),
                $satz->getSoll(),
                $satz->getHaben(),
                $satz->getBetrag()/100,
                $satz->getBeleg()
            );
            $data[] = $array;
        }

        $spreadsheet = new Spreadsheet();
        
        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data, null, 'A1');
        $sheet->setTitle("Kontoauszug ".$konto->getId4());
        
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);
        
        // Create a Temporary file in the system
        $fileName = date("Y-m-d").'-Kontoauszug-'.$konto->getId4().'.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
        
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
