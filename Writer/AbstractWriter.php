<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\MagentoConnectorBundle\Manager\MagentoConfigurationManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;

/**
 * Abstract writer
 *
 * @author     Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright  2014 Akeneo SAS (http://www.akeneo.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractWriter extends AbstractConfigurableStepElement implements
    ItemWriterInterface,
    StepExecutionAwareInterface
{
    /** @var MagentoConfigurationManager */
    protected $configurationManager;

    /** @var string */
    protected $configurationCode;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var MagentoSoapClient */
    protected $client;

    /** @var ErrorHelper */
    protected $errorHelper;

    /**
     * @param MagentoConfigurationManager $configurationManager
     * @param ErrorHelper                 $errorHelper
     */
    public function __construct(MagentoConfigurationManager $configurationManager, ErrorHelper $errorHelper)
    {
        $this->configurationManager = $configurationManager;
        $this->errorHelper          = $errorHelper;
    }

    /**
     * Login the client to be able to call API Import method
     * This method is called once before write
     */
    public function initialize()
    {
        $code = $this->getConfigurationCode();

        $configuration = $this->configurationManager->getMagentoConfigurationByCode($code);
        $client = $this->configurationManager->createClient($configuration);

        $client->login($configuration->getSoapUsername(), $configuration->getSoapApiKey());
        $this->setClient($client);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'configurationCode' => [
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
    public function getConfigurationCode()
    {
        return $this->configurationCode;
    }

    /**
     * @param MagentoSoapClient $client
     *
     * @return ProductWriter
     */
    public function setClient(MagentoSoapClient $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return MagentoSoapClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set the Magento configuration code
     *
     * @param string $configurationCode
     *
     * @return ProductWriter
     */
    public function setConfigurationCode($configurationCode)
    {
        $this->configurationCode = $configurationCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Flatten items by concatenating entity parts into one array
     * $items = [entity1, e2, e3, ...]
     * entity = [part1, part2, p3, ...]
     * Returns [entity1 part1, e1p2, e2p1, e2p2, e3p1, ...]
     *
     * @param array $entities Items received from ItemStep
     *
     * @return array
     */
    protected function getFlattenedItems(array $items)
    {
        $flattenedItems = [];
        foreach ($items as $entity) {
            $flattenedItems = array_merge($flattenedItems, $entity);
        }

        return $flattenedItems;
    }
}
