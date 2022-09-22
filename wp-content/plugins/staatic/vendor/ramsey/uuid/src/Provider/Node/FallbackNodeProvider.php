<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Provider\Node;

use Staatic\Vendor\Ramsey\Uuid\Exception\NodeException;
use Staatic\Vendor\Ramsey\Uuid\Provider\NodeProviderInterface;
use Staatic\Vendor\Ramsey\Uuid\Type\Hexadecimal;
class FallbackNodeProvider implements NodeProviderInterface
{
    private $nodeProviders;
    /**
     * @param mixed[] $providers
     */
    public function __construct($providers)
    {
        $this->nodeProviders = $providers;
    }
    public function getNode() : Hexadecimal
    {
        $lastProviderException = null;
        foreach ($this->nodeProviders as $provider) {
            try {
                return $provider->getNode();
            } catch (NodeException $exception) {
                $lastProviderException = $exception;
                continue;
            }
        }
        throw new NodeException('Unable to find a suitable node provider', 0, $lastProviderException);
    }
}
