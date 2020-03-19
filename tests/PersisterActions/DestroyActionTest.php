<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests\PersisterActions;

use Assert\InvalidArgumentException;
use Somnambulist\ApiClient\PersisterActions\CreateAction;
use PHPUnit\Framework\TestCase;
use Somnambulist\ApiClient\PersisterActions\DestroyAction;
use Somnambulist\ApiClient\Tests\Stubs\Entities\User;

/**
 * Class DestroyActionTest
 *
 * @package    Somnambulist\ApiClient\Tests\PersisterActions
 * @subpackage Somnambulist\ApiClient\Tests\PersisterActions\DestroyActionTest
 *
 * @group persister-actions
 */
class DestroyActionTest extends TestCase
{

    public function testBuild()
    {
        $action = new DestroyAction(User::class);

        $this->assertSame(User::class, $action->getClass());
    }

    public function testBuildStatically()
    {
        $action = DestroyAction::destroy(User::class);

        $this->assertSame(User::class, $action->getClass());
    }

    public function testBuildFullAction()
    {
        $action = DestroyAction::destroy(User::class)
            ->with([
                'name' => 'foo bar', 'email' => 'bar@example.com',
            ])
            ->route('users.destroy', ['id' => '123'])
        ;

        $this->assertSame(User::class, $action->getClass());
        $this->assertSame(['name' => 'foo bar', 'email' => 'bar@example.com',], $action->getProperties());
        $this->assertSame('users.destroy', $action->getRoute());
        $this->assertSame(['id' => '123'], $action->getRouteParams());
    }

    public function testIfNoRouteParamsRaisesException()
    {
        $action = DestroyAction::destroy(User::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The following 2 assertions failed:
1) route: Value "" is blank, but was expected to contain a value.
2) params: Value "<ARRAY>" is empty, but non empty value was expected.
');

        $action->isValid();
    }
}