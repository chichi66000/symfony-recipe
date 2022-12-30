<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserController extends AbstractController
{
    
    /**
     * This controller to modify user's profil
     *
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $hasher): Response
    {
        // vérifier si user est login?
        if (!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }
        // dd($user, $this->getUser());

        // check if user has is the same id?
        if ($this->getUser() !== $user) {
            return $this->redirectToRoute('recipe.index');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData());
            // check if password is correct
            if ($hasher -> isPasswordValid($user, $form->getData()->getPlainPassword())) 
            {
                $user = $form->getData();
                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', 'Votre profil a été modifié');
                return $this->redirectToRoute('recipe.index');
            }
            else {
                $this->addFlash('warning', 'Le mot de pass n\'est pas correct');

            }

            
        }

        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/utilisateur/edition-mot-de-passe/{id}', 'user.edit.password', methods: ['GET', 'POST'])]
    public function editPassword (
        User $user, 
        Request $request, 
        EntityManagerInterface $manager, 
        UserPasswordHasherInterface $hasher) : Response
    {
        // vérifier si user est login?
        if (!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }
        // dd($user, $this->getUser());

        // check if user has is the same id?
        if ($this->getUser() !== $user) {
            return $this->redirectToRoute('recipe.index');
        }

        $form = $this->createForm(UserPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData());
            // check if password is ok -> setPlainPassword = newPassword
            if ($hasher->isPasswordValid($user, $form->getData()['plainPassword'])) {
                $user->setUpdatedAt(new \DateTimeImmutable());
                $user->setPassword(
                    $hasher->hashPassword($user, $form->getData()['newPassword'])
                );
                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', 'Le mot de passe a été modifié');
                return $this->redirectToRoute('recipe.index');
            }
            else {
                $this->addFlash('warning', 'Le mot de passe est incorrect');
            }

        }
        return $this->render('pages/user/edit_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
