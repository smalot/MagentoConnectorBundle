<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\CatalogBundle\Manager\GroupManager as BaseGroupManager;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Custom group manager
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupManager extends BaseGroupManager
{
    /**
     * @var ObjectRepository
     */
    protected $objectRepository;

    /**
     * Constructor
     *
     * @param RegistryInterface $doctrine
     * @param string            $productClass
     * @param string            $attributeClass
     * @param ObjectRepository  $objectRepository
     */
    public function __construct(
        RegistryInterface $doctrine,
        $productClass,
        $attributeClass,
        ObjectRepository $objectRepository
    ) {
        parent::__construct($doctrine, $productClass, $attributeClass);

        $this->objectRepository = $objectRepository;
    }

    /**
     * Returns the entity repository
     *
     * @return ObjectRepository
     */
    public function getRepository()
    {
        return $this->objectRepository;
    }
}
