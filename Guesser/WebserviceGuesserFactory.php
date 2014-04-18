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
     *
     */
    public function __construct()
    {
        $this->magentoVersion = $this->getMagentoVersion($this->magentoSoapClient);
    }

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
     *
     */
    protected function getInstance($webserviceName)
    {
        $webServices = array(
            'category'    => new CategoryWebservice($this->magentoSoapClient),
            'option'      => new OptionWebservice($this->magentoSoapClient),
            'product'     => new ProductWebservice($this->magentoSoapClient),
            'attribute'   => new AttributeWebservice($this->magentoSoapClient),
            'association' => new AssociationWebservice($this->magentoSoapClient),
            'storeviews'  => new StoreViewsWebservice($this->magentoSoapClient)
        );

        return array_key_exists($webserviceName, $webServices) ? $webServices[$webserviceName] : null;
    }
}
