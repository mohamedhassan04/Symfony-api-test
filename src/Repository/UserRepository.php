<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;



/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private UserPasswordHasherInterface $passwordEncoder;
    public function __construct(ManagerRegistry $registry, UserPasswordHasherInterface $passwordEncoder)
    {
        parent::__construct($registry, User::class);
        $this->passwordEncoder = $passwordEncoder;
    }

 public function createUser($firstName, $lastName, $email, $password, $roles)
    {
        $entityManager = $this->getEntityManager();

        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        // Hash the password
        $hashedPassword = $this->passwordEncoder->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        if (defined('App\Enum\UserRole::' . $roles)) {
            $user->setRoles($roles);
             
            $entityManager->persist($user);
            $entityManager->flush();

            return $user;
        }

        return null; // or throw an exception for invalid role
    }
}


