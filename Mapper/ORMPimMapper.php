<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\ConnectorMappingBundle\Mapper\ORMMapper;
use Pim\Bundle\ConnectorMappingBundle\Manager\SimpleMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;

/**
 * Mapper for ORM
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMPimMapper extends ORMMapper
{
    /**
     * @var HasValidCredentialsValidator
     */
    protected $hasValidCredentialsValidator;

    /**
     * @param HasValidCredentialsValidator $hasValidCredentialsValidator
     * @param SimpleMappingManager         $simpleMappingManager
     * @param string                       $rootIdentifier
     */
    public function __construct(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        SimpleMappingManager $simpleMappingManager,
        $rootIdentifier
    ) {
        parent::__construct($simpleMappingManager, $rootIdentifier);
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
}
