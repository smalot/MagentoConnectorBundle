<?php

namespace Pim\Bundle\MagentoConnectorBundle\Item;

use Symfony\Component\Validator\Constraints as Assert;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Magento item step
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials(groups={"Execution"})
 */
abstract class MagentoItemStep extends AbstractConfigurableStepElement implements StepExecutionAwareInterface
{
    /**
     * @var Webservice
     */
    protected $webservice;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @var WebserviceGuesser
     */
    protected $webserviceGuesser;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $soapUsername;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $wsdlUrl = MagentoSoapClientParameters::SOAP_WSDL_URL;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @Assert\Url(groups={"Execution"})
     */
    protected $magentoUrl;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $soapApiKey;

    /**
     * @var MagentoSoapClientParameters
     */
    protected $clientParameters;

    /**
     * @var boolean
     */
    protected $beforeExecute = false;

    /**
     * @var boolean
     */
    protected $afterConfiguration = false;

    /**
     * @param WebserviceGuesser $webserviceGuesser
     */
    public function __construct(WebserviceGuesser $webserviceGuesser)
    {
        $this->webserviceGuesser = $webserviceGuesser;
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $config)
    {
        parent::setConfiguration($config);

        if (!$this->afterConfiguration) {
            $this->afterConfigurationSet();

            $this->afterConfiguration = true;
        }
    }

    /**
     * Get fields for the twig
     *
     * @return array
     */
    public function getConfigurationFields()
    {
        return array(
            'soapUsername' => array(
                'options' => array(
                    'required' => true,
                    'help'     => 'pim_magento_connector.export.soapUsername.help',
                    'label'    => 'pim_magento_connector.export.soapUsername.label'
                )
            ),
            'soapApiKey'   => array(
                //Should be replaced by a password formType but which doesn't
                //empty the field at each edit
                'type'    => 'text',
                'options' => array(
                    'required' => true,
                    'help'     => 'pim_magento_connector.export.soapApiKey.help',
                    'label'    => 'pim_magento_connector.export.soapApiKey.label'
                )
            ),
            'magentoUrl' => array(
                'options' => array(
                    'required' => true,
                    'help'     => 'pim_magento_connector.export.magentoUrl.help',
                    'label'    => 'pim_magento_connector.export.magentoUrl.label'
                )
            ),
            'wsdlUrl' => array(
                'options' => array(
                    'required' => true,
                    'help'     => 'pim_magento_connector.export.wsdlUrl.help',
                    'label'    => 'pim_magento_connector.export.wsdlUrl.label',
                    'data'     => $this->getWsdlUrl()
                )
            )
        );
    }

    /**
     * Get SoapUsername
     *
     * @return string Soap magento soapUsername
     */
    public function getSoapUsername()
    {
        return $this->soapUsername;
    }

    /**
     * Set SoapUsername
     *
     * @param string $soapUsername Soap magento soapUsername
     *
     * @return MagentoItemStep
     */
    public function setSoapUsername($soapUsername)
    {
        $this->soapUsername = $soapUsername;

        return $this;
    }

    /**
     * Get soapApiKey
     *
     * @return string Soap magento soapApiKey
     */
    public function getSoapApiKey()
    {
        return $this->soapApiKey;
    }

    /**
     * Set soapApiKey
     *
     * @param string $soapApiKey Soap magento soapApiKey
     *
     * @return MagentoItemStep
     */
    public function setSoapApiKey($soapApiKey)
    {
        $this->soapApiKey = $soapApiKey;

        return $this;
    }

    /**
     * Get wsdlUrl
     *
     * @return string magento wsdl relative url
     */
    public function getWsdlUrl()
    {
        return $this->wsdlUrl;
    }

    /**
     * Set wsdlUrl
     *
     * @param string $wsdlUrl wsdl relative url
     *
     * @return MagentoItemStep
     */
    public function setWsdlUrl($wsdlUrl)
    {
        $this->wsdlUrl = $wsdlUrl;

        return $this;
    }

    /**
     * Get magentoUrl
     *
     * @return string Soap magento url
     */
    public function getMagentoUrl()
    {
        return $this->magentoUrl;
    }

    /**
     * Get magentoUrl
     *
     * @return MagentoItemStep
     */
    public function setMagentoUrl($magentoUrl)
    {
        $this->magentoUrl = $magentoUrl;

        return $this;
    }

    /**
     * Get soapUrl
     *
     * @return string magento soap url
     */
    public function getSoapUrl()
    {
        return $this->magentoUrl . $this->wsdlUrl;
    }

    /**
     * Function called before all item step execution
     */
    protected function beforeExecute()
    {
        if ($this->beforeExecute) {
            return;
        }

        $this->beforeExecute = true;

        $this->webservice = $this->webserviceGuesser->getWebservice($this->getClientParameters());
    }

    /**
     * Called after configuration set
     */
    protected function afterConfigurationSet()
    {
    }

    /**
     * Get the magento soap client parameters
     *
     * @return MagentoSoapClientParameters
     */
    protected function getClientParameters()
    {
        if (!$this->clientParameters) {
            $this->clientParameters = new MagentoSoapClientParameters(
                $this->soapUsername,
                $this->soapApiKey,
                $this->magentoUrl,
                $this->wsdlUrl
            );
        }

        return $this->clientParameters;
    }
}
