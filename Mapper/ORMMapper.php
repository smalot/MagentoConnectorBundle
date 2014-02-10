<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Manager\SimpleMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;

/**
 * Magento ORM mapper
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMMapper extends AbstractMapper
{
    /**
     * @var SimpleMappingManager
     */
    protected $simpleMappingManager;

    /**
     * @var string
     */
    protected $rootIdentifier;

    /**
     * @param HasValidCredentialsValidator $hasValidCredentialsValidator
     * @param SimpleMappingManager         $simpleMappingManager
     * @param string                       $rootIdentifier
     */
    public function __construct(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        SimpleMappingManager $simpleMappingManager,
        $rootIdentifier
    ) {
        parent::__construct($hasValidCredentialsValidator);

        $this->simpleMappingManager = $simpleMappingManager;
        $this->rootIdentifier       = $rootIdentifier;
    }

    /**
     * Get mapping
     * @return array
     */
    public function getMapping()
    {
        if (!$this->isValid()) {
            return new MappingCollection();
        }

        $simpleMappingItems = $this->simpleMappingManager->getMapping($this->getIdentifier($this->rootIdentifier));

        $mapping = new MappingCollection();
        foreach ($simpleMappingItems as $simpleMappingItem) {
            $mapping->add(array(
                'source'    => $simpleMappingItem->getSource(),
                'target'    => $simpleMappingItem->getTarget(),
                'deletable' => true
            ));
        }

        return $mapping;
    }

    /**
     * Set mapping
     * @param array $mapping
     */
    public function setMapping(array $mapping)
    {
        if (!$this->isValid()) {
            return;
        }

        $this->simpleMappingManager->setMapping($mapping, $this->getIdentifier($this->rootIdentifier));
    }

    /**
     * Get all targets
     * @return array
     */
    public function getAllTargets()
    {
        return $this->isValid() ? array_map(function($mappingItem) {
            return $mappingItem->getTarget();
        }, $this->simpleMappingManager->getMapping($this->getIdentifier($this->rootIdentifier))) : array();
    }

    /**
     * Get all sources
     * @return array
     */
    public function getAllSources()
    {
        return $this->isValid() ? array_map(function($mappingItem) {
            return $mappingItem->getSource();
        }, $this->simpleMappingManager->getMapping($this->getIdentifier($this->rootIdentifier))) : array();
    }

    /**
     * Get mapper priority
     * @return integer
     */
    public function getPriority()
    {
        return 10;
    }
}
