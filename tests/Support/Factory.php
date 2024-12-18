<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Support;

use IlluminateAgnostic\Str\Support\Str;
use RuntimeException;
use Somnambulist\Components\ApiClient\Client\ApiRoute;
use Somnambulist\Components\ApiClient\Client\ApiRouter;
use Somnambulist\Components\ApiClient\Client\Connection;
use Somnambulist\Components\ApiClient\Manager;
use Somnambulist\Components\ApiClient\Tests\Support\Decorators\AssertableConnectionDecorator;
use Somnambulist\Components\AttributeModel\TypeCasters;
use Somnambulist\Components\Models\Types\Geography\Country;
use Somnambulist\Components\Models\Types\Identity\EmailAddress;
use Somnambulist\Components\Models\Types\Identity\Uuid;
use Somnambulist\Components\Models\Types\PhoneNumber;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Routing\RouteCollection;

use Symfony\Component\String\UnicodeString;
use function file_get_contents;
use function is_callable;
use function sprintf;
use function Symfony\Component\String\u;

class Factory
{
    public function makeManager(?callable $decoratorFactory = null): Manager
    {
        $host = 'http://api.example.dev/users/v1';

        $callback = function (string $method, string $url, array $options = []) {
            $url = u($url);

            switch (true) {
                case $url->containsAny('/v1/users'):    return $this->userRoutes($method, $url, $options);
                case $url->containsAny('/v1/accounts'): return $this->accountRoutes($method, $url, $options);

                case $url->containsAny('/v1/groups/1?include=permissions'):
                    return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/group_view_with_permissions.json'));

                case $url->containsAny('/v1/foobar'):
                    return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_foobar.json'));
            }

            throw new RuntimeException(sprintf('No response configured for request: %s %s', $method, $url));
        };

        $client = new MockHttpClient($callback);
        $router = new ApiRouter($host, new RouteCollection());
        $router->routes()->add('users.list', new ApiRoute('users'));
        $router->routes()->add('users.view', new ApiRoute('users/{id}'));
        $router->routes()->add('groups.list', new ApiRoute('groups'));
        $router->routes()->add('groups.view', new ApiRoute('groups/{id}'));
        $router->routes()->add('accounts.list', new ApiRoute('accounts'));
        $router->routes()->add('accounts.view', new ApiRoute('accounts/{id}'));
        $router->routes()->add('inbox.list', new ApiRoute('{accountId}/user/{userId}/inbox'));
        $router->routes()->add('inbox.view', new ApiRoute('{accountId}/user/{userId}/inbox/{itemid}'));

        $connection = new AssertableConnectionDecorator(new Connection($client, $router, new EventDispatcher()));

        if (is_callable($decoratorFactory)) {
            $connection = $decoratorFactory($connection);
        }

        return new Manager(
            [
                'default' => $connection,
            ],
            [
                new TypeCasters\DateTimeCaster(),
                new TypeCasters\SimpleValueObjectCaster(Uuid::class, ['uuid']),
                new TypeCasters\SimpleValueObjectCaster(EmailAddress::class, ['email']),
                new TypeCasters\SimpleValueObjectCaster(PhoneNumber::class, ['phone']),
                new TypeCasters\EnumerableKeyCaster(Country::class, ['country']),
            ]
        );
    }

    private function accountRoutes(string $method, UnicodeString $url, array $options = []): MockResponse
    {
        switch (true) {
            case $url->containsAny('/v1/accounts/1228ec03-1a58-4e51-8cea-cb787104aa3d?include=related_accounts'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/account_view_1228ec03_with_related_accounts.json'));

            case $url->containsAny('/v1/accounts/1228ec03-1a58-4e51-8cea-cb787104aa3d?include=related'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/account_view_1228ec03_with_related.json'));

            case $url->containsAny('/v1/accounts/1228ec03-1a58-4e51-8cea-cb787104aa3d'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/account_view_1228ec03.json'));

            case $url->containsAny('/v1/accounts/8c4ba4ea-c4f6-43ad-b97c-cb84f4314fa8'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/account_view_8c4ba4ea.json'));
        }

        throw new RuntimeException(sprintf('No response configured for request: %s %s', $method, $url));
    }

    private function userRoutes(string $method, UnicodeString $url, array $options = []): MockResponse
    {
        switch (true) {
            case $url->containsAny('/v1/users/468185d5-4238-44bb-ae34-44909e35e4fe?include=address3'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_with_no_address.json'));

            case $url->containsAny('/v1/users/1e335331-ee15-4871-a419-c6778e190a54?include=account.related.related'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_with_account_relations.json'));

            case $url->containsAny('/v1/users/1e335331-ee15-4871-a419-c6778e190a54?include=contacts'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_with_contacts.json'));

            case $url->containsAny('/v1/users/1e335331-ee15-4871-a419-c6778e190a54'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_1e335331.json'));

            case $url->containsAny('/v1/users/c8259b3b-8603-3098-8361-425325078c9a?include=addresses,contacts'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_with_addresses_contacts.json'));

            case $url->containsAny('/v1/users/c8259b3b-8603-3098-8361-425325078c9a?include=addresses'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_with_addresses.json'));

            case $url->containsAny('/v1/users/c8259b3b-8603-3098-8361-425325078c9a?include=address,contacts'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_with_address_contacts.json'));

            case $url->containsAny('/v1/users/c8259b3b-8603-3098-8361-425325078c9a?include=address'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_with_address.json'));

            case $url->containsAny('/v1/users/c8259b3b-8603-3098-8361-425325078c9a?include=groups,groups.permissions'):
            case $url->containsAny('/v1/users/c8259b3b-8603-3098-8361-425325078c9a?include=groups.permissions'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_with_groups_permissions.json'));

            case $url->containsAny('/v1/users/c8259b3b-8603-3098-8361-425325078c9a?include=groups'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_with_groups.json'));

            case $url->containsAny('/v1/users/c8259b3b-8603-3098-8361-425325078c9a'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_view_c8259b3b.json'));

            case $url->containsAny('/v1/users/c8259b3b-0000-0000-0000-425325078c9a'):
                return new MockResponse('{"message":"Record not found"}', ['http_code' => 404]);

            case $url->containsAny('/v1/users?email=noresults@example.com'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_list_no_result.json'));

            case $url->containsAny('/v1/users?email=hodkiewicz.anastasia@feest.org'):
            case $url->containsAny('/v1/users?id=c8259b3b-8603-3098-8361-425325078c9a&per_page=10&page=1'):
            case $url->containsAny('/v1/users?id=c8259b3b-8603-3098-8361-425325078c9a&order=-name,created_at'):
            case $url->containsAny('/v1/users?id=c8259b3b-8603-3098-8361-425325078c9a&include=addresses,contacts'):
            case $url->containsAny('/v1/users?id=c8259b3b-8603-3098-8361-425325078c9a'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_list_single.json'));

            case $url->containsAny('/v1/users?include=addresses,contacts'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_list_with_addresses_contacts.json'));

            case $url->containsAny('/v1/users'):
                return new MockResponse(file_get_contents(__DIR__ . '/Stubs/json/user_list.json'));
        }

        throw new RuntimeException(sprintf('No response configured for request: %s %s', $method, $url));
    }
}
