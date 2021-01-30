<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     * Method ({"GET"})
     */
    public function index(): Response
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        return $this->render('user/index.html.twig', array
        ('users' => $users));

    }
    /**
     * @Route("/user/create", name="create_user")
     * Method ({"GET", "POST"}
     */
    public function createUser(Request $request){
        $user = new User();

        $form = $this->createFormBuilder($user)
            ->add('email', TextType::class, array('attr' =>array('class' => 'form-control')))
            ->add('password', PasswordType::class, array('attr' =>array('class' => 'form-control')))
            ->add('save', SubmitType::class, array(
                'label' =>'Create',
                'attr' =>array('class'=>'btn btn-lg btn-info btn-block')
            ))
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $user = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('user');
        }

        return $this->render('user/create.html.twig',array(
            'form'=>$form->createView()
        ));
    }

    /**
 * @Route ("/user/{id}", name= "view_user")
 * @Method ({"GET"})
 */

public function view($id){
    $user = $this->getDoctrine()->getRepository(User::class)->find($id);
  
    return $this->render('user/view.html.twig', array('user' =>$user));
  
  }

    /**
     * @Route("/user/update/{id}", name="update_user")
     * Method ({"GET", "POST"})
     */
    public function updateUser(Request $request, $id){
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $form = $this->createFormBuilder($user)
            ->add('email', TextType::class, array('attr' =>array('class' => 'form-control')))
            ->add('password', PasswordType::class, array('attr' =>array('class' => 'form-control')))
            ->add('save', SubmitType::class, array(
                'label' =>'Update',
                'attr' =>array('class'=>'btn btn-lg btn-success btn-block')
            ))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('user');
        }

        return $this->render('user/update.html.twig',array('form'=>$form->createView()));
    }

    /**
     * @Route("/user/delete/{id}", name="delete_user")
     * @Method ({"DELETE"})
     */
    public function deleteUser(Request $request, $id){
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('user');
    }

}
