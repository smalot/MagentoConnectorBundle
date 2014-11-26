<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * Magento Soap client
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClient extends \SoapClient implements MagentoSoapClientInterface
{
    /**
     * {@inheritdoc}
     */
    public function login($username, $soapApiKey)
    {
        return parent::login($username, $soapApiKey);
    }
}
