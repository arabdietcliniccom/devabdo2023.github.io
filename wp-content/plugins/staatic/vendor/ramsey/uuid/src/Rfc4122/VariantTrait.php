<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Rfc4122;

use Staatic\Vendor\Ramsey\Uuid\Exception\InvalidBytesException;
use Staatic\Vendor\Ramsey\Uuid\Uuid;
use function decbin;
use function str_pad;
use function strlen;
use function strpos;
use function substr;
use function unpack;
use const STR_PAD_LEFT;
trait VariantTrait
{
    public abstract function getBytes() : string;
    public function getVariant() : int
    {
        if (strlen($this->getBytes()) !== 16) {
            throw new InvalidBytesException('Invalid number of bytes');
        }
        $parts = unpack('n*', $this->getBytes());
        $binary = str_pad(decbin((int) $parts[5]), 16, '0', STR_PAD_LEFT);
        $msb = substr($binary, 0, 3);
        if ($msb === '111') {
            $variant = Uuid::RESERVED_FUTURE;
        } elseif ($msb === '110') {
            $variant = Uuid::RESERVED_MICROSOFT;
        } elseif (strpos($msb, '10') === 0) {
            $variant = Uuid::RFC_4122;
        } else {
            $variant = Uuid::RESERVED_NCS;
        }
        return $variant;
    }
}
