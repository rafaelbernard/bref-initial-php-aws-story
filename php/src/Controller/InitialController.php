<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InitialController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/')]
    public function index(): Response
    {
        return new Response('hello');
    }

    #[Route(path: '/info', methods: ['GET'])]
    public function info(): Response
    {
        $envvars = getenv();
        unset(
            $envvars['AWS_SESSION_TOKEN'],
            $envvars['AWS_SECRET_ACCESS_KEY'],
            $envvars['AWS_ACCESS_KEY_ID'],
        );

        $this->logger->info('envvars', $envvars);

        $requestContext = json_decode($envvars['LAMBDA_REQUEST_CONTEXT']);

        $reponse = [
            'return' => 'hello',
            'env' => getenv('ENV'),
            'APP_ENV' => getenv('APP_ENV'),
            'requestContext' => $requestContext,
        ];

        return new JsonResponse($reponse);
    }
}
