<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\Kontostand;

class BilanzController extends AbstractController
{
    /**
     * @Route("/bilanz", name="bilanz")
     */
    public function index(Kontostand $kontostand)
    {
        return $this->render('bilanz/index.html.twig', [
            'test' => $kontostand->get(2400,$this->getDoctrine()),
        ]);
    }
}
