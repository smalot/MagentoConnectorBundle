<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

/**
 * Attribute writer use to send attributes in Api Import
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeWriter extends AbstractWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $this->client->exportAttributes($items);
    }
}
