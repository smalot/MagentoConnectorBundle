<?php

namespace Pim\Bundle\MagentoConnectorBundle\Helper;

use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Help to compute prices for configurables
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceHelper
{
    /** @staticvar string */
    const BASE_PRICE    = 'base_price';

    /** @staticvar string */
    const OPTIONS_PRICE = 'options_price';

    /**
     * Compute price changes for configurable
     *
     * @param Group   $variantGroup
     * @param array   $products
     * @param string  $locale
     * @param string  $currency
     * @param string  $channelCode
     * @param boolean $lowest
     *
     * @return array
     */
    public function computePriceChanges(
        Group $variantGroup,
        array $products,
        $locale,
        $currency,
        $channelCode,
        $lowest = true
    ) {
        return [];
    }
}
