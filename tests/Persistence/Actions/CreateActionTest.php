<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Persistence\Actions;

use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Somnambulist\Components\ApiClient\Persistence\Actions\CreateAction;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\User;

/**
 * @group persister-actions
 */
class CreateActionTest extends TestCase
{
    public function testBuild()
    {
        $action = new CreateAction(User::class);

        $this->assertSame(User::class, $action->getClass());
    }

    public function testBuildStatically()
    {
        $action = CreateAction::new(User::class);

        $this->assertSame(User::class, $action->getClass());
    }

    public function testBuildFullAction()
    {
        $action = CreateAction::new(User::class)
            ->include([
                'name' => 'foo bar', 'email' => 'bar@example.com',
            ])
            ->route('users.create', ['id' => '123'])
        ;

        $this->assertSame(User::class, $action->getClass());
        $this->assertSame(['name' => 'foo bar', 'email' => 'bar@example.com',], $action->getProperties());
        $this->assertSame('users.create', $action->getRoute());
        $this->assertSame(['id' => '123'], $action->getRouteParams());
    }

    public function testIfNoPropertiesRaisesException()
    {
        $action = CreateAction::new(User::class)->route('users.create', ['id' => '123']);

        $this->expectException(InvalidArgumentException::class);

        $action->isValid();
    }

    public function testIfNoRouteParamsRaisesException()
    {
        $action = CreateAction::new(User::class)->include(['name' => 'foo bar', 'email' => 'bar@example.com',]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The following 1 assertions failed:
1) route: The route should not be blank or null
');

        $action->isValid();
    }
}
