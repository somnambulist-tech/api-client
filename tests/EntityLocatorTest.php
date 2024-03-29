<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests;

use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\EntityLocator;
use Somnambulist\Components\ApiClient\Exceptions\EntityNotFoundException;
use Somnambulist\Components\ApiClient\Manager;
use Somnambulist\Components\ApiClient\Tests\Support\Behaviours\UseFactory;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\User;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\UserCollection;

/**
 * @group entity-locator
 */
class EntityLocatorTest extends TestCase
{
    use UseFactory;

    protected function setUp(): void
    {
        $this->factory()->makeManager();
    }

    public function testFind()
    {
        $locator = new EntityLocator(Manager::instance(), User::class);

        $user = $locator->find($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($id, $user->id->toString());
    }

    public function testFindOrFail()
    {
        $locator = new EntityLocator(Manager::instance(), User::class);

        $user = $locator->findOrFail($id = 'c8259b3b-8603-3098-8361-425325078c9a');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($id, $user->id->toString());
    }

    public function testFindOrFailRaisesExceptionWhenNotFound()
    {
        $locator = new EntityLocator(Manager::instance(), User::class);

        $this->expectException(EntityNotFoundException::class);

        $locator->findOrFail('c8259b3b-0000-0000-0000-425325078c9a');
    }

    public function testFindBy()
    {
        $locator = new EntityLocator(Manager::instance(), User::class);

        $users = $locator->findBy([]);

        $this->assertInstanceOf(UserCollection::class, $users);
        $this->assertCount(30, $users);
        $users->each(fn ($user) => $this->assertInstanceOf(User::class, $user));
    }

    public function testFindOneBy()
    {
        $locator = new EntityLocator(Manager::instance(), User::class);

        $user = $locator->findOneBy([]);

        $this->assertInstanceOf(User::class, $user);
    }

    public function testPaginate()
    {
        $locator = new EntityLocator(Manager::instance(), User::class);

        $users = $locator->findByPaginated([]);

        $this->assertInstanceOf(Pagerfanta::class, $users);
    }
}
