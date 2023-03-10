<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
    #[IsGranted('ROLE_USER')]
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
    #[Route('/ingredient/creation', name:'ingredient.new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new (
        Request $request,
        EntityManagerInterface $manager
        ): Response 
    {
        $ingredient = new Ingredient();
        // create form
        $form = $this->createForm(IngredientType::class, $ingredient,  ['route' => "ingredient.new"]);
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
                'Votre ingr??dient a ??t?? cr??e'
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
    #[Security("is_granted('ROLE_USER') and user === ingredient.getUser()")]
    #[Route("/ingredient/edition/{id}", "ingredient.edit", methods:["GET", "POST"])]
    public function edit (
        Ingredient $ingredient,
        Request $request,
        EntityManagerInterface $manager
        ): Response 
    {
        // create form
        $form = $this->createForm(IngredientType::class, $ingredient, ['route' => "ingredient.edit"]);
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
                'Votre ingr??dient a ??t?? modifi??'
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
    #[Security("is_granted('ROLE_USER') and user === ingredient.getUser()")]
    #[Route('/ingredient/suppression/{id}', 'ingredient.delete', methods: ['GET'])]
    public function delete (EntityManagerInterface $manager, Ingredient $ingredient) : Response 
    {   
        $manager->remove($ingredient);
        $manager->flush();
        // add message
        $this->addFlash(
            'warning',
            'Votre ingr??dient a ??t?? supprim??'
        );
        return $this->redirectToRoute('ingredient.index');
    }
}
