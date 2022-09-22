<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Rfc4122;

use Staatic\Vendor\Ramsey\Uuid\Builder\UuidBuilderInterface;
use Staatic\Vendor\Ramsey\Uuid\Codec\CodecInterface;
use Staatic\Vendor\Ramsey\Uuid\Converter\NumberConverterInterface;
use Staatic\Vendor\Ramsey\Uuid\Converter\TimeConverterInterface;
use Staatic\Vendor\Ramsey\Uuid\Exception\UnableToBuildUuidException;
use Staatic\Vendor\Ramsey\Uuid\Exception\UnsupportedOperationException;
use Staatic\Vendor\Ramsey\Uuid\Nonstandard\UuidV6;
use Staatic\Vendor\Ramsey\Uuid\Rfc4122\UuidInterface as Rfc4122UuidInterface;
use Staatic\Vendor\Ramsey\Uuid\UuidInterface;
use Throwable;
class UuidBuilder implements UuidBuilderInterface
{
    private $numberConverter;
    private $timeConverter;
    public function __construct(NumberConverterInterface $numberConverter, TimeConverterInterface $timeConverter)
    {
        $this->numberConverter = $numberConverter;
        $this->timeConverter = $timeConverter;
    }
    /**
     * @param CodecInterface $codec
     * @param string $bytes
     */
    public function build($codec, $bytes) : UuidInterface
    {
        try {
            $fields = $this->buildFields($bytes);
            if ($fields->isNil()) {
                return new NilUuid($fields, $this->numberConverter, $codec, $this->timeConverter);
            }
            switch ($fields->getVersion()) {
                case 1:
                    return new UuidV1($fields, $this->numberConverter, $codec, $this->timeConverter);
                case 2:
                    return new UuidV2($fields, $this->numberConverter, $codec, $this->timeConverter);
                case 3:
                    return new UuidV3($fields, $this->numberConverter, $codec, $this->timeConverter);
                case 4:
                    return new UuidV4($fields, $this->numberConverter, $codec, $this->timeConverter);
                case 5:
                    return new UuidV5($fields, $this->numberConverter, $codec, $this->timeConverter);
                case 6:
                    return new UuidV6($fields, $this->numberConverter, $codec, $this->timeConverter);
            }
            throw new UnsupportedOperationException('The UUID version in the given fields is not supported ' . 'by this UUID builder');
        } catch (Throwable $e) {
            throw new UnableToBuildUuidException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
    /**
     * @param string $bytes
     */
    protected function buildFields($bytes) : FieldsInterface
    {
        return new Fields($bytes);
    }
}
