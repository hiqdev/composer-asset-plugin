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

use Composer\Semver\Comparator;
use Composer\Semver\Constraint\EmptyConstraint;
use Composer\Semver\VersionParser;

/**
 * Constraint helper class.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class Constraint
{
    static private $parser;

    static public function getParser()
    {
        if (static::$parser === null) {
            static::$parser = new VersionParser();
        }

        return static::$parser;
    }

    static public function parse($constraint)
    {
        return static::getParser()->parseConstraints($constraint);
    }

    /**
     * Merges two constraints.
     * Doesn't resolve version conflicts.
     * @param string $a
     * @param string $b
     * @return string
     */
    static public function merge($a, $b)
    {
        $acon = static::parse($a);
        $bcon = static::parse($b);

        if ($acon instanceof EmptyConstraint) {
            return $b;
        } elseif ($bcon instanceof EmptyConstraint) {
            return $a;
        } elseif ($acon->matches($bcon) || $bcon->matches($acon)) {
            return strlen($a)>strlen($b) ? $b : $a;
        } else {
            return $a . ' ' . $b;
        }
    }

    static public function findMax(array $versions)
    {
        $versions = array_unique(array_values($versions));
        if (count($versions)<2) {
            return reset($versions);
        }
        $max = $versions[0];
        for ($i=1; $i<= count($versions); $i++) {
            $cur = $versions[$i];
            if (Comparator::compare($cur, '>', $max)) {
                $max = $cur;
            }
        }

        return trim($max);
    }

    /**
     * Is constraint disjunctive.
     * TODO redo after Semver will have such function.
     * @param string $constraint
     * @return bool
     */
    static public function isDisjunctive($constraint)
    {
        return strpos($constraint, '|') !== false;
    }

}
