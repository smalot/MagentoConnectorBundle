<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;

/**
 * A normalizer to transform a option entity into an array
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionNormalizer extends AbstractNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $label = array(
            array(
                'store_id' => '0',
                'value'    => $object->getCode()
            ),
            array(
                'store_id' => 1,
                'value'    => $object->setLocale($context['defaultLocale'])->getOptionValue()->getLabel()
            )
        );

        foreach ($this->getPimLocales($context['channel']) as $locale) {
            $storeView = $this->getStoreViewForLocale(
                $locale->getCode(),
                $context['magentoStoreViews'],
                $context['storeViewMapping']
            );

            if ($storeView) {
                $label[] = array(
                    'store_id' => $storeView['store_id'],
                    'value'    => $object->setLocale($locale)->getOptionValue()->getLabel()
                );
            }
        }

        return array(
            $object->getAttribute()->getCode(),
            array(
                'label'      => $label,
                'order'      => 0,
                'is_default' => 0
            )
        );
    }
}
