<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client;

use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Somnambulist\Components\ApiClient\Client\EventListeners\InjectHeadersFromRequestStack;
use Somnambulist\Components\ApiClient\Client\Events\PreRequestEvent;
use Somnambulist\Components\ApiClient\Tests\Support\Behaviours\UseFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @group client
 * @group client-entity-locator
 */
class ApiClientHeaderExtractionTest extends TestCase
{
    use UseFactory;

    private ?ConnectionInterface $client = null;

    protected function setUp(): void
    {
        $injector = new InjectHeadersFromRequestStack($stack = new RequestStack(), [
            'X-Request-Id', 'X-Forwarded-For',
        ]);

        $manager = $this->factory()->makeManager();
        $this->client = $manager->connection()->for('default');
        $this->client->dispatcher()->addListener(PreRequestEvent::class, [$injector, 'onPreRequest']);

        $stack->push(new Request([], [], [], [], [], [
            'HTTP_X-Request-Id'    => 'foo-bar-bob',
            'HTTP_X-Forwarded-For' => '192.168.1.1',
            'CONTENT_TYPE'         => 'text/plain',
        ]));
    }

    public function testCanInjectHeadersIntoClient()
    {
        $response = $this->client->get('users.list');

        $headers = $response->getRequestOptions()['headers'];

        $this->assertContains('X-Request-Id: foo-bar-bob', $headers);
        $this->assertContains('X-Forwarded-For: 192.168.1.1', $headers);
    }
}
