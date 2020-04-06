<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\UserManagement\Component\Model\UserInterface as AkeneoUserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CollectApiErrorsEventSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'collectApiErrors'];
    }

    public function collectApiErrors(ExceptionEvent $event): void
    {
        /* We should retrieve the Connection used for the authentication if any (instead of the user).
        - Maybe create a Connection[Storage|Context] service that use the ApiAuthenticationEvent to store the client_id
            and is able to do the subsequent queries to retrieve the Connection / User details. */

        $user = $this->getUser();
        if (null === $user || false === $user->isApiUser()) {
            return;
        }

        $exception = $event->getException();
        if (false === $exception instanceof UnprocessableEntityHttpException) {
            return;
        }

        $data = [
            'username' => $user->getUsername(),
            'route' => $event->getRequest()->get('_route'),
            'error' => [
                'class' => get_class($exception),
                // 'code' =>
                'message' => $exception->getMessage()
            ]
        ];

        if ($exception instanceof ViolationHttpException) {
            $data['error']['violations'] = array_map(function (ConstraintViolation $violation) {
                return [
                    'class' => get_class($violation->getConstraint()),
                    'code' => $violation->getCode(),
                    'message' => $violation->getMessage()
                ];
            }, iterator_to_array($exception->getViolations()));
        }

        dd($data);
    }

    private function getUser(): ?AkeneoUserInterface
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        if (false === $user instanceof AkeneoUserInterface) {
            return null;
        }

        return $user;
    }
}
