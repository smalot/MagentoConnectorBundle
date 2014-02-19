<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\AttributeOption;

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
                'store_id' => '1',
                'value'    => $this->getOptionLabel($object, $context['defaultLocale'])
            )
        );

        foreach ($this->getOptionLocales($object) as $locale) {
            $storeView = $this->getStoreViewForLocale(
                $locale,
                $context['magentoStoreViews'],
                $context['storeViewMapping']
            );

            if ($storeView) {
                $label[] = array(
                    'store_id' => (string) $storeView['store_id'],
                    'value'    => $this->getOptionLabel(
                        $object,
                        $locale,
                        $context['defaultLocale']
                    )
                );
            }
        }

        return array(
            $context['attributeCode'],
            array(
                'label'      => $label,
                'order'      => 0,
                'is_default' => 0
            )
        );
    }

    /**
     * get options locale
     * @param AttributeOption $option
     *
     * @return array
     */
    protected function getOptionLocales(AttributeOption $option)
    {
        $locales = array();

        foreach ($option->getOptionValues() as $optionValue) {
            $locales[] = $optionValue->getLocale();
        }

        return $locales;
    }

    /**
     * Get option translation for given locale code
     * @param AttributeOption $option
     * @param string          $locale
     * @param string          $defaultLocale
     *
     * @return mixed
     */
    protected function getOptionLabel(AttributeOption $option, $locale, $defaultLocale = null)
    {
        $optionValue = $option->setLocale($locale)->getOptionValue();

        if (!$optionValue) {
            if ($defaultLocale) {
                return $this->getOptionTranslation($option, $defaultLocale);
            } else {
                return $option->getCode();
            }
        } else {
            return $optionValue->getLabel();
        }
    }
}
