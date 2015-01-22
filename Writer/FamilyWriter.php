<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Normalizer\Dictionary\FamilyLabelDictionary;

/**
 * Family writer use to send attribute sets and attribute groups in Api Import
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyWriter extends AbstractWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        try {
            $client = $this->getClient();
            $client->exportAttributeSets($items);
        } catch (\SoapFault $e) {
            $this->errorHelper->manageErrors(
                $this->stepExecution,
                $e,
                $items,
                FamilyLabelDictionary::ATTRIBUTE_SET_ID_HEADER,
                $this->getName()
            );
        }
    }
}
