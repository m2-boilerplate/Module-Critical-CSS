<?php

namespace M2Boilerplate\CriticalCss\Service;

use M2Boilerplate\CriticalCss\Provider\ProviderInterface;
use Magento\Framework\Encryption\Encryptor;
use Magento\Store\Api\Data\StoreInterface;

class Identifier
{
    /**
     * @var Encryptor
     */
    protected $encryptor;

    public function __construct(Encryptor $encryptor)
    {
        $this->encryptor = $encryptor;
    }


    public function generateIdentifier(ProviderInterface $provider, StoreInterface $store, $identifier)
    {
        $uniqueIdentifier = sprintf('[%s]%s_%s', $store->getCode(), $provider->getName(), $identifier);
        return $this->encryptor->hash($uniqueIdentifier, Encryptor::HASH_VERSION_MD5);
    }

}