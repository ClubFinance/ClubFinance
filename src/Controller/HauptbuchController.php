<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Hauptbuch;
use App\Entity\Kontenplan;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class HauptbuchController extends AbstractController
{
    /**
     * @Route("/hauptbuch", name="hauptbuch")
     */
    public function index()
    {
        $buchungssaetze = $this->getDoctrine()->getRepository(Hauptbuch::class)->findBy(array(), array('datum' => 'DESC'));

        return $this->render('hauptbuch/index.html.twig', [
            'hauptbuch' => $buchungssaetze,
        ]);
    }

    /**
     * @Route("/hauptbuch/new", name="hauptbuch_new")
     * Method({"GET", "POST"})
     */
    public function new(Request $request) {
        $hauptbuch = new Hauptbuch();
        
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

        if($form->isSubmitted() &&  $form->isValid()) {
            $data = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($data);
            $entityManager->flush();

            $this->addFlash('success', 'Der Buchungssatz wurde erfasst.');
        }

        return $this->render('hauptbuch/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/hauptbuch/edit/{id}", name="hauptbuch_edit")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id) {
        $hauptbuch = $this->getDoctrine()->getRepository(Hauptbuch::class)->find($id);
        
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

        if($form->isSubmitted() &&  $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $this->addFlash('success', 'Der Buchungssatz wurde erfolgreich gespeichert.');
        }

        return $this->render('hauptbuch/edit.html.twig', [
            'form' => $form->createView(),
            'satz' => $hauptbuch,
        ]);
    }

    /**
     * @Route("hauptbuch/delete/{id}", name="hauptbuch_delete")
     */
    public function delete($id) {
        $user = $this->getDoctrine()->getRepository(Hauptbuch::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('hauptbuch');
    }

    /**
     * @Route("hauptbuch/konti/json", name="hauptbuch_konti")
     */
    public function konti_json(Request $request) {
        $term = $request->query->get('term');
        $konti = $this->getDoctrine()->getRepository(Kontenplan::class)->createQueryBuilder('p')->where('p.id4 LIKE :word')->orWhere('p.name LIKE :word')->setParameter('word', '%'.$term.'%')->getQuery()->getResult();

        return $this->render('hauptbuch/json.html.twig', [
            'konti' => $konti,
        ]);
    }
}
