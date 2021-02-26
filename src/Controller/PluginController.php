<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Plugins;

use App\Entity\Kontenplan;
use App\Entity\Hauptbuch;

class PluginController extends AbstractController
{
    /**
     * @Route("/plugin/demo", name="plugin_demo")
     */
    public function index(Plugins $plugins) {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // -- Vorlage für Plugins
        return $this->render('plugin/demo.html.twig', [
            'plugins' => $plugins->get(),
        ]);
    }
}
