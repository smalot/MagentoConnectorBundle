<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\MagentoConnectorBundle\Manager\SimpleMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\GroupMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;

/**
 * Magento group mapper
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMGroupMapper extends ORMMapper
{
    /**
     * @var GroupMappingManager
     */
    protected $groupManager;

    /**
     * @param HasValidCredentialsValidator $hasValidCredentialsValidator
     * @param SimpleMappingManager         $simpleMappingManager
     * @param GroupMappingManager         $groupManager
     * @param string                       $rootIdentifier
     */
    public function __construct(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        SimpleMappingManager         $simpleMappingManager,
        GroupMappingManager          $groupManager,
        $rootIdentifier
    ) {
        parent::__construct($hasValidCredentialsValidator, $simpleMappingManager, $rootIdentifier);

        $this->groupManager = $groupManager;
    }

    /**
     * Get all sources
     * @param AttributeGroup $group
     *
     * @return array
     */
    public function getAllSources(AttributeGroup $group = null)
    {
        $sources = array();

        if ($this->isValid()) {
            $groups = $group === null ? $this->groupManager->getTrees() : $group->getChildren();

            foreach ($groups as $group) {
                $sources[] = array(
                    'id'   => $group->getCode(),
                    'text' => sprintf('%s (%s)', $group->getLabel(), $group->getCode())
                );

                $sources = array_merge($sources, $this->getAllSources($group));
            }
        }

        return $sources;
    }
}
