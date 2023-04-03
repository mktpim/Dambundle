<?php

/**
 * Pimcore DAM
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 */

namespace Pimcore\Bundle\DamBundle\Security;

use Pimcore\Bundle\AdminBundle\Security;
use Pimcore\Event\Admin\Login\LogoutEvent;
use Pimcore\Event\AdminEvents;
use Pimcore\Model\Element\Editlock;
use Pimcore\Model\User;
use Pimcore\Tool\Session;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutSuccessHandler extends Security\LogoutSuccessHandler implements LogoutSuccessHandlerInterface, LoggerAwareInterface
{
    /**
     * @inheritDoc
     */
    public function onLogoutSuccess(Request $request)
    {
        $this->logger->debug('Logging out');

        $this->tokenStorage->setToken(null);

        // clear open edit locks for this session
        Editlock::clearSession(session_id());

        /** @var LogoutEvent $event */
        $event = Session::useSession(function (AttributeBagInterface $adminSession) use ($request) {
            $event = null;

            $user = $adminSession->get('user');
            if ($user && $user instanceof User) {
                $event = new LogoutEvent($request, $user);
                $this->eventDispatcher->dispatch(AdminEvents::LOGIN_LOGOUT, $event);

                $adminSession->remove('user');
            }

            Session::invalidate();

            return $event;
        });

        $response = null;
        if ($event && $event->hasResponse()) {
            $response = $event->getResponse();
        } else {
            $response = new RedirectResponse($this->router->generate('pimcore_dam_login'));
        }

        $this->logger->debug('Logout succeeded, redirecting to ' . $response->getTargetUrl());

        return $response;
    }
}
