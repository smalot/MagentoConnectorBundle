<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection;

/**
 * Magento attribute id mapper
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoAttributeIdMapper extends MagentoMapper
{
    /**
     * @var WebserviceGuesser
     */
    protected $webserviceGuesser;

    /**
     * @param HasValidCredentialsValidator $hasValidCredentialsValidator
     * @param WebserviceGuesser            $webserviceGuesser
     */
    public function __construct(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        WebserviceGuesser $webserviceGuesser
    ) {
        parent::__construct($hasValidCredentialsValidator);

        $this->webserviceGuesser = $webserviceGuesser;
    }

    /**
     * Get mapping
     *
     * @return array
     */
    public function getMapping()
    {
        if (!$this->isValid()) {
            return new MappingCollection();
        } else {
            $attributes = $this->webserviceGuesser->getWebservice($this->clientParameters)->getAllAttributes();

            $mapping = new MappingCollection();
            foreach ($attributes as $attribute) {
                $mapping->add([
                    'source'    => $attribute['attribute_id'],
                    'target'    => $attribute['code'],
                    'deletable' => true
                ]);
            }

            return $mapping;
        }
    }

    /**
     * Get all targets
     *
     * @return array
     */
    public function getAllTargets()
    {
        return array();
    }

    /**
     * Get all sources
     *
     * @return array
     */
    public function getAllSources()
    {
        return array();
    }

    /**
     * Get mapper identifier
     *
     * @param string $rootIdentifier
     *
     * @return string
     */
    public function getIdentifier($rootIdentifier = 'attribute_id')
    {
        return parent::getIdentifier($rootIdentifier);
    }
}
