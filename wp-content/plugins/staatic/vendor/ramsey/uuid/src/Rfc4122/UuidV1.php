<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Rfc4122;

use DateTimeImmutable;
use DateTimeInterface;
use Staatic\Vendor\Ramsey\Uuid\Codec\CodecInterface;
use Staatic\Vendor\Ramsey\Uuid\Converter\NumberConverterInterface;
use Staatic\Vendor\Ramsey\Uuid\Converter\TimeConverterInterface;
use Staatic\Vendor\Ramsey\Uuid\Exception\DateTimeException;
use Staatic\Vendor\Ramsey\Uuid\Exception\InvalidArgumentException;
use Staatic\Vendor\Ramsey\Uuid\Rfc4122\FieldsInterface as Rfc4122FieldsInterface;
use Staatic\Vendor\Ramsey\Uuid\Uuid;
use Throwable;
use function str_pad;
use const STR_PAD_LEFT;
final class UuidV1 extends Uuid implements UuidInterface
{
    public function __construct(Rfc4122FieldsInterface $fields, NumberConverterInterface $numberConverter, CodecInterface $codec, TimeConverterInterface $timeConverter)
    {
        if ($fields->getVersion() !== Uuid::UUID_TYPE_TIME) {
            throw new InvalidArgumentException('Fields used to create a UuidV1 must represent a ' . 'version 1 (time-based) UUID');
        }
        parent::__construct($fields, $numberConverter, $codec, $timeConverter);
    }
    public function getDateTime() : DateTimeInterface
    {
        $time = $this->timeConverter->convertTime($this->fields->getTimestamp());
        try {
            return new DateTimeImmutable('@' . $time->getSeconds()->toString() . '.' . str_pad($time->getMicroseconds()->toString(), 6, '0', STR_PAD_LEFT));
        } catch (Throwable $e) {
            throw new DateTimeException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
