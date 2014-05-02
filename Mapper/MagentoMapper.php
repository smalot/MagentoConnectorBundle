<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\ConnectorMappingBundle\Mapper\Mapper;

/**
 * Magento mapper
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoMapper extends Mapper
{
    /**
     * @var MagentoSoapClientParameters
     */
    protected $clientParameters = null;

    /**
     * @var HasValidCredentialsValidator
     */
    protected $hasValidCredentialsValidator;

    /**
     * @param HasValidCredentialsValidator $hasValidCredentialsValidator
     */
    public function __construct(HasValidCredentialsValidator $hasValidCredentialsValidator)
    {
        $this->hasValidCredentialsValidator = $hasValidCredentialsValidator;
    }

    /**
     * Set mapper parameters
     * @param MagentoSoapClientParameters $clientParameters
     */
    public function setParameters(MagentoSoapClientParameters $clientParameters)
    {
        $this->clientParameters = $clientParameters;
    }

    /**
     * Get mapper identifier
     * @param string $rootIdentifier
     *
     * @return string
     */
    public function getIdentifier($rootIdentifier = 'generic')
    {
        if ($this->isValid()) {
            return sha1(sprintf(self::IDENTIFIER_FORMAT, $rootIdentifier, $this->clientParameters->getSoapUrl()));
        } else {
            return '';
        }
    }

    /**
     * Is the mapper valid ?
     * @return boolean
     */
    public function isValid()
    {
        return $this->clientParameters !== null &&
            $this->hasValidCredentialsValidator->areValidSoapCredentials($this->clientParameters);
    }
}
