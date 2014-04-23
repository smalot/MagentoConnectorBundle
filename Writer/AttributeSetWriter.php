<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyManager;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Proxies\__CG__\Pim\Bundle\CatalogBundle\Entity\Family;

/**
 * Magento attribute set writer
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeSetWriter extends AbstractWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $this->beforeExecute();

        foreach ($items as $item) {
            try {
                $this->webservice->createAttributeSet($item['attributeSetName']);
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), array());
            }
        }
    }

}
