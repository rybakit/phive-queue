<?php

/*
 * This file is part of the Phive Queue package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phive\Queue\Tests\Queue;

class Types
{
    const TYPE_NULL = 1;
    const TYPE_BOOL = 2;
    const TYPE_INT = 3;
    const TYPE_FLOAT = 4;
    const TYPE_STRING = 5;
    const TYPE_BINARY_STRING = 6;
    const TYPE_ARRAY = 7;
    const TYPE_OBJECT = 8;

    public static function getAll()
    {
        return [
            self::TYPE_NULL => null,
            self::TYPE_BOOL => true,
            self::TYPE_INT => 42,
            self::TYPE_FLOAT => 1.5,
            self::TYPE_STRING => 'string',
            self::TYPE_BINARY_STRING => "\x04\x00\xa0\x00\x00",
            self::TYPE_ARRAY => ['a', 'r', 'r', 'a', 'y'],
            self::TYPE_OBJECT => new self(),
        ];
    }
}
