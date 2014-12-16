<?php

namespace Pim\Bundle\MagentoConnectorBundle\Reader;

use Pim\Bundle\BaseConnectorBundle\Reader\ORM\EntityReader;

/**
 * This attribute reader is used to retrieve non-identifier attributes
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeReader extends EntityReader
{
    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        if (!$this->query) {
            $this->query = $this->em
                ->getRepository($this->className)
                ->createQueryBuilder('c')
                ->select('c')
                ->where('c.attributeType != :attributeType')
                ->setParameter('attributeType', 'pim_catalog_identifier')
                ->getQuery();
        }

        return $this->query;
    }
}
