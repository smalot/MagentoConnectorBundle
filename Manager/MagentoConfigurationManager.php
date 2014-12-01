<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Magento configuration manager
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoConfigurationManager
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var string */
    protected $magentoConfigClass;

    /**
     * Constructor
     *
     * @param RegistryInterface $doctrine
     * @param string            $magentoConfigClass
     */
    public function __construct(RegistryInterface $doctrine, $magentoConfigClass)
    {
        $this->doctrine           = $doctrine;
        $this->magentoConfigClass = $magentoConfigClass;
    }

    /**
     * Returns the Magento configuration repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->doctrine->getRepository($this->magentoConfigClass);
    }

    /**
     * Save a new Magento configuration in db
     *
     * @param MagentoConfiguration $configuration
     *
     * @return MagentoConfigurationManager
     */
    public function save(MagentoConfiguration $configuration)
    {
        $em = $this->doctrine->getEntityManager();

        $em->persist($configuration);
        $em->flush($configuration);

        return $this;
    }

    /**
     * Allow to retrieve a Magento configuration entity by its code
     *
     * @param string $code
     *
     * @return null|MagentoConfiguration
     */
    public function getMagentoConfigurationByCode($code)
    {
        return $this->getRepository()->findOneBy(['code' => $code]);
    }
}
