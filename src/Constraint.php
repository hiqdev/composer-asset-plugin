<?php

/*
 * Composer plugin for bower/npm assets
 *
 * @link      https://github.com/hiqdev/composer-asset-plugin
 * @package   composer-asset-plugin
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2015-2016, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\composerassetplugin;

/**
 * Constraint helper class.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class Constraint
{
    /**
     * Merges two constraints.
     * Doesn't resolve version conflicts.
     * @param $a
     * @param $b
     * @return string
     */
    static public function merge($a, $b)
    {
        $a = trim($a);
        $b = trim($b);
        var_dump("a:$a b:$b");

        if ($a === $b || static::isWeaker($b, $a)) {
            return $a;
        } elseif (static::isWeaker($a, $b)) {
            return $b;
        } else {
            return $a . ' ' . $b;
        }
    }

    /**
     * Check if $a is weaker condition then $b, like:
     * - a="*"         b="2.2"
     * - a="2.2 | 3.3" b="2.2"
     * - a="1.1 | 2.2" b="2.2"
     * Possible optimization.
     * // TODO Rename and implement.
     * @param string $a
     * @param string $b
     * @return boolean
     */
    static public function isWeaker($a, $b)
    {
        return static::isEmpty($a) || static::startsWith($a, $b . ' |') | static::endsWith($a, '| ' . $b);
    }

    static public function startsWith($haystack, $needle) {
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    static public function endsWith($haystack, $needle) {
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    /**
     * Checks whether the $version represents any possible version.
     *
     * @param string $version
     * @return boolean
     */
    static public function isEmpty($version)
    {
        return $version === '' || $version === '*' || $version === '>=0.0.0';
    }

    static public function findMax(array $versions)
    {
        $versions = array_unique(array_values($versions));
        if (count($versions)<2) {
            return reset($versions);
        }
        $max = $versions[0];
        $maxNum = static::toNum($max);
        for ($i=1; $i<= count($versions); $i++) {
            $cur = $versions[$i];
            $curNum = static::toNum($cur);
            if ($curNum > $maxNum) {
                $max = $cur;
                $maxNum = $curNum;
            }
        }

        return $max;
    }

    static public function toNum($version)
    {
        $version = preg_replace('/[^0-9\.]/', '', $version);
        $nums = explode('.', $version);
        $n1 = isset($nums[0]) ? $nums[0] : 0;
        $n2 = isset($nums[1]) ? $nums[1] : 0;
        $n3 = isset($nums[2]) ? $nums[2] : 0;

        return (($n1*1000) + $n2)*1000 + $n3;
    }

}
