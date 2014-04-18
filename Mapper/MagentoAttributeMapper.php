<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesserFactory;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;

/**
 * Magento attribute mapper
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoAttributeMapper extends Mapper
{
    /**
     * @var WebserviceGuesserFactory
     */
    protected $webserviceGuesserFactory;

    /**
     * @param HasValidCredentialsValidator $hasValidCredentialsValidator
     * @param WebserviceGuesserFactory     $webserviceGuesserFactory
     */
    public function __construct(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        WebserviceGuesserFactory $webserviceGuesserFactory
    ) {
        parent::__construct($hasValidCredentialsValidator);

        $this->webserviceGuesserFactory = $webserviceGuesserFactory;
    }

    /**
     * Get mapping
     * @return array
     */
    public function getMapping()
    {
        if (!$this->isValid()) {
            return new MappingCollection();
        } else {
            $attributes = $this->webserviceGuesserFactory
                ->getWebservice('attribute', $this->getClientParameters())->getAllAttributes();

            $mapping = new MappingCollection();
            foreach (array_keys($attributes) as $attributeCode) {
                if (in_array($attributeCode, $this->mandatoryAttributes())) {
                    $mapping->add(
                        array(
                            'source'    => $attributeCode,
                            'target'    => $attributeCode,
                            'deletable' => false
                        )
                    );
                }
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
            $attributeCodes = array_keys(
                $this->webserviceGuesserFactory
                    ->getWebservice('attribute', $this->getClientParameters())->getAllAttributes()
            );

            foreach ($attributeCodes as $attributeCode) {
                $sources[] = array('id' => $attributeCode, 'text' => $attributeCode);
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
    public function getIdentifier($rootIdentifier = 'attribute')
    {
        return parent::getIdentifier($rootIdentifier);
    }

    /**
     * Get mandatory attributes
     * @return array
     */
    protected function mandatoryAttributes()
    {
        return array(
            'name',
            'price',
            'description',
            'short_description',
            'tax_class_id',
        );
    }
}
