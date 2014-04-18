<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesserFactory;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Pim\Bundle\MagentoConnectorBundle\Merger\MappingMerger;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Doctrine\ORM\EntityManager;

/**
 * Magento attribute cleaner
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class AttributeCleaner extends Cleaner
{
    const ATTRIBUTE_DELETED = 'Attribute deleted';

    /**
     * @var MappingMerger
     */
    protected $attributeMappingMerger;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $attributeClassName;

    /**
     * @var string
     */
    protected $attributeMapping;

    /**
     * Set attribute mapping
     * @param string $attributeMapping
     *
     * @return AttributeCleaner
     */
    public function setAttributeMapping($attributeMapping)
    {
        $this->attributeMappingMerger->setMapping(json_decode($attributeMapping, true));

        return $this;
    }

    /**
     * Get attribute mapping
     * @return string
     */
    public function getAttributeMapping()
    {
        return json_encode($this->attributeMappingMerger->getMapping()->toArray());
    }

    /**
     * @param WebserviceGuesserFactory $webserviceGuesserFactory
     * @param MappingMerger            $attributeMappingMerger
     * @param EntityManager            $em
     * @param string                   $attributeClassName
     */
    public function __construct(
        WebserviceGuesserFactory $webserviceGuesserFactory,
        MappingMerger            $attributeMappingMerger,
        EntityManager            $em,
        $attributeClassName
    ) {
        parent::__construct($webserviceGuesserFactory);

        $this->attributeMappingMerger = $attributeMappingMerger;
        $this->em                     = $em;
        $this->attributeClassName     = $attributeClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $magentoAttributes = $this->webserviceGuesserFactory
            ->getWebservice('attribute', $this->getClientParameters())->getAllAttributes();

        foreach ($magentoAttributes as $attribute) {
            $this->cleanAttribute($attribute, $magentoAttributes);
        }
    }

    /**
     * Clean the given attribute
     * @param array $attribute
     * @throws \Akeneo\Bundle\BatchBundle\Item\InvalidItemException
     */
    protected function cleanAttribute(array $attribute)
    {
        $magentoAttributeCode = $attribute['code'];
        $pimAttributeCode     = $this->attributeMappingMerger->getMapping()->getSource($magentoAttributeCode);
        $pimAttribute         = $this->getAttribute($pimAttributeCode);

        if (!in_array($attribute['code'], $this->getIgnoredAttributes()) &&
            (
                $pimAttributeCode == null ||
                (!$pimAttribute || ($pimAttribute && !$pimAttribute->getFamilies()))
            )
        ) {
            try {
                $this->handleAttributeNotInPimAnymore($attribute);
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), array($attribute['code']));
            }
        }
    }

    /**
     * Handle deletion or disabling of attributes which are not in PIM anymore
     * @param array $attribute
     */
    protected function handleAttributeNotInPimAnymore(array $attribute)
    {
        if ($this->notInPimAnymoreAction === self::DELETE) {
            $this->webserviceGuesserFactory->getWebservice('attribute', $this->getClientParameters())
                ->deleteAttribute($attribute['code']);
            $this->stepExecution->incrementSummaryInfo(self::ATTRIBUTE_DELETED);
        }
    }

    /**
     * Get attribute for attribute code
     * @param string $attributeCode
     *
     * @return mixed
     */
    protected function getAttribute($attributeCode)
    {
        return $this->em->getRepository($this->attributeClassName)->findOneBy(array('code' => $attributeCode));
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        $configurationFields = parent::getConfigurationFields();

        $configurationFields['notInPimAnymoreAction']['options']['choices'] = array(
            Cleaner::DO_NOTHING => 'pim_magento_connector.export.do_nothing.label',
            Cleaner::DELETE     => 'pim_magento_connector.export.delete.label'
        );

        $configurationFields['notInPimAnymoreAction']['options']['help'] =
            'pim_magento_connector.export.notInPimAnymoreAction.help';
        $configurationFields['notInPimAnymoreAction']['options']['label'] =
            'pim_magento_connector.export.notInPimAnymoreAction.label';

        return array_merge(
            $configurationFields,
            $this->attributeMappingMerger->getConfigurationField()
        );
    }

    /**
     * Called after the configuration is set
     */
    protected function afterConfigurationSet()
    {
        $this->attributeMappingMerger->setParameters($this->getClientParameters());
    }

    /**
     * Get all ignored attributes
     * @return array
     */
    protected function getIgnoredAttributes()
    {
        return array(
            'visibility',
            'old_id',
            'news_from_date',
            'news_to_date',
            'image_label',
            'small_image_label',
            'thumbnail_label',
            'country_of_manufacture',
            'price_type',
            'links_purchased_separately',
            'samples_title',
            'links_title',
            'links_exist',
            'tax_class_id',
            'status',
            'url_key',
            'url_path',
            'created_at',
            'meta_title',
            'updated_at',
            'meta_description',
            'meta_keyword',
            'is_recurring',
            'recurring_profile',
            'options_container',
            'custom_design',
            'custom_design_from',
            'custom_design_to',
            'custom_layout_update',
            'page_layout',
            'price',
            'category_ids',
            'required_options',
            'has_options',
            'sku_type',
            'weight_type',
            'shipment_type',
            'group_price',
            'special_price',
            'special_from_date',
            'special_to_date',
            'cost',
            'tier_price',
            'minimal_price',
            'msrp_enabled',
            'msrp_display_actual_price_type',
            'msrp',
            'price_view',
            'gift_message_available',
        );
    }
}
