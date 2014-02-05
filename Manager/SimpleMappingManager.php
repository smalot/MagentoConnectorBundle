<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\MagentoConnectorBundle\Entity\SimpleMapping;

/**
 * Mapping manager
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleMappingManager
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $className;

    /**
     * Constructor
     * @param ObjectManager $objectManager
     * @param string        $className
     */
    public function __construct(ObjectManager $objectManager, $className)
    {
        $this->objectManager = $objectManager;
        $this->className     = $className;
    }

    /**
     * Get mapping for given identifier
     * @param string $identifier
     *
     * @return array
     */
    public function getMapping($identifier)
    {
        return $this->getEntityRepository()->findBy(array('identifier' => $identifier));
    }

    /**
     * Set mapping to database for given identifier
     * @param array  $mapping
     * @param string $identifier
     */
    public function setMapping(array $mapping, $identifier)
    {
        foreach ($mapping as $mappingItemSource => $mappingItemOutcome)
        {
            if ($mappingItemSource != 0) {
                $simpleMappingItem = $this->getEntityRepository()->findOneBy(array(
                    'identifier' => $identifier,
                    'source'     => $mappingItemSource
                ));

                if (!$simpleMappingItem) {
                    $simpleMappingItem = new SimpleMapping();
                    $simpleMappingItem->setIdentifier($identifier);
                    $simpleMappingItem->setSource($mappingItemSource);
                }

                $simpleMappingItem->setOutcome($mappingItemOutcome);

                $this->objectManager->persist($simpleMappingItem);
            }
        }

        $this->objectManager->flush();
    }

    /**
     * Get the entity manager
     * @return EntityRepository
     */
    protected function getEntityRepository()
    {
        return $this->objectManager->getRepository($this->className);
    }
}
