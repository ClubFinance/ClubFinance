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

class UsersController extends AbstractController
{

    private $rollen = [
        'Normaler Benutzer (standard)' => 'ROLE_USER',
        'Administrator' => 'ROLE_ADMIN',
    ];

    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder) {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/users", name="users")
     */
    public function index(Plugins $plugins) {
        // -- Nutzer aus DB laden
        $users = $this->getDoctrine()->getRepository(User::class)->findBy(array(), array('nachname' => 'ASC'));

        // Seite laden
        return $this->render('users/index.html.twig', [
            'plugins' => $plugins->get(),
            'users' => $users,
        ]);
    }

    /**
     * @Route("/users/new", name="users_new")
     * Method({"GET", "POST"})
     */
    public function new(Plugins $plugins, Request $request) {
        // -- Neuen Nutzer erstellen
        $user = new User();
        
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
            ->add('password', PasswordType::class, [
                'attr' => ['class' => 'form-control mb-3'],
                'label' => "Passwort"
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

            // Rolle (Standardbenutzer) eintragen
            $data->setRoles(array("ROLE_USER"));

            // Passwort verschlüsseln
            $data->setPassword($this->passwordEncoder->encodePassword(
                $data,$data->getPassword()
            ));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($data);
            $entityManager->flush();

            // Bestätigung
            $this->addFlash('success', 'Der Nutzer wurde erfolgreich angelegt.');
        }

        // Seite ausgeben
        return $this->render('users/new.html.twig', [
            'plugins' => $plugins->get(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/users/edit/{id}", name="users_edit")
     * Method({"GET", "POST"})
     */
    public function edit(Plugins $plugins, Request $request, $id) {
        // -- Nutzer bearbeiten
        // aus DB laden
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
            $this->addFlash('success', 'Das Profil wurde erfolgreich gespeichert.');
        }

        // Seite ausgeben
        return $this->render('users/edit.html.twig', [
            'plugins' => $plugins->get(),
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/users/setpw/{id}", name="users_setpw")
     * Method({"GET", "POST"})
     */
    public function setpw(Plugins $plugins, Request $request, $id) {
        // -- Passwort für Nutzer setzen
        // aus DB laden
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

        $form->handleRequest($request);

        // Submit
        if($form->isSubmitted() &&  $form->isValid()) {
            $data = $form->getData();

            // Passwort verschlüsseln
            $data->setPassword($this->passwordEncoder->encodePassword(
                $data,$data->getPassword()
            ));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            // Bestätigung
            $this->addFlash('success', 'Das Passwort wurde erfolgreich gesetzt.');
        }

        // Seite ausgeben
        return $this->render('users/setpw.html.twig', [
            'plugins' => $plugins->get(),
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("users/delete/{id}", name="users_delete")
     */
    public function delete($id) {
        // Benutzer löschen
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        // Weiterleitung
        return $this->redirectToRoute('users');
    }
}
