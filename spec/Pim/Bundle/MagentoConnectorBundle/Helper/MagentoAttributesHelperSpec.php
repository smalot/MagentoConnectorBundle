<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Helper;

use PhpSpec\ObjectBehavior;

class MagentoAttributesHelperSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\MagentoConnectorBundle\Helper\MagentoAttributesHelper');
    }

    public function it_returns_mandatory_attribute_codes_for_associations()
    {
        $this->getMandatoryAttributeCodesForAssociations()->shouldReturn(
            [
                'sku',
                'description',
                'short_description',
                'name'
            ]
        );
    }

    public function it_returns_header_association_replace_pattern()
    {
        $this->getAssociationReplacePatternHeader()->shouldReturn('/#toReplace#/');
    }

    public function it_returns_header_association_replace_subject()
    {
        $this->getAssociationReplaceSubjectHeader()->shouldReturn('_links_#toReplace#_sku');
    }

    public function it_returns_association_type_header()
    {
        $this->getAssociationTypeHeader('foo')->shouldReturn('_links_foo_sku');
    }

    public function it_returns_product_type_configurable()
    {
        $this->getConfigurableProductType()->shouldReturn('configurable');
    }

    public function it_returns_product_type_simple()
    {
        $this->getSimpleProductType()->shouldReturn('simple');
    }

    public function it_returns_attribute_set_header()
    {
        $this->getAttributeSetHeader()->shouldReturn('_attribute_set');
    }

    public function it_returns_category_header()
    {
        $this->getCategoryHeader()->shouldReturn('_category');
    }

    public function it_returns_category_root_header()
    {
        $this->getCategoryRootHeader()->shouldReturn('_root_category');
    }

    public function it_returns_created_at_header()
    {
        $this->getCreatedAtHeader()->shouldReturn('created_at');
    }

    public function it_returns_updated_at_header()
    {
        $this->getUpdatedAtHeader()->shouldReturn('updated_at');
    }

    public function it_returns_description_header()
    {
        $this->getDescriptionHeader()->shouldReturn('description');
    }

    public function it_returns_short_description_header()
    {
        $this->getShortDescriptionHeader()->shouldReturn('short_description');
    }

    public function it_returns_name_header()
    {
        $this->getNameHeader()->shouldReturn('name');
    }

    public function it_returns_product_type_header()
    {
        $this->getProductTypeHeader()->shouldReturn('_type');
    }

    public function it_returns_product_website_header()
    {
        $this->getProductWebsiteHeader()->shouldReturn('_product_websites');
    }

    public function it_returns_sku_header()
    {
        $this->getSkuHeader()->shouldReturn('sku');
    }

    public function it_returns_status_header()
    {
        $this->getStatusHeader()->shouldReturn('status');
    }

    public function it_returns_super_attribute_code_header()
    {
        $this->getSuperAttributeCodeHeader()->shouldReturn('_super_attribute_code');
    }

    public function it_returns_super_attribute_option_header()
    {
        $this->getSuperAttributeOptionHeader()->shouldReturn('_super_attribute_option');
    }

    public function it_returns_super_attribute_price_header()
    {
        $this->getSuperAttributePriceHeader()->shouldReturn('_super_attribute_price_corr');
    }

    public function it_returns_super_product_sku_header()
    {
        $this->getSuperProductSkuHeader()->shouldReturn('_super_products_sku');
    }

    public function it_returns_tax_class_id_header()
    {
        $this->getTaxClassIDHeader()->shouldReturn('tax_class_id');
    }

    public function it_returns_visibility_header()
    {
        $this->getVisibilityHeader()->shouldReturn('visibility');
    }

    public function it_returns_store_header()
    {
        $this->getStoreHeader()->shouldReturn('_store');
    }
}
