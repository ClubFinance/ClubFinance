<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Kontenplan;
use App\Entity\Hauptbuch;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class KontenplanController extends AbstractController
{
    /**
     * @Route("/kontenplan", name="kontenplan")
     */
    public function index()
    {
        $konti = $this->getDoctrine()
                        ->getRepository(Kontenplan::class)->createQueryBuilder('p')
                        ->where('p.id4 IS NOT NULL')
                        ->orderBy('p.id1', 'ASC')->addOrderBy('p.id2', 'ASC')->addOrderBy('p.id3', 'ASC')->addOrderBy('p.id4', 'ASC')
                        ->getQuery()->getResult();

        return $this->render('kontenplan/index.html.twig', [
            'konti' => $konti,
        ]);
    }

    /**
     * @Route("/kontenplan/edit/{id}", name="kontenplan_edit")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id) {
        $konto = $this->getDoctrine()->getRepository(Kontenplan::class)->find($id);

        $buchungen = $this->getDoctrine()
                            ->getRepository(Hauptbuch::class)->createQueryBuilder('p')
                            ->where('p.soll = '.$konto->getId4())->orWhere('p.haben = '.$konto->getId4())
                            ->getQuery()->getResult();

        if(count($buchungen) != 0) {
            $statusMsg = ' (Ein benutztes Konto kann nicht deaktiviert werden.)';
            $statusDisabled = true;
        } else {
            $statusMsg = '';
            $statusDisabled = false;
        }
        
        $form = $this->createFormBuilder($konto)
            ->add('id4', NumberType::class, [
                'label' => 'Nummer',
                'attr' => ['class' => 'form-control mb-3'],
                'disabled' => true,
            ])
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('status', CheckboxType::class, [
                'label' => 'Status'.$statusMsg,
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
                'disabled' => $statusDisabled,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Speichern',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $this->addFlash('success', 'Das Konto wurde erfolgreich gespeichert.');
        }

        return $this->render('kontenplan/edit.html.twig', [
            'form' => $form->createView(),
            'konto' => $konto,
        ]);
    }
}
