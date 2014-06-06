<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\CatalogBundle\Manager\AttributeManager as BaseAttributeManager;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;

/**
 * Custom attribute manager
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeManager extends BaseAttributeManager
{
    /**
     * Get attributes
     * @param array $criterias
     *
     * @return array
     */
    public function getAttributes(array $criterias = [])
    {
        return $this->getRepository()->findBy($criterias);
    }

    /**
     * Returns the entity repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository()
    {
        $classMetadata = $this->objectManager->getMetadataFactory()->getMetadataFor($this->attributeClass);

        return new AttributeRepository($this->objectManager, $classMetadata);
    }
}
