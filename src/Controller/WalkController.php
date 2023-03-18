<?php

// src/Controller/WalkController.php
namespace App\Controller;

use App\Entity\Walks;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\HttpFoundation\Request;

class WalkController extends AbstractController
{

    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $walk = new Walks();
        $walk->setIdUser($request->request->get('id_user'));
        $walk->setIdWalker($request->request->get('id_walker'));
        $walk->setDate($request->request->get('date'));
        $walk->setStart($request->request->get('start'));
        $walk->setEnd($request->request->get('end'));

        // tell Doctrine you want to (eventually) save the Walks (no queries yet)
        $entityManager->persist($walk);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new walk with id '.$walk->getId());
    }

    public function update(Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        // get the walk from the database
        $walkRepository = $doctrine->getRepository(Walks::class);
        $walk = $walkRepository->find($request->request->get('id'));

        if (!$walk) {
            // walk not found
            return new Response('Walk not found', 401);
        }

        $walk->setInvite($request->request->get('invite'));
        $entityManager->flush();

        return new Response('Updated walk', 200);
    }
}
