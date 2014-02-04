<?php

namespace Pim\Bundle\MagentoConnectorBundle\Merger;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;

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

    public function setParameters(MagentoSoapClientParameters $clientParameters)
    {
        foreach ($this->getOrderedMappers() as $mapper) {
            $mapper->setParameters($clientParameters);
        }

        $this->hasParametersSetted = true;
    }

    public function getMapping()
    {
        $mergedMapping = array();
        foreach ($this->getOrderedMappers() as $mapper) {
            $mergedMapping = array_merge($mergedMapping, $mapper->getMapping());
        }

        return json_encode($mergedMapping);
    }

    public function setMapping($mapping)
    {
        foreach ($this->getOrderedMappers() as $mapper) {
            $mapper->setMapping(json_decode($mapping, true));
        }
    }

    public function getConfigurationField()
    {
        return array(
            $this->name . 'Mapping' => array(
                'type'    => 'hidden',
                'options' => array(
                    'required' => false,
                    'attr'     => array(
                        'class' => 'mapping-field'
                    )
                )
            )
        );
    }

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
