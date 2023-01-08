<?php

namespace App\Controller;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Form\MarkType;
use App\Form\RecipeType;
use App\Repository\MarkRepository;
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
        Request $request
    ) : Response
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

    #[Route('/recette/publique', 'recipe.index.public', methods:['GET'])]
    public function indexPublic (
        PaginatorInterface $paginator, 
        RecipeRepository $repository, 
        Request $request
    ) : Response 
    {   
        $recipes = $paginator->paginate(
            $repository->findPublicRecipe(null),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('pages/recipe/index_public.html.twig', [
            'recipes' => $recipes
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
     * This controller display one recipe for all connected user if this one is public
     *
     * @param Recipe $recipe
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and (recipe.isIsPublic() === true|| user === recipe.getUser())")]
    #[Route('/recette/{id}', 'recipe.show', methods:['GET', 'POST'])]
    public function show (
        Recipe $recipe, 
        Request $request, 
        MarkRepository $markRepository,
        EntityManagerInterface $manager
        ) : Response 
    {
        $mark = new Mark();

        $form = $this->createForm(MarkType::class, $mark);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $mark
                ->setUser($this->getUser())
                ->setRecipe($recipe);

            // check if user is already noed this recipe?
            $existingMark = $markRepository->findOneBy([
                'user' => $this->getUser(),
                'recipe' => $recipe
            ]);
            if (! $existingMark) {
                $manager->persist($mark);
            }
            // if user has noted this recette => change his note
            else {
                $existingMark->setMark(
                    $form->getData()->getMark()
                );
            }
            $manager->flush();

            $this->addFlash('success', 'Votre vote a été pris en compte');
            return $this->redirectToRoute('recipe.show', ['id' => $recipe->getId()]);
        }

        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe,
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
