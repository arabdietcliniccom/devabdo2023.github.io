<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Fields;

use ValueError;
use function base64_decode;
use function sprintf;
use function strlen;
trait SerializableFieldsTrait
{
    public abstract function __construct(string $bytes);
    public abstract function getBytes() : string;
    public function serialize() : string
    {
        return $this->getBytes();
    }
    public function __serialize() : array
    {
        return ['bytes' => $this->getBytes()];
    }
    /**
     * @return void
     */
    public function unserialize($serialized)
    {
        if (strlen($serialized) === 16) {
            $this->__construct($serialized);
        } else {
            $this->__construct(base64_decode($serialized));
        }
    }
    /**
     * @param mixed[] $data
     * @return void
     */
    public function __unserialize($data)
    {
        if (!isset($data['bytes'])) {
            throw new ValueError(sprintf('%s(): Argument #1 ($data) is invalid', __METHOD__));
        }
        $this->unserialize($data['bytes']);
    }
}
