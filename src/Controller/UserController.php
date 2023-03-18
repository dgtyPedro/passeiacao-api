<?php

// src/Controller/UserController.php
namespace App\Controller;

use App\Entity\Users;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{

    public function create(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $factory = new PasswordHasherFactory([
            'common' => ['algorithm' => 'bcrypt'],
            'memory-hard' => ['algorithm' => 'sodium'],
        ]);
        $passwordHasher = $factory->getPasswordHasher('common');
        $hash = $passwordHasher->hash($_POST['password']);

        $userRepository = $entityManager->getRepository(Users::class);
        $existingUser = $userRepository->findOneBy(['email' => $_POST['email']]);
        $existingUser2 = $userRepository->findOneBy(['phone' => $_POST['phone']]);

        if ($existingUser || $existingUser2) {
            return new Response('There is already a user with this email or phone.');
        }

        $user = new Users();
        $user->setName($_POST['name']);
        $user->setEmail($_POST['email']);
        $user->setPhone($_POST['phone']);
        $user->setPassword($hash);

        // tell Doctrine you want to (eventually) save the Users (no queries yet)
        $entityManager->persist($user);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new user with id '.$user->getId());
    }


    public function login(Request $request, ManagerRegistry $doctrine): Response
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        // get the user from the database
        $userRepository = $doctrine->getRepository(Users::class);
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            // user not found
            return new Response('User not found', 401);
        }

        $factory = new PasswordHasherFactory([
            'common' => ['algorithm' => 'bcrypt'],
            'memory-hard' => ['algorithm' => 'sodium'],
        ]);
        $passwordHasher = $factory->getPasswordHasher('common');

        // check if the provided password matches the user's hashed password
        if (!$passwordHasher->verify($user->getPassword(), $password)) {
            // password does not match
            return new Response('Incorrect password', 401);
        }

        // password matches, create a response with a success message
        return new Response('Welcome ' . $user->getName() . '!');
    }
}
