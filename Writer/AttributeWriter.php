<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeMappingManager;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;

/**
 * Magento attribute writer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeWriter extends AbstractWriter
{
    const ATTRIBUTE_UPDATE_SIZE = 2;
    const ATTRIBUTE_UPDATED     = 'Attributes updated';
    const ATTRIBUTE_CREATED     = 'Attributes created';

    /**
     * @var AttributeMappingManager
     */
    protected $attributeMappingManager;

    protected $attribute;

    /**
     * Constructor
     *
     * @param WebserviceGuesser         $webserviceGuesser
     * @param AttributeMappingManager   $attributeMappingManager
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        AttributeMappingManager $attributeMappingManager
    ) {
        parent::__construct($webserviceGuesser);

        $this->attributeMappingManager = $attributeMappingManager;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $attributes)
    {
        $this->beforeExecute();


        foreach ($attributes as $attribute) {
                $this->attribute = $attribute[0];
            try {
                $this->handleAttribute($attribute[1]);
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), array(json_encode($attribute)));
            }
        }
    }

    /**
     * Handle attribute creation and update
     * @param array $attribute
     */
    protected function handleAttribute(array $attribute)
    {
        if (count($attribute) === self::ATTRIBUTE_UPDATE_SIZE) {
            $this->webservice->updateAttribute($attribute);
            $this->stepExecution->incrementSummaryInfo(self::ATTRIBUTE_UPDATED);
        } else {
            $pimAttribute = $this->attribute;
            $magentaAttributeId = $this->webservice->createAttribute($attribute);
            $this->stepExecution->incrementSummaryInfo(self::ATTRIBUTE_CREATED);
            $magentoUrl      = $this->soapUrl;

            $this->attributeMappingManager->registerAttributeMapping(
                $pimAttribute,
                $magentaAttributeId,
                $magentoUrl
            );
        }
    }
}
