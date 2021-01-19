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
        $logger->info('Telegram webhook', ['request' => $request->request->all()]);

        return new Response();
    }
}
