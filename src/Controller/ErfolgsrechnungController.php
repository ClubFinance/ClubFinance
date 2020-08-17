<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ErfolgsrechnungController extends AbstractController
{
    /**
     * @Route("/erfolgsrechnung", name="erfolgsrechnung")
     */
    public function index()
    {
        return $this->render('erfolgsrechnung/index.html.twig', [
            'controller_name' => 'ErfolgsrechnungController',
        ]);
    }
}
