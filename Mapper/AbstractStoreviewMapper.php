<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;

/**
 * Abstract storeview mapper
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
Abstract class AbstractStoreviewMapper extends AbstractMapper
{
    const IDENTIFIER_FORMAT = 'storeview-%s';

    /**
     * Get mapper identifier
     * @return string
     */
    public function getIdentifier()
    {
        if ($this->clientParameters) {
            return sha1(sprintf(self::IDENTIFIER_FORMAT, $this->clientParameters->getSoapUrl()));
        } else {
            return '';
        }
    }
}
