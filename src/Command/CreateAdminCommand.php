<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un utilisateur administrateur (ROLE_SUPER_ADMIN)',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'administrateur')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe en clair')
            ->addArgument('nom', InputArgument::REQUIRED, 'Nom')
            ->addArgument('prenom', InputArgument::REQUIRED, 'Prénom')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $plainPassword = $input->getArgument('password');
        $nom = $input->getArgument('nom');
        $prenom = $input->getArgument('prenom');

        $existing = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing) {
            $io->error(sprintf('Un utilisateur avec l\'email "%s" existe déjà.', $email));

            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setRoles(['ROLE_SUPER_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Administrateur "%s" créé avec succès (ROLE_SUPER_ADMIN).', $email));

        return Command::SUCCESS;
    }
}