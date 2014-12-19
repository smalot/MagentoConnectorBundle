<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

/**
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class NormalizerRegistry
{
    /** @var NormalizerInterface[] */
    protected $normalizers = [];

    /**
     * @param string $key
     * @param NormalizerInterface $service
     */
    public function addNormalizer($key, $service)
    {
        $this->normalizers[$key] = $service;
    }

    /**
     * @param string $key
     *
     * @return NormalizerInterface
     * @throws \InvalidItemException
     */
    public function getNormalizer($key)
    {
        if (isset($this->normalizers[$key])) {
            throw new \InvalidItemException(
                sprintf('Normalizer "%s" unknown', $key)
            );
        }

        return $this->normalizers[$key];
    }
}
