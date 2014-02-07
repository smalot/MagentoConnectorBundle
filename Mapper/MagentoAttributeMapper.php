<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\MagentoUrlValidator;

/**
 * Magento attribute mapper
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoAttributeMapper extends AbstractAttributeMapper
{
    /**
     * @var WebserviceGuesser
     */
    protected $webserviceGuesser;

    /**
     * @param MagentoUrlValidator $magentoUrlValidator
     * @param WebserviceGuesser $webserviceGuesser
     */
    public function __construct(
        MagentoUrlValidator $magentoUrlValidator,
        WebserviceGuesser $webserviceGuesser
    ) {
        parent::__construct($magentoUrlValidator);

        $this->webserviceGuesser = $webserviceGuesser;
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
            $attributes = $this->webserviceGuesser->getWebservice($this->clientParameters)->getAllAttributes();

            $mapping = new MappingCollection();
            foreach (array_keys($attributes) as $attributeCode) {
                if (in_array($attributeCode, $this->mandatoryAttributes())) {
                    $mapping->add(array(
                        'source'    => $attributeCode,
                        'target'    => $attributeCode,
                        'deletable' => false
                    ));
                }
            }

            return $mapping;
        }
    }

    /**
     * Set mapping
     * @param array $mapping
     */
    public function setMapping(array $mapping)
    {

    }

    /**
     * Get all targets
     * @return array
     */
    public function getAllTargets()
    {
        if (!$this->isValid()) {
            return array();
        } else {
            $attributes = $this->webserviceGuesser->getWebservice($this->clientParameters)->getAllAttributes();

            return array_keys($attributes);
        }
    }

    /**
     * Get all sources
     * @return array
     */
    public function getAllSources()
    {
        return array();
    }

    /**
     * Get mapper priority
     * @return integer
     */
    public function getPriority()
    {
        return 0;
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
