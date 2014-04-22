<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;

/**
 * Magento attribute set writer
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeSetWriter extends AbstractWriter
{
    const ATTRIBUTE_SET_UPDATE_SIZE = 2;
    const ATTRIBUTE_SET_UPDATED     = 'Attributes set updated';
    const ATTRIBUTE_SET_CREATED     = 'Attributes set created';

    /**
     * {@inheritdoc}
     */
    public function write(array $attributesSet)
    {
        $this->beforeExecute();

        foreach ($attributesSet as $attributeSet) {
            try {
                if (count($attributeSet) === self::ATTRIBUTE_UPDATE_SIZE) {
                    $this->webservice->createAttributeSet($attributeSet);
                    $this->stepExecution->incrementSummaryInfo(self::ATTRIBUTE_SET_UPDATED);
                } else {
                    $this->webservice->createAttributeSet($attributeSet);
                    $this->stepExecution->incrementSummaryInfo(self::ATTRIBUTE_SET_CREATED);
                }
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), array(json_encode($attributeSet)));
            }
        }
    }
}
