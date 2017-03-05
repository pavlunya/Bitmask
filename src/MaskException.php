<?php

declare(strict_types=1);

namespace Abibidu\Bit;

final class MaskException extends \Exception
{
    /**
     * @param Mask $mask
     * @param int  $flag
     *
     * @return self
     */
    public static function whenFlagIsPresentInMask(Mask $mask, int $flag): self
    {
        return new self(sprintf('The flag %032b is already present in mask %032b', $flag, $mask->get()));
    }

    /**
     * @param Mask $mask
     * @param int  $flag
     *
     * @return self
     */
    public static function whenFlagIsAbsentInMask(Mask $mask, int $flag): self
    {
        return new self(sprintf('The flag %032b is absent in mask %032b', $flag, $mask->get()));
    }

    /**
     * @param Mask $mask
     *
     * @return self
     */
    public static function whenFlagIsNegative(Mask $mask): self
    {
        return new self(sprintf('The mask %032b must be a positive integer', $mask->get()));
    }
}
