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

    public const LOGIN_ROUTE = 'security_login'; // Route de connexion

    public function __construct(private UrlGeneratorInterface $urlGenerator, private EntityManagerInterface $entityManager)
    {}

    public function authenticate(Request $request, ): Passport
    {
        $email = $request->request->get('email', ''); // Récupère l'email saisi dans le formulaire de connexion

        $user = $this->entityManager->getRepository(Participant::class)->findOneBy(['email' => $email]);

        if (!$user) { // Si l'utilisateur n'a pas été trouvé
            throw new CustomUserMessageAuthenticationException('Email introuvable.'); // Lève une exception pour indiquer que l'email est introuvable
        }

        if (!$user->isActif()) { // Si le compte de l'utilisateur est désactivé
            throw new CustomUserMessageAuthenticationException('Votre compte est désactivé.'); // Lève une exception pour indiquer que le compte est désactivé
        }

        $request->getSession()->set(Security::LAST_USERNAME, $email); // Stocke l'email de l'utilisateur dans la session

        return new Passport(
            new UserBadge($email), // Identifiant de l'utilisateur pour l'authentification
            new PasswordCredentials($request->request->get('password', '')), // Mot de passe de l'utilisateur
            [
                new RememberMeBadge(), // Badge pour activer la fonction "Se souvenir de moi"
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')), // Badge pour vérifier le jeton CSRF
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) { // Si l'utilisateur a été redirigé vers la page de connexion avant l'authentification
            return new RedirectResponse($targetPath); // Redirige l'utilisateur vers la page de destination initiale
        }

        return new RedirectResponse($this->urlGenerator->generate('home_home')); // Redirige l'utilisateur vers la page d'accueil
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE); // Renvoie l'URL de la page de connexion
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
