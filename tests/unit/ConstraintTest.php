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
    public function testMerge()
    {
        $this->assertSame('1.1.x | 2.2.x', Constraint::merge('1.1.x | 2.2.x', '*'));
        $this->assertSame('1.1.x | 2.2.x', Constraint::merge('*', '1.1.x | 2.2.x'));

        $this->assertSame('2.2.x', Constraint::merge('1.1.x | 2.2.x', '2.2.x'));
        $this->assertSame('2.2.x', Constraint::merge('2.2.x', '1.1.x | 2.2.x'));

        $this->assertSame('1.1.x', Constraint::merge('1.1.x | 2.2.x', '1.1.x'));
        $this->assertSame('1.1.x', Constraint::merge('1.1.x', '1.1.x | 2.2.x'));

        $this->assertSame('1.1.x 2.2.x', Constraint::merge('1.1.x', '2.2.x'));
    }

    public function testFindMax()
    {
        $this->assertSame('3.1.x', Constraint::findMax(['3.1.x', '2.1.x', '1.1.x']));
        $this->assertSame('3.1.x', Constraint::findMax(['1.1.x', '3.1.x', '2.1.x']));
        $this->assertSame('3.1.x', Constraint::findMax(['2.1.x', '1.1.x', '3.1.x']));
        $this->assertSame('3.1.x', Constraint::findMax(['x.x.x', '1.1.x', '3.1.x']));
    }
}
