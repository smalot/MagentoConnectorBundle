<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClientParametersRegistry
{
    /**
     * Array of all MagentoSoapClientParameters instances
     *
     * @var array
     */
    protected static $instances;

    /**
     * Gives MagentoSoapClientParameters which corresponding to given parameters
     *
     * @param array $soapParameters Associative array which contains soap parameters
     * @return MagentoSoapClientParameters
     */
    public static function getInstance(
        $soapUsername,
        $soapApiKey,
        $magentoUrl,
        $wsdlUrl,
        $defaultStoreView,
        $httpLogin = null,
        $httpPassword = null
    ) {
        $hash = md5(
            $soapUsername.
            $soapApiKey.
            $magentoUrl.
            $wsdlUrl.
            $defaultStoreView.
            $httpLogin.
            $httpPassword
        );

        if (!isset(static::$instances[$hash])) {
            static::$instances[$hash] = new MagentoSoapClientParameters(
                $soapUsername,
                $soapApiKey,
                $magentoUrl,
                $wsdlUrl,
                $defaultStoreView,
                $httpLogin,
                $httpPassword
            );
        }

        return $this->instances[$hash];
    }

}
