<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="contact")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'ContactController',
        ]);
    }
    /**
     * @Route("createContact", name="createContact")
     *Method ({"GET", "POST"}
     */
    public function createContact(Request $request)
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact, array(
            'action' => $this->generateUrl('createContact'),
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($contact);
            $em->flush();

            //return $this->redirectToRoute('home');
            return $this->redirect('http://symfony_project:8000');
        }

        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
