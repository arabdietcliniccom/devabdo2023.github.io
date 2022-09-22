<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Provider\Time;

use Staatic\Vendor\Ramsey\Uuid\Provider\TimeProviderInterface;
use Staatic\Vendor\Ramsey\Uuid\Type\Integer as IntegerObject;
use Staatic\Vendor\Ramsey\Uuid\Type\Time;
class FixedTimeProvider implements TimeProviderInterface
{
    private $fixedTime;
    public function __construct(Time $time)
    {
        $this->fixedTime = $time;
    }
    /**
     * @return void
     */
    public function setUsec($value)
    {
        $this->fixedTime = new Time($this->fixedTime->getSeconds(), $value);
    }
    /**
     * @return void
     */
    public function setSec($value)
    {
        $this->fixedTime = new Time($value, $this->fixedTime->getMicroseconds());
    }
    public function getTime() : Time
    {
        return $this->fixedTime;
    }
}
