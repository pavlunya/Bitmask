<?php

declare(strict_types=1);

namespace Abibidu\Bit;

use Iterator;
use JsonSerializable;

class Mask implements JsonSerializable, Iterator
{
    const EMPTY_MASK = 0;

    const FLAG_1  = 0b00000000000000000000000000000001; // 1
    const FLAG_2  = 0b00000000000000000000000000000010; // 2
    const FLAG_3  = 0b00000000000000000000000000000100; // 4
    const FLAG_4  = 0b00000000000000000000000000001000; // 8
    const FLAG_5  = 0b00000000000000000000000000010000; // 16
    const FLAG_6  = 0b00000000000000000000000000100000; // 32
    const FLAG_7  = 0b00000000000000000000000001000000; // 64
    const FLAG_8  = 0b00000000000000000000000010000000; // 128
    const FLAG_9  = 0b00000000000000000000000100000000; // 256
    const FLAG_10 = 0b00000000000000000000001000000000; // 512
    const FLAG_11 = 0b00000000000000000000010000000000; // 1024
    const FLAG_12 = 0b00000000000000000000100000000000; // 2048
    const FLAG_13 = 0b00000000000000000001000000000000; // 4096
    const FLAG_14 = 0b00000000000000000010000000000000; // 8192
    const FLAG_15 = 0b00000000000000000100000000000000; // 16384
    const FLAG_16 = 0b00000000000000001000000000000000; // 32768
    const FLAG_17 = 0b00000000000000010000000000000000; // 65536
    const FLAG_18 = 0b00000000000000100000000000000000; // 131072
    const FLAG_19 = 0b00000000000001000000000000000000; // 262144
    const FLAG_20 = 0b00000000000010000000000000000000; // 524288
    const FLAG_21 = 0b00000000000100000000000000000000; // 1048576
    const FLAG_22 = 0b00000000001000000000000000000000; // 2097152
    const FLAG_23 = 0b00000000010000000000000000000000; // 4194304
    const FLAG_24 = 0b00000000100000000000000000000000; // 8388608
    const FLAG_25 = 0b00000001000000000000000000000000; // 16777216
    const FLAG_26 = 0b00000010000000000000000000000000; // 33554432
    const FLAG_27 = 0b00000100000000000000000000000000; // 67108864
    const FLAG_28 = 0b00001000000000000000000000000000; // 134217728
    const FLAG_29 = 0b00010000000000000000000000000000; // 268435456
    const FLAG_30 = 0b00100000000000000000000000000000; // 536870912
    const FLAG_31 = 0b01000000000000000000000000000000; // 1073741824
    const FLAG_32 = 0b10000000000000000000000000000000; // 2147483648

    /**
     * @var int[]
     */
    protected $flags = [];

    /**
     * @var int
     */
    protected $iteratorPosition = 0;

    /**
     * @var int
     */
    protected $mask = self::EMPTY_MASK;

    /**
     * @var bool
     */
    protected $strictMode = true;

    /**
     * @param int  $mask
     * @param bool $strictMode
     */
    public function __construct(int $mask = self::EMPTY_MASK, bool $strictMode = true)
    {
        $this->set($mask);
        $this->strictMode = $strictMode;
    }

    /**
     * @param string $mask
     * @param bool   $strictMode
     *
     * @return static
     */
    public static function fromString(string $mask, bool $strictMode = true)
    {
        return new static((int) $mask, $strictMode);
    }

    /**
     * @param int $flag
     *
     * @return bool
     */
    public function has(int $flag): bool
    {
        return (($this->mask & $flag) === $flag);
    }

    /**
     * @param int[] ...$flags
     *
     * @return bool
     */
    public function hasAll(int ...$flags): bool
    {
        $resultingMask = self::EMPTY_MASK;

        foreach ($flags as $flag) {
            $resultingMask |= $flag;
        }

        return (($this->mask & $resultingMask) === $resultingMask);
    }

    /**
     * @param int[] ...$flags
     *
     * @return bool
     */
    public function hasOneOf(int ...$flags): bool
    {
        foreach ($flags as $flag) {
            if ($this->has($flag)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $flag
     *
     * @throws MaskException
     */
    public function add(int $flag)
    {
        if ($this->strictMode && $this->has($flag)) {
            throw MaskException::whenFlagIsPresentInMask($this, $flag);
        }

        $this->set($this->mask | $flag);
    }

    /**
     * @param int $flag
     *
     * @throws MaskException
     */
    public function remove(int $flag)
    {
        if ($this->strictMode && !$this->has($flag)) {
            throw MaskException::whenFlagIsAbsentInMask($this, $flag);
        }

        $this->set($this->mask & ~$flag);
    }

    /**
     * @return int
     */
    public function get(): int
    {
        return $this->mask;
    }

    /**
     * @return int[]
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

    /**
     * @param int $mask
     *
     * @throws MaskException
     */
    public function set(int $mask)
    {
        if ($mask < 0) {
            throw MaskException::whenFlagIsNegative($this);
        }

        $this->mask = $mask;
        $this->flags = $this->extractFlags();
        $this->rewind();
    }

    /**
     * Return the current element
     *
     * @link  http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return isset($this->flags[$this->iteratorPosition]) ? $this->flags[$this->iteratorPosition] : null;
    }

    /**
     * Move forward to next element
     *
     * @link  http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        ++$this->iteratorPosition;
    }

    /**
     * Return the key of the current element
     *
     * @link  http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->iteratorPosition;
    }

    /**
     * Checks if current position is valid
     *
     * @link  http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *        Returns true on success or false on failure.
     */
    public function valid()
    {
        return isset($this->flags[$this->iteratorPosition]);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link  http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->iteratorPosition = 0;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->mask;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): int
    {
        return $this->mask;
    }

    /**
     * @return int[]
     */
    protected function extractFlags(): array
    {
        $flags = [];
        for ($i = 1; $i <= 32; ++$i) {
            $flag = (int) pow(2, $i);

            if ($this->has($flag)) {
                $flags[] = $flag;
            }
        }

        return $flags;
    }
}
