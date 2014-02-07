<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\MagentoUrlValidator;

/**
 * Abstract attribute mapper
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
Abstract class AbstractAttributeMapper implements MapperInterface
{
    const IDENTIFIER_FORMAT = 'attribute-%s';

    /**
     * @var MagentoSoapClientParameters
     */
    protected $clientParameters;

    /**
     * @var MagentoUrlValidator
     */
    protected $magentoUrlValidator;

    /**
     * @param MagentoUrlValidator $magentoUrlValidator
     */
    public function __construct(MagentoUrlValidator $magentoUrlValidator)
    {
        $this->magentoUrlValidator = $magentoUrlValidator;
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
     * @return string
     */
    public function getIdentifier()
    {
        return sha1(sprintf(self::IDENTIFIER_FORMAT, $this->clientParameters->getSoapUrl()));
    }

    /**
     * Is the mapper valid ?
     * @return boolean
     */
    public function isValid()
    {
        if (!$this->clientParameters) {
            var_dump('false');
            return false;
        }

        return $this->magentoUrlValidator->isValidMagentoUrl($this->clientParameters->getSoapUrl());
    }
}
