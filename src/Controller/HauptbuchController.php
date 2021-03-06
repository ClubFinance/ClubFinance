<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Plugins;

use App\Entity\Hauptbuch;
use App\Entity\Kontenplan;
use App\Entity\Buchungsvorlage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

// Include Dompdf required namespaces
use Dompdf\Dompdf;
use Dompdf\Options;

// Include PhpSpreadsheet required namespaces
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class HauptbuchController extends AbstractController
{
    /**
     * @Route("/hauptbuch", name="hauptbuch")
     */
    public function index(Plugins $plugins) {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // -- Hauptbuch anzeigen
        // Buchungssätze aus DB
        $buchungssaetze = $this->getDoctrine()->getRepository(Hauptbuch::class)->findBy(array(), array('datum' => 'DESC'));

        // Buchungsvorlagen aus DB
        $vorlagen = $this->getDoctrine()->getRepository(Buchungsvorlage::class)->findBy(array('status' => true), array('name' => 'ASC'));

        // Seite ausgeben
        return $this->render('hauptbuch/index.html.twig', [
            'plugins' => $plugins->get(),
            'hauptbuch' => $buchungssaetze,
            'vorlagen' => $vorlagen,
        ]);
    }

    /**
     * @Route("/hauptbuch/export/pdf/{orderby}/{order}", name="hauptbuch_export_pdf")
     */
    public function export_pdf($orderby, $order) {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // -- Hauptbuch als PDF exportieren
        // Buchungssätze aus DB
        $buchungssaetze = $this->getDoctrine()->getRepository(Hauptbuch::class)->findBy(array(), array($orderby => $order));

        // Kontonamen aus DB laden (werden in Hauptbuch nur Kontonummern gespeichert)
        foreach($buchungssaetze as $satz) {
            $sql = $this->getDoctrine()->getRepository(Kontenplan::class)->findBy(array("id4" => $satz->getSoll()))[0];
            $satz->setSollT($satz->getSoll().' - '.$sql->getName());

            $sql = $this->getDoctrine()->getRepository(Kontenplan::class)->findBy(array("id4" => $satz->getHaben()))[0];
            $satz->setHabenT($satz->getHaben().' - '.$sql->getName());
        }

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', TRUE);
        
        $dompdf = new Dompdf($pdfOptions);

        // PDF Design
        $html = $this->renderView('hauptbuch/pdf.html.twig', [
            'hauptbuch' => $buchungssaetze,
        ]);
        
        $dompdf->loadHtml($html);
        
        $dompdf->setPaper('A4', 'landscape');

        $dompdf->render();

        // Ausgabe PDF
        $dompdf->stream(date("Y-m-d").'-Hauptbuch.pdf', [
            "Attachment" => false
        ]);
    }

    /**
     * @Route("/hauptbuch/export/xlsx", name="hauptbuch_export_xlsx")
     */
    public function export_xlsx() {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // -- Hauptbuch als XLSX (Excel) exportieren
        // Buchungssätze aus DB
        $buchungssaetze = $this->getDoctrine()->getRepository(Hauptbuch::class)->findBy(array(), array('datum' => 'DESC'));

        // Kontonamen aus DB laden (werden in Hauptbuch nur Kontonummern gespeichert)
        foreach($buchungssaetze as $satz) {
            $sql = $this->getDoctrine()->getRepository(Kontenplan::class)->findBy(array("id4" => $satz->getSoll()))[0];
            $satz->setSollT($satz->getSoll().' - '.$sql->getName());

            $sql = $this->getDoctrine()->getRepository(Kontenplan::class)->findBy(array("id4" => $satz->getHaben()))[0];
            $satz->setHabenT($satz->getHaben().' - '.$sql->getName());
        }

        // Definiere Headline in Excel-File
        $header = array("ID", "Datum", "Soll", "Soll-Kontoname", "Haben", "Haben-Kontoname", "Beschreibung", "Betrag", "Beleg");
        $data = array($header);

        foreach($buchungssaetze as $satz) {
            // definiere Spalten
            $array = array(
                $satz->getId(),
                $satz->getDatum(),
                explode(" - ",$satz->getSollT())[0],
                explode(" - ",$satz->getSollT())[1],
                explode(" - ",$satz->getHabenT())[0],
                explode(" - ",$satz->getHabenT())[1],
                $satz->getBeschreibung(),
                $satz->getBetrag()/100,
                $satz->getBeleg()
            );
            $data[] = $array;
        }

        $spreadsheet = new Spreadsheet();
        
        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data, null, 'A1');
        $sheet->setTitle("Hauptbuch");
        
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);
        
        // Create a Temporary file in the system
        $fileName = date("Y-m-d").'-Hauptbuch.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
        
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/hauptbuch/new", name="hauptbuch_new")
     * Method({"GET", "POST"})
     */
    public function new(Plugins $plugins, Request $request) {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // -- Neuer Buchungssatz
        $hauptbuch = new Hauptbuch();
        
        // Formular
        $form = $this->createFormBuilder($hauptbuch)
            ->add('datum', DateType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'data' => new \DateTime()
            ])
            ->add('soll', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3 autocomplete']
            ])
            ->add('haben', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3 autocomplete']
            ])
            ->add('betrag', MoneyType::class, [
                'label' => 'Betrag (CHF)',
                'divisor' => 100,
                'currency' => false,
                'attr' => ['class' => 'form-control mb-3']
            ])
            ->add('beschreibung', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('beleg', TextType::class, [
                'label' => 'Beleg(e)',
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Speichern',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ])
            ->getForm();

        $form->handleRequest($request);

        // Submit
        if($form->isSubmitted() &&  $form->isValid()) {
            $data = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($data);
            $entityManager->flush();

            // Bestätigung
            $this->addFlash('success', 'Der Buchungssatz wurde erfasst.');
        }

        // Seite laden
        return $this->render('hauptbuch/new.html.twig', [
            'plugins' => $plugins->get(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/hauptbuch/new/vorlage/{soll}/{haben}/{beschreibung}", name="hauptbuch_new_vorlage")
     * Method({"GET", "POST"})
     */
    public function newVorlage(Plugins $plugins, Request $request, $soll, $haben, $beschreibung) {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // -- Neuer Buchungssatz aus Buchungsvorlage
        $hauptbuch = new Hauptbuch();

        // Definition leere Beschreibung
        if($beschreibung == 'null') {
            $beschreibung = '';
        }
        
        // Formular
        $form = $this->createFormBuilder($hauptbuch)
            ->add('datum', DateType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'data' => new \DateTime()
            ])
            ->add('soll', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3 autocomplete'],
                'data' => $soll,
            ])
            ->add('haben', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3 autocomplete'],
                'data' => $haben,
            ])
            ->add('betrag', MoneyType::class, [
                'label' => 'Betrag (CHF)',
                'divisor' => 100,
                'currency' => false,
                'attr' => ['class' => 'form-control mb-3']
            ])
            ->add('beschreibung', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
                'data' => $beschreibung,
            ])
            ->add('beleg', TextType::class, [
                'label' => 'Beleg(e)',
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Speichern',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ])
            ->getForm();

        $form->handleRequest($request);

        // Submit
        if($form->isSubmitted() &&  $form->isValid()) {
            $data = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($data);
            $entityManager->flush();

            // Bestätigung
            $this->addFlash('success', 'Der Buchungssatz wurde erfasst.');
        }

        // Seite ausgeben
        return $this->render('hauptbuch/new.html.twig', [
            'plugins' => $plugins->get(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/hauptbuch/edit/{id}", name="hauptbuch_edit")
     * Method({"GET", "POST"})
     */
    public function edit(Plugins $plugins, Request $request, $id) {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // -- Buchungssatz bearbeiten
        // aus DB laden
        $hauptbuch = $this->getDoctrine()->getRepository(Hauptbuch::class)->find($id);
        
        // Formular
        $form = $this->createFormBuilder($hauptbuch)
            ->add('datum', DateType::class, [
                'attr' => ['class' => 'form-control mb-3']
            ])
            ->add('soll', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3 autocomplete', 'onClick' => 'this.setSelectionRange(0, this.value.length)']
            ])
            ->add('haben', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3 autocomplete', 'onClick' => 'this.setSelectionRange(0, this.value.length)']
            ])
            ->add('betrag', MoneyType::class, [
                'label' => 'Betrag (CHF)',
                'divisor' => 100,
                'currency' => false,
                'attr' => ['class' => 'form-control mb-3']
            ])
            ->add('beschreibung', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('beleg', TextType::class, [
                'label' => 'Beleg(e)',
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Speichern',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ])
            ->getForm();

        $form->handleRequest($request);

        // Submit
        if($form->isSubmitted() &&  $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            // Bestätigung
            $this->addFlash('success', 'Der Buchungssatz wurde erfolgreich gespeichert.');
        }

        // Seite laden
        return $this->render('hauptbuch/edit.html.twig', [
            'plugins' => $plugins->get(),
            'form' => $form->createView(),
            'satz' => $hauptbuch,
        ]);
    }

    /**
     * @Route("hauptbuch/delete/{id}", name="hauptbuch_delete")
     */
    public function delete(Plugins $plugins, $id) {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // Buchungssatz löschen
        $buchungssatz = $this->getDoctrine()->getRepository(Hauptbuch::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($buchungssatz);
        $entityManager->flush();

        // Weiterleitung
        return $this->redirectToRoute('hauptbuch');
    }

    /**
     * @Route("hauptbuch/konti/json", name="hauptbuch_konti")
     */
    public function konti_json(Request $request) {
        // -- Konti als JSON ausgeben für asynchrone Abfragen (AJAX)
        // Definition Suchterm
        $term = $request->query->get('term');

        // Suche nach Term in DB
        $konti = $this->getDoctrine()->getRepository(Kontenplan::class)->createQueryBuilder('p')->where('p.id4 LIKE :word')->orWhere('p.name LIKE :word')->setParameter('word', '%'.$term.'%')->getQuery()->getResult();

        // Ausgabe JSON
        return $this->render('hauptbuch/json.html.twig', [
            'konti' => $konti,
        ]);
    }
}
