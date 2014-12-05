<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration;
use Pim\Bundle\MagentoConnectorBundle\Factory\MagentoSoapClientFactory;
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

    /** @var MagentoSoapClientFactory */
    protected $clientFactory;

    /**
     * Constructor
     *
     * @param RegistryInterface        $doctrine
     * @param MagentoSoapClientFactory $clientFactory
     * @param string                   $magentoConfigClass
     */
    public function __construct(
        RegistryInterface $doctrine,
        MagentoSoapClientFactory $clientFactory,
        $magentoConfigClass
    ) {
        $this->doctrine           = $doctrine;
        $this->clientFactory      = $clientFactory;
        $this->magentoConfigClass = $magentoConfigClass;
    }

    /**
     * Returns the Magento configuration repository
     *
     * @return \Pim\Bundle\MagentoConnectorBundle\Entity\Repository\MagentoConfigurationRepository
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

    /**
     * Get configuration choices
     * Allow to list configurations in an array like array[<code>] = <label>
     *
     * @return string[]
     */
    public function getConfigurationChoices()
    {
        $configChoices = $this->getRepository()->getChoices();

        $choices = [];
        foreach ($configChoices as $config) {
            $choices[$config['code']] = $config['label'];
        }

        return $choices;
    }

    /**
     * Create a Magento SOAP client from configuration and SOAP options
     *
     * @param MagentoConfiguration $configuration
     * @param array                $soapOptions
     *
     * @return \Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient
     */
    public function createClient(MagentoConfiguration $configuration, array $soapOptions = [])
    {
        return $this->clientFactory->createMagentoSoapClient($configuration, $soapOptions);
    }
}
