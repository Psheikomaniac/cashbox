<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ContentSecurityPolicySubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -10], // Niedrige Priorität, um nach anderen Modifikationen auszuführen
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();

        // CSP nur für HTML-Antworten oder wenn explizit angefordert
        $contentType = $response->headers->get('Content-Type');
        if (!$contentType || (!str_contains($contentType, 'text/html') && !str_contains($contentType, 'application/json'))) {
            return;
        }

        // CSP-Header setzen
        $response->headers->set('Content-Security-Policy', $this->getCSPDirectives());

        // Weitere Sicherheitsheader setzen
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    }

    private function getCSPDirectives(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'", // Für Entwicklung; in Produktion einschränken
            "style-src 'self' 'unsafe-inline'", // Für Entwicklung; in Produktion einschränken
            "img-src 'self' data: blob:",
            "font-src 'self'",
            "connect-src 'self'",
            "media-src 'self'",
            "object-src 'none'",
            "frame-src 'none'",
            "base-uri 'self'",
            "form-action 'self'"
        ]);
    }
}
