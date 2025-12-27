<?php

namespace App\EventListener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleListener
{
    public function __construct(
        #[Autowire('%supported_locales%')]
        private readonly array $supportedLocales,
    ) {
    }

    #[AsEventListener(event: KernelEvents::REQUEST, priority: 100)]
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $locale = (string) $request->query->get('locale');

        if (!in_array($locale, $this->supportedLocales, true)) {
            $locale = $request->getPreferredLanguage($this->supportedLocales);
        }

        $request->setLocale($locale ?? 'en');
    }
}
