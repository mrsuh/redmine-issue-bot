<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TelegramController extends AbstractController
{
    /**
     * @Route("/telegram/webhook")
     */
    public function index(Request $request, LoggerInterface $logger): Response
    {
        $logger->info('Telegram webhook', ['request' => json_decode($request->getContent(), true)]);

        return new Response();
    }
}
