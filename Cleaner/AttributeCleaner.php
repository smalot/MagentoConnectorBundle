<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
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
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $attributeClassName;

    /**
     * @param WebserviceGuesser $webserviceGuesser
     * @param EntityManager     $em
     * @param string            $attributeClassName
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        EntityManager $em,
        $attributeClassName
    ) {
        parent::__construct($webserviceGuesser);

        $this->em                 = $em;
        $this->attributeClassName = $attributeClassName;
    }

    /**
     * {@inhertidoc}
     */
    public function execute()
    {
        parent::beforeExecute();

        $magentoAttributes = $this->webservice->getAllAttributes();

        foreach ($magentoAttributes as $attribute) {
            $pimAttribute = $this->getAttribute($attribute['code']);

            if (
                    (!$pimAttribute ||
                    ($pimAttribute && !$pimAttribute->getFamilies())
                ) &&
                !in_array($attribute['code'], $this->getIgnoredAttributes())
            ) {
                try {
                    $this->handleAttributeNotInPimAnymore($attribute);
                } catch (SoapCallException $e) {
                    throw new InvalidItemException($e->getMessage(), array($attribute['code']));
                }
            }
        }
    }

    /**
     * Handle deletion or disableing of attributes which are not in PIM anymore
     * @param array $attribute
     */
    protected function handleAttributeNotInPimAnymore(array $attribute)
    {
        if ($this->notInPimAnymoreAction === self::DELETE) {
            var_dump($attribute);
            $this->webservice->deleteAttribute($attribute['code']);
        }
    }

    /**
     * Get attribute for attribute code
     * @param string $attributeCode
     *
     * @return mixed
     */
    protected function getAttribute($attributeCode) {
        return $this->em->getRepository($this->attributeClassName)->findOneBy(array('code' => $attributeCode));
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        $configurationFields = parent::getConfigurationFields();

        $configurationFields['notInPimAnymoreAction']['options']['choices'] = array(
            Cleaner::DO_NOTHING => Cleaner::DO_NOTHING,
            Cleaner::DELETE     => Cleaner::DELETE
        );

        return $configurationFields;
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
