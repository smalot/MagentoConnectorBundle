<?php

namespace Pim\Bundle\MagentoConnectorBundle\Guesser;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice16;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\WebserviceAttributeManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\WebserviceCategoryManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\WebserviceOptionManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\WebserviceProductManager;

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
    protected $MagentoSoapClient;

    /*
     *
     */
    public function __construct(
        MagentoSoapClient $magentoSoapClient,
        MagentoSoapClientParameters $clientParameters
    ) {
        $this->magentoSoapClient = $magentoSoapClient;
        $this->magentoVersion = $this->getMagentoVersion($this->MagentoSoapClient);
    }

    /**
     * Get the Webservice corresponding to the given Magento parameters
     *
     * @param $webserviceName
     * @throws NotSupportedVersionException
     * @return Webservice
     */
    public function getWebservice($webserviceName)
    {
        $webservice = null;
        switch ($this->magentoVersion) {
            case AbstractGuesser::MAGENTO_VERSION_1_8:
            case AbstractGuesser::MAGENTO_VERSION_1_7:
                switch ($webserviceName) {
                    case 'category':
                        $webservice = new WebserviceCategoryManager($this->MagentoSoapClient);
                        break;
                    case 'option':
                        $webservice = new WebserviceOptionManager($this->MagentoSoapClient);
                        break;
                    case 'product':
                        $webservice = new WebserviceProductManager($this->MagentoSoapClient);
                        break;
                    case 'attribute':
                        $webservice = new WebserviceAttributeManager($this->MagentoSoapClient);
                        break;
                }
                break;
            case AbstractGuesser::MAGENTO_VERSION_1_6:
                $webservice = new Webservice16($this->MagentoSoapClient);
                break;
            default:
                throw new NotSupportedVersionException(AbstractGuesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }
        return $webservice;
    }
}
