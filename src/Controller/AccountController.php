<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Plugins;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountController extends AbstractController {
    private $rollen = [
        'Normaler Benutzer (standard)' => 'ROLE_USER',
        'Administrator' => 'ROLE_ADMIN',
    ];

    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder) {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/account/profil", name="account")
     */
    public function index(Plugins $plugins, Request $request) {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // -- Formular für Bearbeitung des eigenen Profils
        // User aus DB laden
        $id = $this->getUser();
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        
        // Formular
        $form = $this->createFormBuilder($user)
            ->add('vorname', TextType::class, [
                'attr' => ['class' => 'form-control mb-3']
            ])
            ->add('nachname', TextType::class, [
                'attr' => ['class' => 'form-control mb-3']
            ])
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control mb-3']
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rolle',
                'attr' => ['class' => 'form-control mb-3'],
                'choices' => $this->rollen,
                'multiple' => true,
                'disabled' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Speichern',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ])
            ->getForm();

        // Submit
        $form->handleRequest($request);

        if($form->isSubmitted() &&  $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            // Bestätigung
            $this->addFlash('success', 'Das Profil wurde erfolgreich gespeichert.');
        }

        // Seite laden
        return $this->render('account/index.html.twig', [
            'plugins' => $plugins->get(),
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/account/passwort", name="account_passwort")
     */
    public function pw(Plugins $plugins, Request $request) {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // -- Formular für Passwortänderung
        // User aus DB laden
        $id = $this->getUser();
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        
        // Formular
        $form = $this->createFormBuilder($user)
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'disabled' => true,
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Neues Passwort',
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Speichern',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ])
            ->getForm();

        // Submit
        $form->handleRequest($request);

        if($form->isSubmitted() &&  $form->isValid()) {
            $data = $form->getData();
            $data->setPassword($this->passwordEncoder->encodePassword(
                $data,$data->getPassword()
            ));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            // Bestätigung
            $this->addFlash('success', 'Das Passwort wurde erfolgreich geändert.');
        }

        // Seite laden
        return $this->render('account/pw.html.twig', [
            'plugins' => $plugins->get(),
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
