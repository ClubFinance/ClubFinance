<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HauptbuchController extends AbstractController
{
    /**
     * @Route("/hauptbuch", name="hauptbuch")
     */
    public function index()
    {
        return $this->render('hauptbuch/index.html.twig', [
            'controller_name' => 'HauptbuchController',
        ]);
    }
}
