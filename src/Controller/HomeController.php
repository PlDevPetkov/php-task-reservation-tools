<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @class HomeController
 * @package App\Controller
 */
class HomeController
{
    #[Route('/api/home', name: 'home')]
    public function home(): Response
    {
        return new Response(
            json_encode(['message' => 'Welcome to the reservations app!']),
            headers: ['content-type' => 'application/json']
        );
    }
}
