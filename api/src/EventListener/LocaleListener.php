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
        $locale = $request->query->get('locale');

        if ($locale && is_string($locale) && in_array($locale, $this->supportedLocales, true)) {
            $request->setLocale($locale);
        } elseif (null === $locale || !is_string($locale)) {
            return;
        } else {
            $locale = $request->getPreferredLanguage($this->supportedLocales);
            $request->setLocale($locale ?? $this->supportedLocales[0]);
        }
    }
}
