<?php
// src/Controller/ApiController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class ApiController
{
    public function index(): Response
    {
        return new Response('hello world');
    }
}