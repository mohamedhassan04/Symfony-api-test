<?php

namespace App\Controller;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
//Import the ManagerRegistry from Doctrine\Persistence namespace
use Doctrine\Persistence\ManagerRegistry;
//Import the Request class from Symfony\Component\HttpFoundation namespace
use Symfony\Component\HttpFoundation\Request;
//Import the User entity class from the App\Entity namespace
use App\Entity\User;
use App\Enum\UserRole; // Import the UserRole enum



#[Route('/api', name: 'api_')]
class UserController extends AbstractController
{
    /* Create user */
   #[Route('/register', name: 'create_user', methods:['post'])]
public function create(UserRepository $userRepository, Request $request): JsonResponse
{
    // This the body of the request
        $requestData = [
            'firstName' => $request->request->get("firstName"),
            'lastName' => $request->request->get("lastName"),
            'email' => $request->request->get("email"),
            'password' => $request->request->get("password"),
            'roles' => $request->request->get("roles"),
        ];

       try{ 
       // Create a new user from the request data and repository
        $user = $userRepository->createUser( 
                $requestData['firstName'],
                $requestData['lastName'],
                $requestData['email'],
                $requestData['password'],
                $requestData['roles']);
       // Test if the user was created
        if ($user) {
            $data = [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'password' => $user->getPassword(),  // Note: storing passwords in plain text is not recommended in production
                'roles' => $user->getRoles(),
            ];
        // return the response
            return $this->json($data,201);
        } else {
            return $this->json(['error' => 'Invalid role provided'], 400);
        }}
       
       
       catch(UniqueConstraintViolationException $exception){
          return $this->json(['error' =>  'Email address is already in use.'],400);
       }
    }


/* Get all users */
 #[Route('/users', name: 'get_all_user', methods:['get'])]
public function getAllUsers(ManagerRegistry $doctrine, Request $request): JsonResponse
{
      $users = $doctrine
            ->getRepository(User::class)
            ->findAll();
   
        $data = [];
   
        foreach ($users as $users) {
           $data[] = [
               'id' => $users->getId(),
               'firstName' => $users->getFirstName(),
               'lastName' => $users->getLastName(),
               'email' => $users->getEmail(),
           ];
        }
   
        return $this->json($data);
}

/* Get user by id */
#[Route('/user/{id}', name: 'get_one_user', methods:['get'] )]
    public function getUserById(ManagerRegistry $doctrine, string $id): JsonResponse
    {
        $user = $doctrine->getRepository(User::class)->find($id);
   
        if (!$user) {
   
            return $this->json('No user found for id ' . $id, 404);
        }
   
        $data =  [
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
        ];
           
        return $this->json($data);
    }

    /* delete user by id */
#[Route('/user/{id}', name: 'remove_user', methods:['delete'] )]
    public function removeUser(ManagerRegistry $doctrine, string $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $user = $doctrine->getRepository(User::class)->find($id);
   
        if (!$user) {
   
            return $this->json('No user found for id ' . $id, 404);
        }
   
       $entityManager->remove($user);
       $entityManager->flush();
           
        return $this->json('User deleted successfully');
    }
}