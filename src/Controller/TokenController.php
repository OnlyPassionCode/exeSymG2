<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserActivationToken;
use App\Repository\UserActivationTokenRepository;
use Doctrine\DBAL\Driver\Mysqli\Exception\HostRequired;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class TokenController extends AbstractController
{

    public function __construct(
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
        private AuthenticationUtils $authenticationUtils,
        private UserActivationTokenRepository $userActivationTokenRepository
    ) {
    }

    #[Route('/verify/email', name: 'app_token')]
    public function index(Request $request): Response
    {
        $token = $request->query->get('token');
        $signature = $request->query->get('signature');

        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();

        if($token === null || $signature === null)
            return $this->render('token/index.html.twig', [
                'title' => 'Token',
                'lost' => true,
                'last_username' => $lastUsername,
            ]);
        
        $token = htmlspecialchars($token);
        $signature = htmlspecialchars($signature);

        /**
         * @var \App\Entity\UserActivationToken
         */
        $userActivationToken = $this->userActivationTokenRepository->findOneBy([
            'token' => $token
        ]);

        if($userActivationToken === null)
            return $this->render('token/index.html.twig', [
                'title' => 'Token',
                'token' => true,
                'last_username' => $lastUsername,
            ]);

        if($userActivationToken->getExpiresAt()->getTimestamp() < time()){
            return $this->render('token/index.html.twig', [
                'title' => 'Token',
                'expires' => true,
                'last_username' => $lastUsername,
            ]);
        };

        /**
         * @var User
         */
        $user = $userActivationToken->getUser();

        if($signature !== hash_hmac('sha256', $user->getEmail(), $_ENV['APP_SECRET_SIGNATURE'])){
            return $this->render('token/index.html.twig', [
                'title' => 'Token',
                'signature' => true,
                'last_username' => $lastUsername,
            ]);
        }

        $user->setActivate(true);

        $this->entityManager->remove($userActivationToken);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->render('token/index.html.twig', [
            'title' => 'Token',
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/verify/send', name: 'app_send')]
    public function send(Request $request): Response
    {
        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();
        /**
         * @var User
         */
        $user = $this->getUser();
        if($user === null)
            return $this->render('token/index.html.twig', [
                'title' => 'Token',
                'connect' => true,
                'last_username' => $lastUsername,
            ]);
        
        if($user->isActivate())
            return $this->redirectToRoute('app_home');

        /**
         * @var UserActivationToken
         */
        $userActivationToken = $this->userActivationTokenRepository->findOneBy([
            'user' => $user
        ]);


        if($userActivationToken !== null){
            $actualTime = time();
            $lapsTime = $actualTime - $userActivationToken->getCreatedAt()->getTimestamp();
            if($lapsTime < 60 * 10 /* 60 secondes * 10 = 10 minutes */){
                return $this->render('token/index.html.twig', [
                    'title' => 'Token',
                    'lapsTime' => $lapsTime,
                    'last_username' => $lastUsername,
                ]);
            }
            $this->entityManager->remove($userActivationToken);
            $this->entityManager->flush();
        }
        
        $this::sendEmailValidation($user, $this->entityManager, $this->mailer, $request->getSchemeAndHttpHost());

        return $this->render('token/index.html.twig', [
            'title' => 'Token',
            'send' => true,
            'last_username' => $lastUsername,
        ]);
    }

    public static function sendEmailValidation(User $user, EntityManagerInterface $entityManager, MailerInterface $mailer, string $host){
        // On crée le token
        $createAt = new \DateTimeImmutable();
        $expireAt = $createAt->modify('+60 minutes');
        $token = $user->getUniqid();
        // La signature est basé sur l'email et la key secret qui est pas secret pour le coup mdr
        $signature = hash_hmac('sha256', $user->getEmail(), $_ENV['APP_SECRET_SIGNATURE']);

        $userActivationToken = new UserActivationToken();
        $userActivationToken->setToken($token)
            ->setCreatedAt($createAt)
            ->setExpiresAt($expireAt)
            ->setUser($user);

        // On met le token en DB
        $entityManager->persist($userActivationToken);
        $entityManager->flush();

        // On crée le template email avec l'url d'activation
        $email = (new TemplatedEmail())
            ->from(new Address('ti_symfony_2024@kevin-vanwassenhove.com', 'TI Symfony 2024 By Kevin'))
            ->to($user->getEmail())
            ->subject('Activation de votre compte')
            ->htmlTemplate('email/activation.html.twig') // Chemin vers le template
            ->context([
                'url' => "$host/verify/email?signature=".$signature.'&token='.$token, // Passer l'URL d'activation
            ]);
        // On envoie le mail
        // Attention, le worker "php bin/console messenger:consume async" doit être allumé
        $mailer->send($email);
    }
}
