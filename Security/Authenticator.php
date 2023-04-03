<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Security;

use Pimcore\Bundle\AdminBundle\Security\User\User;
use Pimcore\Bundle\DamBundle\Dam\Facade;
use Pimcore\Cache\Runtime;
use Pimcore\Event\Admin\Login\LoginCredentialsEvent;
use Pimcore\Event\Admin\Login\LoginFailedEvent;
use Pimcore\Event\AdminEvents;
use Pimcore\Model\User as UserModel;
use Pimcore\Tool\Authentication;
use Pimcore\Tool\Session;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class Authenticator extends \Pimcore\Bundle\AdminBundle\Security\Guard\AdminAuthenticator implements LoggerAwareInterface
{
    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'pimcore_dam_login_check' || parent::supports($request);
    }


    /**
     * @inheritDoc
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        if ($request->isXmlHttpRequest()) {
            // TODO use a JSON formatted error response?
            $response = new Response('Session expired or unauthorized request. Please reload and try again!');
            $response->setStatusCode(Response::HTTP_FORBIDDEN);

            return $response;
        }

        $url = $this->router->generate('pimcore_dam_login');

        return new RedirectResponse($url);
    }

    /**
     * @inheritDoc
     */
    public function getCredentials(Request $request)
    {
        $credentials = null;

        if ($request->attributes->get('_route') === 'pimcore_dam_login_check') {
            if ($request->get('mannington-public')) {
                return [
                    'mannington-guest' => true
                ];
            } elseif ($request->get('phenix-public')) {
                return [
                    'phenix-guest' => true
                ];
            } else {
                if (!null === $username = $request->get('username')) {
                    throw new AuthenticationException('Missing username');
                }

                $this->bruteforceProtectionHandler->checkProtection($username);

                if ($request->getMethod() === 'POST' && $password = $request->get('password')) {
                    $credentials = [
                        'username' => $username,
                        'password' => $password
                    ];
                } elseif ($token = $request->get('token')) {
                    $credentials = [
                        'username' => $username,
                        'token' => $token,
                        'reset' => (bool)$request->get('reset', false)
                    ];
                }

                $event = new LoginCredentialsEvent($request, $credentials);
                $this->dispatcher->dispatch(AdminEvents::LOGIN_CREDENTIALS, $event);

                return $event->getCredentials();
            }
        } else {
            if ($pimcoreUser = Authentication::authenticateSession($request)) {
                return [
                    'user' => $pimcoreUser
                ];
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var User|null $user */
        $user = null;

        // echo ('<pre>');
        // print_r($credentials);

        if (!is_array($credentials)) {
            throw new AuthenticationException('Invalid credentials');
        }

        if (isset($credentials['user']) && $credentials['user'] instanceof UserModel) {
            $user = new User($credentials['user']);
        } else {
            if ($credentials['mannington-guest'] === true) {
                $user = new User(Facade::getPublicUser('mannington-user'));
            } elseif ($credentials['phenix-guest'] === true) {
                $user = new User(Facade::getPublicUser('phenix-user'));
            } elseif (!isset($credentials['username'])) {
                throw new AuthenticationException('Missing username');
            } elseif (isset($credentials['password'])) {
                $pimcoreUser = Authentication::authenticatePlaintext($credentials['username'], $credentials['password']);

                if ($pimcoreUser) {
                    $user = new User($pimcoreUser);
                } else {
                    // trigger LOGIN_FAILED event if user could not be authenticated via username/password
                    $event = new LoginFailedEvent($credentials);
                    $this->dispatcher->dispatch(AdminEvents::LOGIN_FAILED, $event);

                    if ($event->hasUser()) {
                        $user = new User($event->getUser());
                    } else {
                        throw new AuthenticationException('Failed to authenticate with username and password');
                    }
                }
            } elseif (isset($credentials['token'])) {
                $pimcoreUser = Authentication::authenticateToken($credentials['username'], $credentials['token']);

                if ($pimcoreUser) {
                    $user = new User($pimcoreUser);
                } else {
                    throw new AuthenticationException('Failed to authenticate with username and token');
                }

                if ($credentials['reset']) {
                    // save the information to session when the user want's to reset the password
                    // this is because otherwise the old password is required => see also PIMCORE-1468

                    Session::useSession(function (AttributeBagInterface $adminSession) {
                        $adminSession->set('password_reset', true);
                    });
                }
            } else {
                throw new AuthenticationException('Invalid authentication method, must be either password or token');
            }

            if ($user && Authentication::isValidUser($user->getUser())) {
                $pimcoreUser = $user->getUser();

                Session::useSession(function (AttributeBagInterface $adminSession) use ($pimcoreUser) {
                    Session::regenerateId();
                    $adminSession->set('user', $pimcoreUser);
                });
            }
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->bruteforceProtectionHandler->addEntry($request->get('username'), $request);

        $url = $this->router->generate('pimcore_dam_login', [
            'auth_failed' => 'true'
        ]);

        return new RedirectResponse($url);
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        /** @var UserModel $user */
        $user = $token->getUser()->getUser();

        // set user language
        $request->setLocale($user->getLanguage());
        $this->translator->setLocale($user->getLanguage());

        // set user on runtime cache for legacy compatibility
        Runtime::set('pimcore_admin_user', $user);

        // as we authenticate statelessly (short lived sessions) the authentication is called for
        // every request. therefore we only redirect if we're on the login page
        if (!in_array($request->attributes->get('_route'), [
            'pimcore_dam_login',
            'pimcore_dam_login_check'
        ])) {
            return null;
        }

        $url = $this->router->generate('pimcore_dam_asset_list');

        return new RedirectResponse($url);
    }
}
