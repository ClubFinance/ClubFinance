<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Plugins;

use App\Entity\Buchungsvorlage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class BuchungsvorlageController extends AbstractController
{
    /**
     * @Route("/buchungsvorlage", name="buchungsvorlage")
     */
    public function index(Plugins $plugins) {
        $vorlagen = $this->getDoctrine()->getRepository(Buchungsvorlage::class)->findAll();

        return $this->render('buchungsvorlage/index.html.twig', [
            'plugins' => $plugins->get(),
            'vorlagen' => $vorlagen,
        ]);
    }

    /**
     * @Route("/buchungsvorlage/new", name="buchungsvorlage_new")
     * Method({"GET", "POST"})
     */
    public function new(Plugins $plugins, Request $request) {
        $vorlage = new Buchungsvorlage();
        
        $form = $this->createFormBuilder($vorlage)
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control mb-3']
            ])
            ->add('beschreibung', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('soll', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3 autocomplete', 'onClick' => 'this.setSelectionRange(0, this.value.length)']
            ])
            ->add('haben', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3 autocomplete', 'onClick' => 'this.setSelectionRange(0, this.value.length)']
            ])
            ->add('status', CheckboxType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'data' => true,
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

            $this->addFlash('success', 'Die Buchungsvorlage wurde erfolgreich gespeichert.');
        }

        return $this->render('buchungsvorlage/new.html.twig', [
            'plugins' => $plugins->get(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/buchungsvorlage/edit/{id}", name="buchungsvorlage_edit")
     * Method({"GET", "POST"})
     */
    public function edit(Plugins $plugins, Request $request, $id) {
        $vorlage = $this->getDoctrine()->getRepository(Buchungsvorlage::class)->find($id);
        
        $form = $this->createFormBuilder($vorlage)
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control mb-3']
            ])
            ->add('beschreibung', TextareaType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
            ])
            ->add('soll', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3 autocomplete', 'onClick' => 'this.setSelectionRange(0, this.value.length)']
            ])
            ->add('haben', NumberType::class, [
                'attr' => ['class' => 'form-control mb-3 autocomplete', 'onClick' => 'this.setSelectionRange(0, this.value.length)']
            ])
            ->add('status', CheckboxType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'required' => false,
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

            $this->addFlash('success', 'Die Buchungsvorlage wurde erfolgreich gespeichert.');
        }

        return $this->render('buchungsvorlage/edit.html.twig', [
            'plugins' => $plugins->get(),
            'vorlage' => $vorlage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("buchungsvorlage/delete/{id}", name="buchungsvorlage_delete")
     */
    public function delete(Plugins $plugins, $id) {
        $vorlage = $this->getDoctrine()->getRepository(Buchungsvorlage::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($vorlage);
        $entityManager->flush();

        return $this->redirectToRoute('buchungsvorlage');
    }
}