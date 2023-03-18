<?php

// src/Controller/WalkerController.php
namespace App\Controller;

use App\Entity\Walkers;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\HttpFoundation\Request;

class WalkerController extends AbstractController
{

    public function index(ManagerRegistry $doctrine): Response
    {
        // get the walker from the database
        $walkerRepository = $doctrine->getRepository(Walkers::class);
        $walkers = $walkerRepository->findAll();
        foreach ($walkers as $key=>$walker){
            $walkers[$key] = (array) $walker;
        }
        return new JsonResponse($walkers);
    }

    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $factory = new PasswordHasherFactory([
            'common' => ['algorithm' => 'bcrypt'],
            'memory-hard' => ['algorithm' => 'sodium'],
        ]);
        $passwordHasher = $factory->getPasswordHasher('common');
        $hash = $passwordHasher->hash($request->request->get('password'));

        $walkerRepository = $entityManager->getRepository(Walkers::class);
        $existingWalker = $walkerRepository->findOneBy(['email' => $request->request->get('email')]);
        $existingWalker2 = $walkerRepository->findOneBy(['phone' => $request->request->get('phone')]);

        if ($existingWalker || $existingWalker2) {
            return new Response('There is already a walker with this email or phone.', 401);
        }

        $walker = new Walkers();
        $walker->setName($request->request->get('name'));
        $walker->setEmail($request->request->get('email'));
        $walker->setPhone($request->request->get('phone'));
        $walker->setPassword($hash);

        // tell Doctrine you want to (eventually) save the Walkers (no queries yet)
        $entityManager->persist($walker);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new walker with id '.$walker->getId(), 200);
    }


    public function login(Request $request, ManagerRegistry $doctrine): Response
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        // get the walker from the database
        $walkerRepository = $doctrine->getRepository(Walkers::class);
        $walker = $walkerRepository->findOneBy(['email' => $email]);

        if (!$walker) {
            // walker not found
            return new Response('Walker not found', 401);
        }

        $factory = new PasswordHasherFactory([
            'common' => ['algorithm' => 'bcrypt'],
            'memory-hard' => ['algorithm' => 'sodium'],
        ]);
        $passwordHasher = $factory->getPasswordHasher('common');

        // check if the provided password matches the walker's hashed password
        if (!$passwordHasher->verify($walker->getPassword(), $password)) {
            // password does not match
            return new Response('Incorrect password', 401);
        }

        // password matches, create a response with a success message
        return new Response('Welcome ' . $walker->getName() . '!', 200);
    }
}
