<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration;
use Pim\Bundle\MagentoConnectorBundle\Factory\MagentoSoapClientFactory;
use Pim\Bundle\MagentoConnectorBundle\Manager\MagentoConfigurationManager;

/**
 * Product writer used to send products in Api Import
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriter extends AbstractConfigurableStepElement implements ItemWriterInterface
{
    /** @var MagentoSoapClientFactory $clientFactory */
    protected $clientFactory;

    /** @var MagentoConfigurationManager $configurationManager */
    protected $configurationManager;

    /** @var string $magentoConfigurationCode */
    protected $magentoConfigurationCode;

    /**
     * Constructor
     *
     * @param MagentoSoapClientFactory    $clientFactory
     * @param MagentoConfigurationManager $configurationManager
     */
    public function __construct(
        MagentoSoapClientFactory $clientFactory,
        MagentoConfigurationManager $configurationManager
    ) {
        $this->clientFactory = $clientFactory;
        $this->configurationManager = $configurationManager;
    }

    /**
     * {@inheritdoc}
    */
    public function write(array $items)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'magentoConfigurationCode' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->configurationManager->getConfigurationChoices(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_magento_connector.export.configuration.label',
                    'help'     => 'pim_magento_connector.export.configuration.help'
                ]
            ]
        ];
    }

    /**
     * Get the Magento configuration code
     *
     * @return string
     */
    public function getMagentoConfigurationCode()
    {
        return $this->magentoConfigurationCode;
    }

    /**
     * Set the Magento configuration code
     *
     * @param string $configurationCode
     *
     * @return ProductWriter
     */
    public function setMagentoConfigurationCode($configurationCode)
    {
        $this->magentoConfigurationCode = $configurationCode;

        return $this;
    }
}
