<?php

/*
 * Composer plugin for bower/npm assets
 *
 * @link      https://github.com/hiqdev/composer-asset-plugin
 * @package   composer-asset-plugin
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2015-2016, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\composerassetplugin\tests\unit;

use hiqdev\composerassetplugin\Constraint;

/**
 * Class ConstraintTest.
 */
class ConstraintTest extends \PHPUnit_Framework_TestCase
{
    public function testToNum()
    {
        $this->assertSame(1001001, Constraint::toNum('1.1.1'));
        $this->assertSame(2002000, Constraint::toNum('2.2.0'));
        $this->assertSame(3003000, Constraint::toNum('3.3.x'));
    }

    public function testFindMax()
    {
        $this->assertSame('3.1.x', Constraint::findMax(['3.1.x', '2.1.x', '1.1.x']));
        $this->assertSame('3.1.x', Constraint::findMax(['1.1.x', '3.1.x', '2.1.x']));
        $this->assertSame('3.1.x', Constraint::findMax(['2.1.x', '1.1.x', '3.1.x']));
    }

    public function testIsEmpty()
    {
        $this->assertSame(true, Constraint::isEmpty('*'));
        $this->assertSame(true, Constraint::isEmpty(''));
        $this->assertSame(true, Constraint::isEmpty('>=0.0.0'));
        $this->assertSame(false, Constraint::isEmpty('3.2.1'));
    }
}
