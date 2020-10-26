<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Plugins;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(Plugins $plugins)
    {
        return $this->render('home/index.html.twig', [
            'plugins' => $plugins->get(),
        ]);
    }

    /**
     * @Route("/", name="home_forward")
     */
    public function index_forward() {
        return $this->redirectToRoute('home');
    }
}