<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Model\Media;
use Pim\Bundle\CatalogBundle\Model\Metric;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\InvalidScopeMatchException;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\AttributeNotFoundException;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\InvalidOptionException;

/**
 * A normalizer to transform a product value into an array
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface
{
    const GLOBAL_SCOPE = 'global';

    /**
     * Normalizes an object into a set of arrays/scalars
     *
     * @param object $object  object to normalize
     * @param string $format  format the normalization result will be encoded as
     * @param array  $context Context options for the normalizer
     *
     * @return array|scalar
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if ($this->isValueNormalizable(
            $object,
            $context['identifier'],
            $context['scopeCode'],
            $context['localeCode'],
            $context['onlyLocalized']
        )) {
            return $this->getNormalizedValue(
                $object,
                $context['magentoAttributes'],
                $context['magentoAttributesOptions'],
                $context['attributeMapping'],
                $context['currencyCode']
            );
        } else {
            return null;
        }
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer
     *
     * @param mixed  $data   Data to normalize.
     * @param string $format The format being (de-)serialized from or into.
     *
     * @return boolean
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductValueInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Is the given value normalizable
     * @param ProductValueInterface $value
     * @param string                $identifier
     * @param string                $scopeCode
     * @param string                $localeCode
     * @param boolean               $onlyLocalized
     *
     * @return boolean
     */
    protected function isValueNormalizable(
        ProductValueInterface $value,
        $identifier,
        $scopeCode,
        $localeCode,
        $onlyLocalized
    ) {
        return (
            ($value !== $identifier) &&
            ($value->getData() !== null) &&
            $this->isScopeNormalizable($value, $scopeCode) &&
            $this->isLocaleNormalizable($value, $localeCode) &&
            (
                (!$onlyLocalized && !$value->getAttribute()->isLocalizable()) ||
                $value->getAttribute()->isLocalizable()
            ) &&
            $this->forceLocalization($value, $onlyLocalized) &&
            $this->attributeIsNotIgnored($value) &&
            !($value->getData() instanceof Media)
        );
    }

    /**
     * Is scopable and is the scope corresponding ?
     * @param ProductValueInterface $value
     * @param string                $scopeCode
     *
     * @return boolean
     */
    protected function isScopeNormalizable(ProductValueInterface $value, $scopeCode)
    {
        return ($scopeCode == null) ||
            (!$value->getAttribute()->isScopable()) ||
            ($value->getAttribute()->isScopable() && $value->getScope() === $scopeCode);
    }

    /**
     * It is localizable and is the locale corresponding
     * @param ProductValueInterface $value
     * @param string                $localeCode
     *
     * @return boolean
     */
    protected function isLocaleNormalizable(ProductValueInterface $value, $localeCode)
    {
        return ($localeCode == null) ||
            (!$value->getAttribute()->isLocalizable()) ||
            ($value->getAttribute()->isLocalizable() && $value->getLocale() === $localeCode);
    }

    /**
     * Should we normalize the given non localizable value even if we are in only_localizable mode
     * @param ProductValueInterface $value
     * @param boolean               $onlyLocalized
     *
     * @return boolean
     */
    protected function forceLocalization(ProductValueInterface $value, $onlyLocalized)
    {
        return !($onlyLocalized &&
            in_array(
                $value->getAttribute()->getCode(),
                $this->getIgnoredAttributesForLocalization()
            ));
    }

    /**
     * Is the attribute of the given value ignored
     * @param ProductValueInterface $value
     *
     * @return boolean
     */
    protected function attributeIsNotIgnored(ProductValueInterface $value)
    {
        return !in_array($value->getAttribute()->getCode(), $this->getIgnoredAttributes());
    }

    /**
     * Get the normalized value
     *
     * @param ProductValueInterface $value
     * @param array                 $magentoAttributes
     * @param array                 $magentoAttributesOptions
     * @param MappingCollection     $attributeMapping
     * @param string                $currencyCode
     *
     * @throws AttributeNotFoundException If the given attribute doesn't exist in Magento
     * @return array
     */
    protected function getNormalizedValue(
        ProductValueInterface $value,
        array $magentoAttributes,
        array $magentoAttributesOptions,
        MappingCollection $attributeMapping,
        $currencyCode
    ) {
        $data          = $value->getData();
        $attribute     = $value->getAttribute();
        $attributeCode = $attributeMapping->getTarget($attribute->getCode());

        if (!isset($magentoAttributes[$attributeCode])) {
            throw new AttributeNotFoundException(
                sprintf(
                    'The magento attribute %s doesn\'t exist or isn\'t in the requested attributeSet. You should ' .
                    'create it first or adding it to the corresponding attributeSet',
                    $attributeCode
                )
            );
        }

        $normalizer     = $this->getNormalizer($data);
        $attributeScope = $magentoAttributes[$attributeCode]['scope'];

        $normalizedValue = $this->normalizeData(
            $data,
            $normalizer,
            $attribute,
            $attributeScope,
            $magentoAttributesOptions,
            $currencyCode,
            $attributeMapping
        );

        return array($attributeCode => $normalizedValue);
    }

    /**
     * Normalize the given data
     * @param mixed              $data
     * @param callable           $normalizer
     * @param Attribute          $attribute
     * @param string             $attributeScope
     * @param array              $magentoAttributesOptions
     * @param string             $currencyCode
     *
     * @throws InvalidScopeMatchException If there is a scope matching error between Magento and the PIM
     * @return array
     */
    protected function normalizeData(
        $data,
        $normalizer,
        Attribute $attribute,
        $attributeScope,
        $magentoAttributesOptions,
        $currencyCode,
        MappingCollection $attributeMapping
    ) {
        $attributeCode = $attributeMapping->getTarget($attribute->getCode());

        if (in_array($attributeCode, $this->getIgnoredScopeMatchingAttributes()) ||
            $this->scopeMatches($attribute, $attributeScope)
        ) {
            $normalizedValue = $normalizer($data, array(
                'attributeCode'            => $attributeCode,
                'magentoAttributesOptions' => $magentoAttributesOptions,
                'currencyCode'             => $currencyCode
            ));
        } else {
            throw new InvalidScopeMatchException(
                sprintf(
                    'The scope for the PIM attribute "%s" is not matching the scope of his corresponding Magento ' .
                    'attribute. To export the "%s" attribute, you must set the same scope in both Magento and the PIM.'.
                    "\nMagento scope : %s\n" .
                    "PIM scope : %s",
                    $attributeCode,
                    $attributeCode,
                    $attributeScope,
                    (($attribute->isLocalizable()) ? 'translatable' : 'not translatable')
                )
            );
        }

        return $normalizedValue;
    }

    /**
     * Does the attribute scope match with attributeScope on magento ?
     * @param Attribute $attribute
     * @param string    $attributeScope
     *
     * @return boolean
     */
    protected function scopeMatches(Attribute $attribute, $attributeScope)
    {
        return (
            $attributeScope !== self::GLOBAL_SCOPE &&
            $attribute->isLocalizable()
        ) ||
        (
            $attributeScope === self::GLOBAL_SCOPE &&
            !$attribute->isLocalizable()
        );
    }

    /**
     * Get all value normalizer (filter and normlizer)
     *
     * @return array
     */
    protected function getProductValueNormalizers()
    {
        return array(
            array(
                'filter'     => function ($data) {
                    return is_bool($data);
                },
                'normalizer' => function ($data, $parameters) {
                    return ($data) ? 1 : 0;
                }
            ),
            array(
                'filter'     => function ($data) {
                    return $data instanceof \DateTime;
                },
                'normalizer' => function ($data, $parameters) {
                    return $data->format(AbstractNormalizer::DATE_FORMAT);
                }
            ),
            array(
                'filter'     => function ($data) {
                    return $data instanceof AttributeOption;
                },
                'normalizer' => function ($data, $parameters) {
                    if (in_array($parameters['attributeCode'], $this->getIgnoredOptionMatchingAttributes())) {
                        return $data->getCode();
                    }

                    return $this->getOptionId(
                        $parameters['attributeCode'],
                        $data->getCode(),
                        $parameters['magentoAttributesOptions']
                    );
                }
            ),
            array(
                'filter'     => function ($data) {
                    return $data instanceof Collection || is_array($data);
                },
                'normalizer' => function ($data, $parameters) {
                    return $this->normalizeCollectionData(
                        $data,
                        $parameters['attributeCode'],
                        $parameters['magentoAttributesOptions'],
                        $parameters['currencyCode']
                    );
                }
            ),
            array(
                'filter'     => function ($data) {
                    return $data instanceof Metric;
                },
                'normalizer' => function ($data, $parameters) {
                    return (string) $data->getData();
                }
            ),
            array(
                'filter'     => function ($data) {
                    return true;
                },
                'normalizer' => function ($data, $parameters) {
                    return $data;
                }
            )
        );
    }

    /**
     * Get all ignored attribute
     *
     * @return array
     */
    protected function getIgnoredAttributes()
    {
        return array();
    }

    /**
     * Get all ignored attribute
     *
     * @return array
     */
    protected function getIgnoredAttributesForLocalization()
    {
        return array(
            'price'
        );
    }

    /**
     * Get all ignored attribute in scope matching test
     *
     * @return array
     */
    protected function getIgnoredScopeMatchingAttributes()
    {
        return array(
            'visibility'
        );
    }

    /**
     * Get all ignored attribute in option matching test
     *
     * @return array
     */
    protected function getIgnoredOptionMatchingAttributes()
    {
        return array();
    }

    /**
     * Get normalizer closure matching the corresponding filter with $data
     *
     * @param mixed $data
     *
     * @return closure
     */
    protected function getNormalizer($data)
    {
        $productValueNormalizers = $this->getProductValueNormalizers();

        $cpt = 0;
        $end = count($productValueNormalizers);

        while ($cpt < $end && !$productValueNormalizers[$cpt]['filter']($data)) {
            $cpt++;
        }

        return $productValueNormalizers[$cpt]['normalizer'];
    }

    /**
     * Normalize the value collection
     *
     * @param array  $collection
     * @param string $attributeCode
     * @param array  $magentoAttributesOptions
     * @param string $currencyCode
     *
     * @return string
     */
    protected function normalizeCollectionData($collection, $attributeCode, array $magentoAttributesOptions, $currencyCode)
    {
        $result = array();
        foreach ($collection as $item) {
            if ($item instanceof AttributeOption) {
                $optionCode = $item->getCode();

                $result[] = $this->getOptionId($attributeCode, $optionCode, $magentoAttributesOptions);
            } elseif ($item instanceof ProductPrice) {
                if ($item->getData() !== null &&
                    $item->getCurrency() === $currencyCode
                ) {
                    return $item->getData();
                }
            } else {
                $result[] = (string) $item;
            }
        }

        return $result;
    }

    /**
     * Get the id of the given magento option code
     *
     * @param string $attributeCode            The product attribute code
     * @param string $optionCode               The option label
     * @param array  $magentoAttributesOptions Attribute options list from Magento
     *
     * @throws InvalidOptionException If the given option doesn't exist on Magento
     * @return integer
     */
    protected function getOptionId($attributeCode, $optionCode, $magentoAttributesOptions)
    {
        $attributeCode = strtolower($attributeCode);

        if (!isset($magentoAttributesOptions[$attributeCode][$optionCode])) {
            throw new InvalidOptionException(
                sprintf(
                    'The attribute "%s" doesn\'t have any option named "%s" on ' .
                    'Magento side. You should add this option in your "%s" attribute on Magento or export the PIM ' .
                    'options using this Magento connector.',
                    $attributeCode,
                    $optionCode,
                    $attributeCode
                )
            );
        }

        return $magentoAttributesOptions[$attributeCode][$optionCode];
    }
}
