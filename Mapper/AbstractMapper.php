<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;

/**
 * Abstract mapper
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
Abstract class AbstractMapper implements MapperInterface
{
    /**
     * @var MagentoSoapClientParameters
     */
    protected $clientParameters;

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
     * Is the mapper valid ?
     * @return boolean
     */
    public function isValid()
    {
        if (!$this->clientParameters) {
            return false;
        }

        return $this->hasValidCredentialsValidator->areValidSoapParameters($this->clientParameters);
    }
}
