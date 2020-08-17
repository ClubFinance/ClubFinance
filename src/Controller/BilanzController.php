<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BilanzController extends AbstractController
{
    /**
     * @Route("/bilanz", name="bilanz")
     */
    public function index()
    {
        return $this->render('bilanz/index.html.twig', [
            'controller_name' => 'BilanzController',
        ]);
    }
}
