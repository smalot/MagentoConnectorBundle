<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;

/**
 * Magento family mapper
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoFamilyMapper extends Mapper
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
     * @return MappingCollection|array
     */
    public function getMapping()
    {
        if (!$this->isValid()) {
            return new MappingCollection();
        } else {
            $families = $this->webserviceGuesser->getWebservice($this->clientParameters)->getAttributeSetList();

            $mapping = new MappingCollection();
            foreach ($families as $attributeSetCode => $attributeSetName) {
                $targets[] = array('id' => $attributeSetCode, 'name' => $attributeSetName);
            }

            return $mapping;
        }
    }

    /**
     * Get all sources
     * @return array
     */
    public function getAllSources()
    {
        $sources = array();

        if ($this->isValid()) {
            $families = array_keys(
                $this->webserviceGuesser->getWebservice($this->clientParameters)->getAttributeSetList()
            );

            foreach ($families as $attributeSetCode => $attributeSetName) {
                $sources[] = array('id' => $attributeSetCode, 'name' => $attributeSetName);
            }
        }

        return $sources;
    }

    /**
     * Get mapper identifier
     * @param string $rootIdentifier
     *
     * @return string
     */
    public function getIdentifier($rootIdentifier = 'family')
    {
        return parent::getIdentifier($rootIdentifier);
    }
}
