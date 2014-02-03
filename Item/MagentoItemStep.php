<?php

namespace Pim\Bundle\MagentoConnectorBundle\Item;

use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\MagentoUrl;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;

/**
 * Magento item step
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
abstract class MagentoItemStep extends AbstractConfigurableStepElement
{
    /**
     * @var Webservice
     */
    protected $webservice;

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
    protected $soapApiKey;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @Assert\Url(groups={"Execution"})
     * @MagentoUrl(groups={"Execution"})
     */
    protected $soapUrl;

    /**
     * @var MagentoSoapClientParameters
     */
    protected $clientParameters;

    /**
     * @var boolean
     */
    protected $beforeExecute = false;

    /**
     * get soapUsername
     *
     * @return string Soap mangeto soapUsername
     */
    public function getSoapUsername()
    {
        return $this->soapUsername;
    }

    /**
     * Set soapUsername
     *
     * @param string $soapUsername Soap mangeto soapUsername
     *
     * @return MagentoItemStep
     */
    public function setSoapUsername($soapUsername)
    {
        $this->soapUsername = $soapUsername;

        return $this;
    }

    /**
     * get soapApiKey
     *
     * @return string Soap mangeto soapApiKey
     */
    public function getSoapApiKey()
    {
        return $this->soapApiKey;
    }

    /**
     * Set soapApiKey
     *
     * @param string $soapApiKey Soap mangeto soapApiKey
     *
     * @return MagentoItemStep
     */
    public function setSoapApiKey($soapApiKey)
    {
        $this->soapApiKey = $soapApiKey;

        return $this;
    }

    /**
     * get soapUrl
     *
     * @return string mangeto soap url
     */
    public function getSoapUrl()
    {
        return $this->soapUrl;
    }

    /**
     * Set soapUrl
     *
     * @param string $soapUrl mangeto soap url
     *
     * @return MagentoItemStep
     */
    public function setSoapUrl($soapUrl)
    {
        $this->soapUrl = $soapUrl;

        return $this;
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
                $this->soapUrl
            );
        }

        return $this->clientParameters;
    }

    /**
     * @param WebserviceGuesser $webserviceGuesser
     */
    public function __construct(WebserviceGuesser $webserviceGuesser)
    {
        $this->webserviceGuesser = $webserviceGuesser;
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
     * Set the step element configuration
     *
     * @param array $config
     */
    public function setConfiguration(array $config)
    {
        parent::setConfiguration($config);

        $this->afterConfigurationSet();
    }

    protected function afterConfigurationSet()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'soapUsername' => array(
                'options' => array(
                    'required' => true
                )
            ),
            'soapApiKey'   => array(
                //Should be remplaced by a password formType but who doesn't
                //empty the field at each edit
                'type'    => 'text',
                'options' => array(
                    'required' => true
                )
            ),
            'soapUrl' => array(
                'options' => array(
                    'required' => true
                )
            )
        );
    }
}
