<?php
namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController {
    #[Route('/', 'home.index', methods: ['GET'])]
    public function index(RecipeRepository $repository):Response
    {   
        $recipes = $repository->findPublicRecipe(3);
        return $this->render('pages/home.html.twig', [
            'recipes' => $recipes
        ]);
    }
}