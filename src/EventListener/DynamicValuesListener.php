<?php

declare(strict_types=1);

namespace Rudak\JsInjector\EventListener;

use Psr\Log\LoggerInterface;
use Rudak\JsInjector\Attribute\JsInjectorDynamic;
use Rudak\JsInjector\Harvester\DynamicValuesHarvester;
use Rudak\JsInjector\Harvester\HarvesterInterface;
use Rudak\JsInjector\Helper\ValuesChecker;
use Rudak\JsInjector\Validator\VariableNameValidator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Injects the dynamic providers' values into the HTML response of the pages
 * that opt in via the #[JsInjectorDynamic] attribute.
 *
 * The values are recomputed on every request and embedded in an inert
 * <script type="application/json"> tag that the client reads with
 * "injectFromDom('#js-injector-dynamic')".
 */
final class DynamicValuesListener implements EventSubscriberInterface
{
    private const JSON_OPTIONS = JSON_HEX_TAG | JSON_THROW_ON_ERROR;

    public function __construct(
        private readonly DynamicValuesHarvester $harvester,
        private readonly string $scriptTagId,
        private readonly bool $enabled,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onKernelResponse'];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$this->enabled || !$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();

        if (!str_contains((string) $response->headers->get('Content-Type', ''), 'text/html')) {
            return;
        }

        $attribute = $this->findAttribute($event->getRequest());

        if (null === $attribute) {
            return;
        }

        $values = $this->collectValues($attribute);

        if ([] === $values) {
            return;
        }

        $tag = sprintf(
            '<script type="application/json" id="%s">%s</script>',
            $this->scriptTagId,
            json_encode($values, self::JSON_OPTIONS)
        );

        $content = (string) $response->getContent();

        if ('' === $content) {
            return;
        }

        if (str_contains($content, '</body>')) {
            $content = str_replace('</body>', $tag."\n</body>", $content);
        } else {
            $content .= "\n".$tag;
        }

        $response->setContent($content);
    }

    private function findAttribute(Request $request): ?JsInjectorDynamic
    {
        [$class, $method] = $this->resolveController($request);

        if (null === $class || !class_exists($class)) {
            return null;
        }

        $reflection = new \ReflectionClass($class);

        if (null !== $method && $reflection->hasMethod($method)) {
            $methodAttributes = $reflection->getMethod($method)->getAttributes(JsInjectorDynamic::class);

            if ([] !== $methodAttributes) {
                return $methodAttributes[0]->newInstance();
            }
        }

        $classAttributes = $reflection->getAttributes(JsInjectorDynamic::class);

        return [] !== $classAttributes ? $classAttributes[0]->newInstance() : null;
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function resolveController(Request $request): array
    {
        $controller = $request->attributes->get('_controller');

        if (is_array($controller)) {
            $class = is_object($controller[0]) ? get_class($controller[0]) : (string) $controller[0];
            $method = isset($controller[1]) ? (string) $controller[1] : null;

            return [$class, $method];
        }

        if (is_string($controller) && str_contains($controller, '::')) {
            [$class, $method] = explode('::', $controller, 2);

            return [$class, $method];
        }

        if (is_string($controller) && '' !== $controller) {
            return [$controller, null];
        }

        return [null, null];
    }

    /**
     * @return array<string, mixed>
     */
    private function collectValues(JsInjectorDynamic $attribute): array
    {
        $requested = null === $attribute->providers ? null : array_map(strval(...), (array) $attribute->providers);
        $values = [];

        foreach ($this->harvester->getValuesProviders() as $channel => $provider) {
            if (null !== $requested && !in_array((string) $channel, $requested, true)) {
                continue;
            }

            if (!$provider instanceof HarvesterInterface) {
                $this->logger?->warning(
                    sprintf('Service "%s" does not implement HarvesterInterface', get_debug_type($provider))
                );

                continue;
            }

            $providerValues = $provider->getValues();

            if (!ValuesChecker::isValid($providerValues)) {
                $this->logger?->warning(
                    sprintf(
                        '"%s" is not a correct variable name in %s',
                        VariableNameValidator::findInvalidKey($providerValues),
                        get_class($provider)
                    )
                );

                continue;
            }

            $values = array_merge($providerValues, $values);
        }

        return $values;
    }
}
