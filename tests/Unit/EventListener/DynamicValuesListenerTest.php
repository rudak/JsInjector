<?php

declare(strict_types=1);

namespace Rudak\JsInjector\Tests\Unit\EventListener;

use PHPUnit\Framework\TestCase;
use Rudak\JsInjector\Attribute\JsInjectorDynamic;
use Rudak\JsInjector\EventListener\DynamicValuesListener;
use Rudak\JsInjector\Harvester\DynamicValuesHarvester;
use Rudak\JsInjector\Harvester\HarvesterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class DynamicValuesListenerTest extends TestCase
{
    /**
     * @param array<mixed> $providers
     */
    private function createEvent(
        array $providers,
        string $controller = AllDynamicController::class,
        string $contentType = 'text/html',
        int $requestType = HttpKernelInterface::MAIN_REQUEST,
    ): ResponseEvent {
        $kernel = $this->createMock(KernelInterface::class);
        $request = new Request();
        $request->attributes->set('_controller', $controller);

        return new ResponseEvent(
            $kernel,
            $request,
            $requestType,
            new Response('<html><body>Page</body></html>', 200, ['Content-Type' => $contentType])
        );
    }

    /**
     * @param array<mixed> $providers
     */
    private function createListener(
        array $providers,
        string $scriptTagId = 'js-injector-dynamic',
        bool $enabled = true,
    ): DynamicValuesListener {
        return new DynamicValuesListener(new DynamicValuesHarvester($providers), $scriptTagId, $enabled);
    }

    public function testInjectsJsonTagBeforeClosingBody(): void
    {
        $providers = [
            'app' => new class implements HarvesterInterface {
                public function getValues(): array
                {
                    return ['API_URL' => 'https://api.example.com', 'DEBUG' => true];
                }
            },
        ];

        $event = $this->createEvent($providers);
        $this->createListener($providers)->onKernelResponse($event);

        $content = $event->getResponse()->getContent();
        $this->assertStringContainsString(
            '<script type="application/json" id="js-injector-dynamic">{"API_URL":"https:\/\/api.example.com","DEBUG":true}</script>',
            $content
        );
        $this->assertMatchesRegularExpression('/<\/script>\s*<\/body>/', $content);
    }

    public function testUsesConfiguredScriptTagId(): void
    {
        $providers = [
            'app' => new class implements HarvesterInterface {
                public function getValues(): array
                {
                    return ['FOO' => 1];
                }
            },
        ];

        $event = $this->createEvent($providers);
        $this->createListener($providers, 'app-dynamic')->onKernelResponse($event);

        $this->assertStringContainsString('id="app-dynamic"', $event->getResponse()->getContent());
    }

    public function testDoesNothingWhenDisabled(): void
    {
        $providers = [
            'app' => new class implements HarvesterInterface {
                public function getValues(): array
                {
                    return ['FOO' => 1];
                }
            },
        ];

        $event = $this->createEvent($providers);
        $this->createListener($providers, 'js-injector-dynamic', false)->onKernelResponse($event);

        $this->assertSame('<html><body>Page</body></html>', $event->getResponse()->getContent());
    }

    public function testDoesNothingWithoutAttribute(): void
    {
        $providers = [
            'app' => new class implements HarvesterInterface {
                public function getValues(): array
                {
                    return ['FOO' => 1];
                }
            },
        ];

        $event = $this->createEvent($providers, PlainController::class);
        $this->createListener($providers)->onKernelResponse($event);

        $this->assertSame('<html><body>Page</body></html>', $event->getResponse()->getContent());
    }

    public function testDoesNothingWithoutDynamicProviders(): void
    {
        $event = $this->createEvent([]);
        $this->createListener([])->onKernelResponse($event);

        $this->assertSame('<html><body>Page</body></html>', $event->getResponse()->getContent());
    }

    public function testSkipsNonHtmlResponses(): void
    {
        $providers = [
            'app' => new class implements HarvesterInterface {
                public function getValues(): array
                {
                    return ['FOO' => 1];
                }
            },
        ];

        $event = $this->createEvent($providers, AllDynamicController::class, 'application/json');
        $this->createListener($providers)->onKernelResponse($event);

        $this->assertStringNotContainsString('js-injector-dynamic', $event->getResponse()->getContent());
    }

    public function testSkipsSubRequests(): void
    {
        $providers = [
            'app' => new class implements HarvesterInterface {
                public function getValues(): array
                {
                    return ['FOO' => 1];
                }
            },
        ];

        $event = $this->createEvent($providers, AllDynamicController::class, 'text/html', HttpKernelInterface::SUB_REQUEST);
        $this->createListener($providers)->onKernelResponse($event);

        $this->assertStringNotContainsString('js-injector-dynamic', $event->getResponse()->getContent());
    }

    public function testAppendsTagWhenNoClosingBody(): void
    {
        $providers = [
            'app' => new class implements HarvesterInterface {
                public function getValues(): array
                {
                    return ['FOO' => 1];
                }
            },
        ];

        $kernel = $this->createMock(KernelInterface::class);
        $request = new Request();
        $request->attributes->set('_controller', AllDynamicController::class);
        $event = new ResponseEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new Response('plain text', 200, ['Content-Type' => 'text/html'])
        );

        $this->createListener($providers)->onKernelResponse($event);

        $this->assertStringContainsString("\n<script type=\"application/json\"", $event->getResponse()->getContent());
    }

    public function testFirstProviderWinsOnDuplicateKey(): void
    {
        $first = new class implements HarvesterInterface {
            public function getValues(): array
            {
                return ['FOO' => 'first'];
            }
        };

        $second = new class implements HarvesterInterface {
            public function getValues(): array
            {
                return ['FOO' => 'second'];
            }
        };

        $providers = ['first' => $first, 'second' => $second];
        $event = $this->createEvent($providers);
        $this->createListener($providers)->onKernelResponse($event);

        $this->assertStringContainsString('"FOO":"first"', $event->getResponse()->getContent());
    }

    public function testEscapesScriptClosingTag(): void
    {
        $providers = [
            'app' => new class implements HarvesterInterface {
                public function getValues(): array
                {
                    return ['MESSAGE' => '</script>'];
                }
            },
        ];

        $event = $this->createEvent($providers);
        $this->createListener($providers)->onKernelResponse($event);

        $content = $event->getResponse()->getContent();
        $this->assertStringNotContainsString('{"MESSAGE":"</script>"}', $content);
        $this->assertStringContainsString('\u003C\/script\u003E', $content);
    }

    public function testSkipsProviderWithInvalidKeys(): void
    {
        $valid = new class implements HarvesterInterface {
            public function getValues(): array
            {
                return ['VALID' => 1];
            }
        };

        $invalid = new class implements HarvesterInterface {
            public function getValues(): array
            {
                return ['123invalid' => 2];
            }
        };

        $providers = ['valid' => $valid, 'invalid' => $invalid];
        $event = $this->createEvent($providers);
        $this->createListener($providers)->onKernelResponse($event);

        $content = $event->getResponse()->getContent();
        $this->assertStringContainsString('"VALID":1', $content);
        $this->assertStringNotContainsString('123invalid', $content);
    }

    public function testFiltersProvidersByChannel(): void
    {
        $userContext = new class implements HarvesterInterface {
            public function getValues(): array
            {
                return ['USER_ID' => 42];
            }
        };

        $featureFlags = new class implements HarvesterInterface {
            public function getValues(): array
            {
                return ['CHAT_ENABLED' => true];
            }
        };

        $providers = ['user_context' => $userContext, 'feature_flags' => $featureFlags];
        $event = $this->createEvent($providers, ChannelControllers::class.'::account');
        $this->createListener($providers)->onKernelResponse($event);

        $content = $event->getResponse()->getContent();
        $this->assertStringContainsString('"USER_ID":42', $content);
        $this->assertStringNotContainsString('CHAT_ENABLED', $content);
    }

    public function testCollectsMultipleChannels(): void
    {
        $userContext = new class implements HarvesterInterface {
            public function getValues(): array
            {
                return ['USER_ID' => 42];
            }
        };

        $featureFlags = new class implements HarvesterInterface {
            public function getValues(): array
            {
                return ['CHAT_ENABLED' => true];
            }
        };

        $providers = ['user_context' => $userContext, 'feature_flags' => $featureFlags];
        $event = $this->createEvent($providers, ChannelControllers::class.'::dashboard');
        $this->createListener($providers)->onKernelResponse($event);

        $content = $event->getResponse()->getContent();
        $this->assertStringContainsString('"USER_ID":42', $content);
        $this->assertStringContainsString('"CHAT_ENABLED":true', $content);
    }

    public function testCollectsAllProvidersWithoutChannelFilter(): void
    {
        $userContext = new class implements HarvesterInterface {
            public function getValues(): array
            {
                return ['USER_ID' => 42];
            }
        };

        $featureFlags = new class implements HarvesterInterface {
            public function getValues(): array
            {
                return ['CHAT_ENABLED' => true];
            }
        };

        $providers = ['user_context' => $userContext, 'feature_flags' => $featureFlags];
        $event = $this->createEvent($providers);
        $this->createListener($providers)->onKernelResponse($event);

        $content = $event->getResponse()->getContent();
        $this->assertStringContainsString('"USER_ID":42', $content);
        $this->assertStringContainsString('"CHAT_ENABLED":true', $content);
    }

    public function testUsesClassLevelAttribute(): void
    {
        $featureFlags = new class implements HarvesterInterface {
            public function getValues(): array
            {
                return ['CHAT_ENABLED' => true];
            }
        };

        $userContext = new class implements HarvesterInterface {
            public function getValues(): array
            {
                return ['USER_ID' => 42];
            }
        };

        $providers = ['user_context' => $userContext, 'feature_flags' => $featureFlags];
        $event = $this->createEvent($providers, ClassChannelController::class.'::index');
        $this->createListener($providers)->onKernelResponse($event);

        $content = $event->getResponse()->getContent();
        $this->assertStringContainsString('"CHAT_ENABLED":true', $content);
        $this->assertStringNotContainsString('USER_ID', $content);
    }

    public function testMethodAttributeOverridesClassAttribute(): void
    {
        $userContext = new class implements HarvesterInterface {
            public function getValues(): array
            {
                return ['USER_ID' => 42];
            }
        };

        $featureFlags = new class implements HarvesterInterface {
            public function getValues(): array
            {
                return ['CHAT_ENABLED' => true];
            }
        };

        $providers = ['user_context' => $userContext, 'feature_flags' => $featureFlags];
        $event = $this->createEvent($providers, OverrideController::class.'::action');
        $this->createListener($providers)->onKernelResponse($event);

        $content = $event->getResponse()->getContent();
        $this->assertStringContainsString('"USER_ID":42', $content);
        $this->assertStringNotContainsString('CHAT_ENABLED', $content);
    }
}

#[JsInjectorDynamic]
final class AllDynamicController
{
    public function __invoke(): void
    {
    }
}

final class ChannelControllers
{
    #[JsInjectorDynamic('user_context')]
    public function account(): void
    {
    }

    #[JsInjectorDynamic(['user_context', 'feature_flags'])]
    public function dashboard(): void
    {
    }

    public function home(): void
    {
    }
}

#[JsInjectorDynamic('feature_flags')]
final class ClassChannelController
{
    public function index(): void
    {
    }
}

#[JsInjectorDynamic('feature_flags')]
final class OverrideController
{
    #[JsInjectorDynamic('user_context')]
    public function action(): void
    {
    }
}

final class PlainController
{
    public function index(): void
    {
    }
}
