<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
        //$users = $this->getDoctrine()->getRepository(User::class)->findAll();

        $em = $this->getDoctrine()->getManager();
        $connection = $em->getConnection();
        $users = $connection->prepare("SELECT id,name,email FROM user WHERE deleted_at IS NULL");
        $users->execute();

        return $this->render('user/index.html.twig', array
        ('users' => $users));

    }

    /**
     * @Route("/user/create", name="create_user")
     * Method ({"GET", "POST"}
     */
    public function createUser(Request $request)
    {
        $user = new User();

        $form = $this->createFormBuilder($user)
            ->add('email', TextType::class, array('attr' => array('class' => 'form-control')))
            ->add('password', PasswordType::class, array('attr' => array('class' => 'form-control')))
            ->add('save', SubmitType::class, array(
                'label' => 'Create',
                'attr' => array('class' => 'btn btn-lg btn-info btn-block')
            ))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('user');
        }

        return $this->render('user/create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route ("/user/view/{id}", name= "view_user")
     * @Method ({"GET"})
     */

    public function view($id)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        return $this->render('user/view.html.twig', array('user' => $user));

    }

    /**
     * @Route("/user/update/{id}", name="update_user")
     * Method ({"GET", "POST"})
     */
    public function updateUser(Request $request, $id)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $form = $this->createFormBuilder($user)
            ->add('email', TextType::class, array('attr' => array('class' => 'form-control')))
            ->add('password', PasswordType::class, array('attr' => array('class' => 'form-control')))
            ->add('save', SubmitType::class, array(
                'label' => 'Update',
                'attr' => array('class' => 'btn btn-lg btn-success btn-block')
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('user');
        }

        return $this->render('user/update.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/user/delete/{id}", name="delete_user")
     * @Method ({"DELETE"})
     */
    public function deleteUser(Request $request, $id)
    {
        /*$user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();*/

        $em = $this->getDoctrine()->getManager();
        $connection = $em->getConnection();
        $statement = $connection->prepare("UPDATE user SET deleted_at = NOW() WHERE id = :id");
        $statement->execute(['id' => $id]);

        return $this->redirectToRoute('user');
    }

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct( EntityManagerInterface $entityManager)
    {

        $this->entityManager = $entityManager;
    }

    private function getData(): array
    {
        /**
         * @var $user User[]
         */
        $list = [];
        $users = $this->entityManager->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            $list[] = [
                $user->getName(),
                $user->getEmail(),
            ];
        }
        return $list;
    }

    /**
     * @Route("/user/export",  name="export_user")
     */
    public function export($fileName = 'users.xlsx')
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('User List');

        $sheet->getCell('A1')->setValue('Name');
        $sheet->getCell('B1')->setValue('Email');

        // Increase row cursor after header write
        $sheet->fromArray($this->getData(), null, 'A2', true);

        $writer = new Xlsx($spreadsheet);

        //$writer->save('/path/to/users.xlsx');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');

        return $this->redirectToRoute('user');
    }

}
