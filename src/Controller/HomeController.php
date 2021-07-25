<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Turbo\Stream\TurboStreamResponse;

class HomeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ){}

    #[Route('/', name: 'home')]
    public function index(Request $request): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message, []);

        $emptyForm = clone $form;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->em->persist($message);
            $this->em->flush();

            if (TurboStreamResponse::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, only send the HTML to update using a TurboStreamResponse
                return $this->renderForm('message/success.stream.html.twig', ['message' => $message, 'form' => $emptyForm,], new TurboStreamResponse());
            }

            return $this->redirectToRoute('home');
        }

        return $this->renderForm('home/index.html.twig', [
            'form' => $form,
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/messages/list', name: 'list_messages')]
    public function list(): Response
    {
        $allMessages = $this->em->getRepository(Message::Class)->findAll();
        return $this->render('message/list.html.twig', [
            'allMessages' => $allMessages,
        ]);
    }
}
