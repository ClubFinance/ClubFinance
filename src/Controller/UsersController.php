<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

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
    public function index()
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findBy(array(), array('nachname' => 'ASC'));

        return $this->render('users/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/users/new", name="users_new")
     * Method({"GET", "POST"})
     */
    public function new(Request $request) {
        $user = new User();
        
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
                'attr' => ['class' => 'form-control mb-3']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Speichern',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() &&  $form->isValid()) {
            $data = $form->getData();

            $data->setRoles(array("ROLE_USER"));
            $data->setPassword($this->passwordEncoder->encodePassword(
                $data,$data->getPassword()
            ));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($data);
            $entityManager->flush();

            // return $this->redirectToRoute('users');
            $this->addFlash('success', 'Der Nutzer wurde erfolgreich angelegt.');
        }

        return $this->render('users/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/users/edit/{id}", name="users_edit")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id) {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        
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

        if($form->isSubmitted() &&  $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            // return $this->redirectToRoute('users');
            $this->addFlash('success', 'Das Profil wurde erfolgreich gespeichert.');
        }

        return $this->render('users/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/users/setpw/{id}", name="users_setpw")
     * Method({"GET", "POST"})
     */
    public function setpw(Request $request, $id) {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        
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

        if($form->isSubmitted() &&  $form->isValid()) {
            $data = $form->getData();
            $data->setPassword($this->passwordEncoder->encodePassword(
                $data,$data->getPassword()
            ));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            // return $this->redirectToRoute('users');
            $this->addFlash('success', 'Das Passwort wurde erfolgreich gesetzt.');
        }

        return $this->render('users/setpw.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("users/delete/{id}", name="users_delete")
     */
    public function delete($id) {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('users');
    }
}
