<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

/**
 * Associates attributes to their attribute sets and groups
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeToSetsWriter extends AbstractWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $associations = [];
        foreach ($items as $item) {
            $associations = array_merge($associations, $item);
        }

        $this->client->addAttributeToSets($associations);
    }
}
