<?php

namespace App\EventSubscriber;

use App\Exception\ApiException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire('%env(APP_ENV)%')]
        private readonly string $appEnv,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ApiException) {
            $data = [
                'code' => $exception->getStatusCode(),
                'message' => $exception->getMessage(),
                'details' => $exception->details ?? [],
            ];

            $event->setResponse(new JsonResponse($data, $exception->getStatusCode()));

            return;
        }

        $status = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        $message = $exception->getMessage();

        if (500 === $status && 'prod' == $this->appEnv) {
            $message = 'Internal Server Error';
        }

        $data = [
            'code' => $status,
            'message' => $message,
            'details' => [],
        ];

        $event->setResponse(new JsonResponse($data, $status));
    }
}
