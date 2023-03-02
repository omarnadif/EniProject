<?php

namespace App\Security;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class UserAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'security_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator, private EntityManagerInterface $entityManager)
    {}

    public function authenticate(Request $request, ): Passport
    {
        $email = $request->request->get('email', '');

        $user = $this->entityManager->getRepository(Participant::class)->findOneBy(['email' => $email]);

        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Email introuvable.');
        }

        if (!$user->isActif()) {
            throw new CustomUserMessageAuthenticationException('Votre compte est désactivé.');
        }

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new RememberMeBadge(),
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('home_home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}

/*  Brouillon de connexion avec pseudo ou email à mettre dans authenticate normalement
$emailOrPseudo = $request->request->get('email', '');

if (strpos($emailOrPseudo, '@') === false) {

    //Si dans l'input il n'y a pas de "@", alors c'est le pseudo
    $pseudo = $emailOrPseudo;
    $email = ''; //email non spécifié dans le badge de l'utilisateur
} else {
    //Si dans l'input il y a un "@", alors c'est le email
    $email = $emailOrPseudo;
    $pseudo = ''; //pseudo non spécifié dans le badge de l'utilisateur
}

$request->getSession()->set(Security::LAST_USERNAME, $emailOrPseudo);

return new Passport(
    new UserBadge($emailOrPseudo, fn ($pseudo)=> $this->participantRepository->findOneByPseudoOrEmail($pseudo, $email)),
    new PasswordCredentials($request->request->get('password', '')),
    [
        new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
    ]
);
*/
