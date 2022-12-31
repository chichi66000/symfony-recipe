<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Repository\RepositoryFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RecipeController extends AbstractController
{
    /**
     * This controller display all recipes
     *
     * @param PaginatorInterface $paginator
     * @param RecipeRepository $repository
     * @param Request $request
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/recette', name: 'recipe.index', methods: ['GET'])]
    public function index(
        PaginatorInterface $paginator, 
        RecipeRepository $repository, 
        Request $request) : Response
    {
        $recipes = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            10 /*limit per page*/
        );
        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    /**
     * This controller add new recipe
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/recette/creation', 'recipe.new', methods: ['GET', 'POST'])]
    public function new (Request $request, EntityManagerInterface $manager) : Response 
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())  {
            $recipe = $form->getData();
            $recipe->setUser($this->getUser());
            
            $manager->persist($recipe);
            $manager->flush();
            $this->addFlash('success', 'Votre recette a été créé');
            return $this->redirectToRoute('recipe.index');
        }
        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * This controller modify recipe with id
     *
     * @param Recipe $Recipe
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user === recipe.getUser()")]
    #[Route("/recette/edition/{id}", "recipe.edit", methods:["GET", "POST"])]
    public function edit (
        Recipe $recipe,
        Request $request,
        EntityManagerInterface $manager
        ): Response 
    {
        // create form
        $form = $this->createForm(RecipeType::class, $recipe);
        // validate & send form
        $form ->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            // push to database
            $manager->persist($recipe);
            $manager->flush();
            // add message
            $this->addFlash(
                'success',
                'Votre recette a été modifié'
            );
            // redirect
            return $this->redirectToRoute('recipe.index');
        }
        return $this->render('pages/recipe/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * This controller delete recipe
     *
     * @param EntityManagerInterface $manager
     * @param Recipe $recipe
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user === recipe.getUser()")]
    #[Route('/recipe/suppression/{id}', 'recipe.delete', methods: ['GET'])]
    public function delete (EntityManagerInterface $manager, Recipe $recipe) : Response 
    {   
        $manager->remove($recipe);
        $manager->flush();
        // add message
        $this->addFlash(
            'success',
            'Votre recette a été supprimé'
        );
        return $this->redirectToRoute('recipe.index');
    }

}
