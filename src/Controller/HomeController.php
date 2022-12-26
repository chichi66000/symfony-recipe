<?php
namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController {
    #[Route('/', 'home.index', methods: ['GET'])]
    public function index():Response
    {   
        // #[Route('/', 'home.index', method: ['GET'])]
        // return $this->render('home.html.twig');
        return $this->render('home.html.twig');
    }
}