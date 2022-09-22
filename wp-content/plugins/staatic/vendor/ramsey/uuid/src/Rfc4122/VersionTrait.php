<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Rfc4122;

trait VersionTrait
{
    /**
     * @return int|null
     */
    public abstract function getVersion();
    public abstract function isNil() : bool;
    private function isCorrectVersion() : bool
    {
        if ($this->isNil()) {
            return \true;
        }
        switch ($this->getVersion()) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
                return \true;
        }
        return \false;
    }
}
