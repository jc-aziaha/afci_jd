<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SuperAdminFixtures extends Fixture
{

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Créons le superAdmin.
        $superAdmin = $this->createSuperAdmin();

        // Préparons l'insertion du superAdmin dans la table "user" de la base de données.
        $manager->persist($superAdmin);

        // Exécutons la requête.
        $manager->flush();
    }

    private function createSuperAdmin(): User
    {
        $superAdmin = new User();

        $superAdmin->setFirstName("Jean");
        $superAdmin->setLastName("Dupont");
        $superAdmin->setEmail("medecine-du-monde@gmail.com");
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER']);

        $passwordHashed = $this->hasher->hashPassword($superAdmin, "azerty1234A*");
        $superAdmin->setPassword($passwordHashed);

        $superAdmin->setIsVerified(true);
        $superAdmin->setVerifiedAt(new DateTimeImmutable());

        return $superAdmin;
    }
}
