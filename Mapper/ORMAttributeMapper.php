<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Manager\SimpleMappingManager;

/**
 * Magento attribute mapper
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMAttributeMapper extends AbstractAttributeMapper
{
    /**
     * @var SimpleMappingManager
     */
    protected $simpleMappingManager;

    public function __construct(SimpleMappingManager $simpleMappingManager)
    {
        $this->simpleMappingManager = $simpleMappingManager;
    }

    public function getMapping()
    {
        $simpleMappingItems = $this->simpleMappingManager->getMapping($this->getIdentifier());

        $mapping = array();
        foreach ($simpleMappingItems as $simpleMappingItem) {
            $mapping[$simpleMappingItem->getSource()] = $simpleMappingItem->getOutcome();
        }

        return $mapping;
    }

    public function setMapping(array $mapping)
    {
        $this->simpleMappingManager->setMapping($mapping, $this->getIdentifier());
    }

    public function getPriority()
    {
        return 10;
    }
}
