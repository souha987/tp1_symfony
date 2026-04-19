<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MailController extends AbstractController
{
    #[Route('/mail/test')]
    public function test(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('tp@symfony.com')
            ->to('test@gmail.com')
            ->subject('TP4 Symfony Mailer')
            ->text('Email envoyé avec Symfony')
            ->html('<h1>Bonjour 👋</h1>');

        $mailer->send($email);

        return new Response("Email envoyé !");
    }
}