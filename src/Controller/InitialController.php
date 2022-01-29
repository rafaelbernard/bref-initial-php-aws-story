<?php

namespace App\Controller;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InitialController extends AbstractController
{
    #[Route('/')]
    public function index(): Response
    {
        error_log('Error log');

        $log = new Logger('name');
        $log->pushHandler(new StreamHandler('php://stderr'));

        $log->info('Hello from logger');

        return new Response('hello');
    }
}
