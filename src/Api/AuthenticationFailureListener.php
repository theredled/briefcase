<?php
// src/EventListener/AuthenticationFailureListener.php
namespace App\Api;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Psr\Log\LoggerInterface;

#[AsEventListener(event: LoginFailureEvent::class)]
class AuthenticationFailureListener
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(LoginFailureEvent $event): void
    {
        $exception = $event->getException();

        $this->logger->error('Login failed', [
            'message' => $exception->getMessage(),
            'request' => $event->getRequest()->getContent(),
        ]);
    }
}