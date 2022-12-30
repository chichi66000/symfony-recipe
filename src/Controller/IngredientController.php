<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IngredientController extends AbstractController
{
    /**
     * This controller display all ingredients
     * Undocumented function
     *
     * @param IngredientRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/ingredient', name: 'ingredient.index', methods: ['GET'])]
    public function index(IngredientRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $ingredients = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            10 /*limit per page*/
        );

        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients
        ]);
    }

    /**
     * This controller create new ingredient with a form
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/ingredient/nouveau', name:'ingredient.new', methods: ['GET', 'POST'])]
    public function new (
        Request $request,
        EntityManagerInterface $manager
        ): Response 
    {
        $ingredient = new Ingredient();
        // create form
        $form = $this->createForm(IngredientType::class, $ingredient);
        // validate & send form
        $form ->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            $ingredient->setUser($this->getUser());
            // push to database
            $manager->persist($ingredient);
            $manager->flush();
            // add message
            $this->addFlash(
                'success',
                'Votre ingrédient a été crée'
            );
            // redirect
            return $this->redirectToRoute('ingredient.index');
        }
        return $this->render('pages/ingredient/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    
    /**
     * This controller modify ingredient with id
     *
     * @param Ingredient $ingredient
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/ingredient/edition/{id}", "ingredient.edit", methods:["GET", "POST"])]
    public function edit (
        Ingredient $ingredient,
        Request $request,
        EntityManagerInterface $manager
        ): Response 
    {
        // create form
        $form = $this->createForm(IngredientType::class, $ingredient);
        // validate & send form
        $form ->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            // push to database
            $manager->persist($ingredient);
            $manager->flush();
            // add message
            $this->addFlash(
                'success',
                'Votre ingrédient a été modifié'
            );
            // redirect
            return $this->redirectToRoute('ingredient.index');
        }
        return $this->render('pages/ingredient/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    
    /**
     * This controller delete ingredient
     *
     * @param EntityManagerInterface $manager
     * @param Ingredient $ingredient
     * @return Response
     */
    #[Route('/ingredient/suppression/{id}', 'ingredient.delete', methods: ['GET'])]
    public function delete (EntityManagerInterface $manager, Ingredient $ingredient) : Response 
    {   
        $manager->remove($ingredient);
        $manager->flush();
        // add message
        $this->addFlash(
            'warning',
            'Votre ingrédient a été supprimé'
        );
        return $this->redirectToRoute('ingredient.index');
    }
}
