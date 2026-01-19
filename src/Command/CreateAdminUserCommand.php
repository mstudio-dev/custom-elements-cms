<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Erstellt einen neuen Admin-User',
)]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        // Ask for email
        $emailQuestion = new Question('E-Mail: ');
        $emailQuestion->setValidator(function ($value) {
            if (empty($value)) {
                throw new \RuntimeException('E-Mail darf nicht leer sein');
            }
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Ungültige E-Mail-Adresse');
            }
            return $value;
        });
        $email = $helper->ask($input, $output, $emailQuestion);

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error(sprintf('User mit E-Mail "%s" existiert bereits!', $email));
            return Command::FAILURE;
        }

        // Ask for username
        $usernameQuestion = new Question('Username: ');
        $usernameQuestion->setValidator(function ($value) {
            if (empty($value)) {
                throw new \RuntimeException('Username darf nicht leer sein');
            }
            return $value;
        });
        $username = $helper->ask($input, $output, $usernameQuestion);

        // Ask for password
        $passwordQuestion = new Question('Passwort: ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setValidator(function ($value) {
            if (empty($value)) {
                throw new \RuntimeException('Passwort darf nicht leer sein');
            }
            if (strlen($value) < 8) {
                throw new \RuntimeException('Passwort muss mindestens 8 Zeichen lang sein');
            }
            return $value;
        });
        $password = $helper->ask($input, $output, $passwordQuestion);

        // Create user
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setRoles(['ROLE_ADMIN']);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Admin-User "%s" (%s) wurde erfolgreich erstellt!', $username, $email));

        return Command::SUCCESS;
    }
}
