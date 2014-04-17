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
class WebserviceGuesser extends AbstractGuesser
{
    /**
     * @var WebserviceAttributeManager
     */
    protected $webserviceAttributeManager;

    /**
     * @var WebserviceProductManager
     */
    protected $webserviceProductManager;

    /**
     * @var WebserviceOptionManager
     */
    protected $webserviceOptionManager;

    /**
     * @var WebserviceCategoryManager
     */
    protected $webserviceCategoryManager;

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
     * @throws NotSupportedVersionException If the magento version is not supported
     * @return Webservice
     */
    public function getWebservice()
    {
        switch ($this->magentoVersion) {
            case AbstractGuesser::MAGENTO_VERSION_1_8:
            case AbstractGuesser::MAGENTO_VERSION_1_7:
                $this->webserviceCategoryManager = new WebserviceCategoryManager($this->MagentoSoapClient);
                $this->webserviceOptionManager = new WebserviceOptionManager($this->MagentoSoapClient);
                $this->webserviceProductManager = new WebserviceProductManager($this->MagentoSoapClient);
                $this->webserviceAttributeManager = new WebserviceAttributeManager($this->MagentoSoapClient);
                break;
            case AbstractGuesser::MAGENTO_VERSION_1_6:
                $this->webservice = new Webservice16($this->MagentoSoapClient);
                break;
            default:
                throw new NotSupportedVersionException(AbstractGuesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }
    }
}
