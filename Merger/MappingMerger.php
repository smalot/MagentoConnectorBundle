<?php

namespace Pim\Bundle\MagentoConnectorBundle\Merger;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;

/**
 * Mapping merger
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MappingMerger
{
    protected $mappers = array();

    protected $name;

    protected $hasParametersSetted = false;

    /**
     * @param array  $mappers
     * @param string $name
     */
    public function __construct(array $mappers, $name)
    {
        $this->name = $name;

        foreach ($mappers as $mapper) {
            if (!isset($this->mappers[$mapper->getPriority()])) {
                $this->mappers[$mapper->getPriority()] = array();
            }

            $this->mappers[$mapper->getPriority()][] = $mapper;
        }

        ksort($this->mappers);
    }

    /**
     * Set parameters of all mappers
     * @param MagentoSoapClientParameters $clientParameters
     */
    public function setParameters(MagentoSoapClientParameters $clientParameters)
    {
        foreach ($this->getOrderedMappers() as $mapper) {
            $mapper->setParameters($clientParameters);
        }

        $this->hasParametersSetted = true;
    }

    /**
     * Get mapping for all mappers
     * @return array
     */
    public function getMapping()
    {
        $mergedMapping = new MappingCollection();

        if ($this->hasParametersSetted) {
            foreach ($this->getOrderedMappers() as $mapper) {
                $mergedMapping->merge($mapper->getMapping());
            }
        }

        return $mergedMapping;
    }

    /**
     * Set mapping for all mappers
     * @param array $mapping
     */
    public function setMapping($mapping)
    {
        foreach ($this->getOrderedMappers() as $mapper) {
            $mapper->setMapping($mapping);
        }
    }

    /**
     * Get configuration field for the merger
     * @return array
     */
    public function getConfigurationField()
    {
        return array(
            $this->name . 'Mapping' => array(
                'type'    => 'textarea',
                'options' => array(
                    'required' => false,
                    'attr'     => array(
                        'class' => 'mapping-field',
                        'data-sources' => json_encode($this->getAllSources()),
                        'data-targets' => json_encode($this->getAllTargets())
                    )
                )
            )
        );
    }

    /**
     * Get all sources (for suggestion)
     * @return array
     */
    protected function getAllSources()
    {
        $sources = array();
        foreach ($this->getOrderedMappers() as $mapper) {
            $sources = array_merge($sources, $mapper->getAllSources());
        }

        return array_unique($sources);
    }

    /**
     * Get all targets (for suggestion)
     * @return array
     */
    protected function getAllTargets()
    {
        $targets = array();
        foreach ($this->getOrderedMappers() as $mapper) {
            $targets = array_merge($targets, $mapper->getAllTargets());
        }

        return array_unique($targets);
    }

    /**
     * Get mappers ordered by priority
     * @return array
     */
    protected function getOrderedMappers()
    {
        $orderedMappers = array();

        foreach ($this->mappers as $mappers) {
            foreach ($mappers as $mapper) {
                $orderedMappers[] = $mapper;
            }
        }

        return $orderedMappers;
    }
}
