<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserController extends AbstractController
{
    
    /**
     * This controller to modify user's profil
     *
     * @param User $choosenUser
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user === choosenUser")]
    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(
        User $choosenUser,
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $hasher): Response
    {

        $form = $this->createForm(UserType::class, $choosenUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData());
            // check if password is correct
            if ($hasher -> isPasswordValid($choosenUser, $form->getData()->getPlainPassword())) 
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

   
    /**
     * This controller to modify user's password
     *
     * @param User $choosenUser
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user === choosenUser")]
    #[Route('/utilisateur/edition-mot-de-passe/{id}', 'user.edit.password', methods: ['GET', 'POST'])]
    public function editPassword (
        User $choosenUser, 
        Request $request, 
        EntityManagerInterface $manager, 
        UserPasswordHasherInterface $hasher) : Response
    {
        $form = $this->createForm(UserPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData());
            // check if password is ok -> setPlainPassword = newPassword
            if ($hasher->isPasswordValid($choosenUser, $form->getData()['plainPassword'])) {
                $choosenUser->setUpdatedAt(new \DateTimeImmutable());
                $choosenUser->setPassword(
                    $hasher->hashPassword($choosenUser, $form->getData()['newPassword'])
                );
                $manager->persist($choosenUser);
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
