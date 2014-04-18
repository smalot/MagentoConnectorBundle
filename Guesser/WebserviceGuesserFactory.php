<?php

namespace Pim\Bundle\MagentoConnectorBundle\Guesser;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice16;
use Pim\Bundle\MagentoConnectorBundle\Webservice\AttributeWebservice;
use Pim\Bundle\MagentoConnectorBundle\Webservice\CategoryWebservice;
use Pim\Bundle\MagentoConnectorBundle\Webservice\AssociationWebservice;
use Pim\Bundle\MagentoConnectorBundle\Webservice\StoreViewsWebservice;
use Pim\Bundle\MagentoConnectorBundle\Webservice\OptionWebservice;
use Pim\Bundle\MagentoConnectorBundle\Webservice\ProductWebservice;

/**
 * A magento soap client to abstract interaction with the magento api
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebserviceGuesserFactory extends AbstractGuesser
{

    /**
     * @var string
     */
    protected $magentoVersion;

    /**
     * @var MagentoSoapClient
     */
    protected $magentoSoapClient;

    /*
     * @var array
     */
    protected $webserviceInstances = array();

    /**
     * Get the Webservice corresponding to the given Magento parameters
     *
     * @param string                        $webserviceName
     * @param                               $clientParameters
     * @throws NotSupportedVersionException
     * @return string                       $webservice
     */
    public function getWebservice($webserviceName, $clientParameters)
    {
        $this->magentoSoapClient = MagentoSoapClient::getInstance($clientParameters);
        $this->magentoVersion = $this->getMagentoVersion($this->magentoSoapClient);
        $webservice = null;
        switch ($this->magentoVersion) {
            case AbstractGuesser::MAGENTO_VERSION_1_8:
            case AbstractGuesser::MAGENTO_VERSION_1_7:
                $webservice = $this->getInstance($webserviceName);
                break;
            case AbstractGuesser::MAGENTO_VERSION_1_6:
                $webservice = new Webservice16($this->magentoSoapClient);
                break;
            default:
                throw new NotSupportedVersionException(AbstractGuesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }
        return $webservice;
    }

    /*
     * Get an instance of Webservice
     * @param string $webserviceName The name of the webservice needed
     * @return Webservice
     */
    protected function getInstance($webserviceName)
    {
        $webservice = null;
        switch ($webserviceName) {
            case 'category':
                if (array_key_exists($webserviceName, $this->webserviceInstances)) {
                    $webservice = $this->webserviceInstances[$webserviceName];
                } else {
                    $webservice = new CategoryWebservice($this->magentoSoapClient);
                    $this->webserviceInstances['category'] = $webservice;
                }
                break;
            case 'option':
                if (array_key_exists($webserviceName, $this->webserviceInstances)) {
                    $webservice = $this->webserviceInstances[$webserviceName];
                } else {
                    $webservice = new OptionWebservice($this->magentoSoapClient);
                    $this->webserviceInstances['option'] = $webservice;
                }
                break;
            case 'product':
                if (array_key_exists($webserviceName, $this->webserviceInstances)) {
                    $webservice = $this->webserviceInstances[$webserviceName];
                } else {
                    $webservice = new ProductWebservice($this->magentoSoapClient);
                    $this->webserviceInstances['product'] = $webservice;
                }
                break;
            case 'attribute':
                if (array_key_exists($webserviceName, $this->webserviceInstances)) {
                    $webservice = $this->webserviceInstances[$webserviceName];
                } else {
                    $webservice = new AttributeWebservice($this->magentoSoapClient);
                    $this->webserviceInstances['attribute'] = $webservice;
                }
                break;
            case 'association':
                if (array_key_exists($webserviceName, $this->webserviceInstances)) {
                    $webservice = $this->webserviceInstances[$webserviceName];
                } else {
                    $webservice = new AssociationWebservice($this->magentoSoapClient);
                    $this->webserviceInstances['association'] = $webservice;
                }
                break;
            case 'storeviews':
                if (array_key_exists($webserviceName, $this->webserviceInstances)) {
                    $webservice = $this->webserviceInstances[$webserviceName];
                } else {
                    $webservice = new StoreViewsWebservice($this->magentoSoapClient);
                    $this->webserviceInstances['storeviews'] = $webservice;
                }
                break;
        }

        return $webservice;
    }
}
