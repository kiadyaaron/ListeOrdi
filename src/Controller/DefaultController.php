<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DefaultController extends AbstractController{
    #[Route('/', name: 'app_default')]
    public function index(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_liste_ordi_index');
        } else {
            return $this->redirectToRoute('app_home');
        }
    }
}