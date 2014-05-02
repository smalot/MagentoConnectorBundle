<?php

namespace Pim\Bundle\MagentoConnectorBundle\Merger;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\ConnectorMappingBundle\Merger\MappingMerger;

/**
 * Magento mapping merger
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoMappingMerger extends MappingMerger
{
    /**
     * Construct a MagentoMappingMerger
     *
     * @param array $mappers
     * @param string $name
     * @param type $allowAddition
     */
    public function __construct(array $mappers, $name, $allowAddition)
    {
        $direction = 'export';

        parent::__construct($mappers, $name, $direction, $allowAddition);
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

        $this->hasParametersSet = true;
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
                        'data-targets' => json_encode($this->getAllTargets()),
                        'data-name'    => $this->name
                    ),
                    'label' => 'pim_magento_connector.' . $this->direction . '.' . $this->name . 'Mapping.label',
                    'help'  => 'pim_magento_connector.' . $this->direction . '.' . $this->name . 'Mapping.help'
                )
            )
        );
    }
}
