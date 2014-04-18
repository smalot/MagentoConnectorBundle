<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Exception\SoapCallException;

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
     * {@inheritdoc}
     */
    public function write(array $attributes)
    {

        foreach ($attributes as $attribute) {
            try {
                if (count($attribute) === self::ATTRIBUTE_UPDATE_SIZE) {
                    $this->webserviceGuesserFactory
                        ->getWebservice('attribute', $this->getClientParameters())->updateAttribute($attribute);
                    $this->stepExecution->incrementSummaryInfo(self::ATTRIBUTE_UPDATED);
                } else {
                    $this->webserviceGuesserFactory
                        ->getWebservice('attribute', $this->getClientParameters())->createAttribute($attribute);
                    $this->stepExecution->incrementSummaryInfo(self::ATTRIBUTE_CREATED);
                }
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), array(json_encode($attribute)));
            }
        }
    }
}
