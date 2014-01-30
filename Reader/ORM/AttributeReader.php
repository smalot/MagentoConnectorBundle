<?php

namespace Pim\Bundle\MagentoConnectorBundle\Reader\ORM;

use Pim\Bundle\BaseConnectorBundle\Reader\ORM\EntityReader;
use Pim\Bundle\MagentoConnectorBundle\Entity\Repository\CategoryRepository;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Entity\Attribute;

/**
 * ORM reader for product
 *
 * @author    Julien SAnchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeReader extends EntityReader
{
    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $attribute = parent::read();

        while ($attribute !== null && $this->isAttriguteIgnored($attribute)) {
            $attribute = parent::read();
        }

        return $attribute;
    }

    /**
     * Is the given attribute ignored ?
     * @param Attribute $attribute
     *
     * @return boolean
     */
    protected function isAttriguteIgnored(Attribute $attribute)
    {
        return in_array($attribute->getCode(), $this->getIgnoredAttributes());
    }

    /**
     * {@inheritdoc}
     */
    protected function getQuery()
    {
        if (!$this->query) {
            $this->query = $this->em
                ->getRepository($this->className)
                ->createQueryBuilder('c')
                ->join('c.families', 'PimCatalogBundle:Family')
                ->getQuery();
        }

        return $this->query;
    }

    /**
     * Get all ignored attributes
     * @return array
     */
    protected function getIgnoredAttributes()
    {
        return array(
            'sku',
            'name',
            'description'
        );
    }
}
