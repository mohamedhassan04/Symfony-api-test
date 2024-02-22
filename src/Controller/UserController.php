<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
//Import the ManagerRegistry from Doctrine\Persistence namespace
use Doctrine\Persistence\ManagerRegistry;
//Import the Request class from Symfony\Component\HttpFoundation namespace
use Symfony\Component\HttpFoundation\Request;
//Import the User entity class from the App\Entity namespace
use App\Entity\User;

#[Route('/api', name: 'api_')]
class UserController extends AbstractController
{
    /* Create user */
   #[Route('/register', name: 'create_user', methods:['post'])]
public function create(ManagerRegistry $doctrine, Request $request): JsonResponse
{
   // Get the entity manager from Doctrine
   $entityManager = $doctrine->getManager(); 

   // Create a new User object
   $user = new User();

   // Set the first name of the user from the request
   $user->setFirstName($request->request->get("firstName"));

   // Set the last name of the user from the request
   $user->setLastName($request->request->get("lastName"));

   // Set the email of the user from the request
   $user->setEmail($request->request->get("email"));

   // Set the password of the user from the request
   $user->setPassword($request->request->get("password"));

   // Persist the user object to the database
   $entityManager->persist($user);

   // Commit changes to the database
   $entityManager->flush();

   // Prepare data to be returned in the JSON response
   $data =  [
        'id' => $user->getId(),
        'firstName' => $user->getFirstName(),
        'lastName' => $user->getLastName(),
        'email' => $user->getEmail(),
        'password' => $user->getPassword(),  // Note: storing passwords in plain text is not recommended in production
    ];

   // Return JSON response with user data
   return $this->json($data);       
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