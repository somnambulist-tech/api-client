<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Client\Query\Expression;

use Somnambulist\Components\ApiClient\Client\Query\Expression\Expression;
use PHPUnit\Framework\TestCase;

/**
 * Class ExpressionTest
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Client\Query\Expression
 * @subpackage Somnambulist\Components\ApiClient\Tests\Client\Query\Expression\ExpressionTest
 *
 * @group client
 * @group client-query
 * @group client-query-expression
 */
class ExpressionTest extends TestCase
{

    public function testCreate()
    {
        $expr = new Expression('this', '=', 'that');

        $this->assertEquals('this', $expr->getField());
        $this->assertEquals('=', $expr->getOperator());
        $this->assertEquals('that', $expr->getValue());
    }

    public function testCastToString()
    {
        $expr = new Expression('this', 'neq', 'that');

        $this->assertEquals('neq:that', (string)$expr);

        $expr = new Expression('this', 'eq', 'that');

        $this->assertEquals('that', (string)$expr);
    }

    public function testCastToStringWithArrayValues()
    {
        $expr = new Expression('this', 'neq', [1, 2, 3, 4, 4567]);

        $this->assertEquals('neq:1,2,3,4,4567', (string)$expr);
    }
}
