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
        $this->getHeaderAssociationReplacePattern()->shouldReturn('/#toReplace#/');
    }

    public function it_returns_header_association_replace_subject()
    {
        $this->getHeaderAssociationReplaceSubject()->shouldReturn('_links_#toReplace#_sku');
    }

    public function it_returns_association_type_header()
    {
        $this->getAssociationTypeHeader('foo')->shouldReturn('_links_foo_sku');
    }

    public function it_returns_product_type_configurable()
    {
        $this->getProductTypeConfigurable()->shouldReturn('configurable');
    }

    public function it_returns_product_type_simple()
    {
        $this->getProductTypeSimple()->shouldReturn('simple');
    }

    public function it_returns_attribute_set_header()
    {
        $this->getHeaderAttributeSet()->shouldReturn('_attribute_set');
    }

    public function it_returns_category_header()
    {
        $this->getHeaderCategory()->shouldReturn('_category');
    }

    public function it_returns_category_root_header()
    {
        $this->getHeaderCategoryRoot()->shouldReturn('_root_category');
    }

    public function it_returns_created_at_header()
    {
        $this->getHeaderCreatedAt()->shouldReturn('created_at');
    }

    public function it_returns_updated_at_header()
    {
        $this->getHeaderupdatedAt()->shouldReturn('updated_at');
    }

    public function it_returns_description_header()
    {
        $this->getHeaderDescription()->shouldReturn('description');
    }

    public function it_returns_short_description_header()
    {
        $this->getHeaderShortDescription()->shouldReturn('short_description');
    }

    public function it_returns_name_header()
    {
        $this->getHeaderName()->shouldReturn('name');
    }

    public function it_returns_product_type_header()
    {
        $this->getHeaderProductType()->shouldReturn('_type');
    }

    public function it_returns_product_website_header()
    {
        $this->getHeaderProductWebsite()->shouldReturn('_product_websites');
    }

    public function it_returns_sku_header()
    {
        $this->getHeaderSku()->shouldReturn('sku');
    }

    public function it_returns_status_header()
    {
        $this->getHeaderStatus()->shouldReturn('status');
    }

    public function it_returns_super_attribute_code_header()
    {
        $this->getHeaderSuperAttributeCode()->shouldReturn('_super_attribute_code');
    }

    public function it_returns_super_attribute_option_header()
    {
        $this->getHeaderSuperAttributeOption()->shouldReturn('_super_attribute_option');
    }

    public function it_returns_super_attribute_price_header()
    {
        $this->getHeaderSuperAttributePrice()->shouldReturn('_super_attribute_price_corr');
    }

    public function it_returns_super_product_sku_header()
    {
        $this->getHeaderSuperProductSku()->shouldReturn('_super_products_sku');
    }

    public function it_returns_tax_class_id_header()
    {
        $this->getHeaderTaxClassID()->shouldReturn('tax_class_id');
    }

    public function it_returns_visibility_header()
    {
        $this->getHeaderVisibility()->shouldReturn('visibility');
    }

    public function it_returns_store_header()
    {
        $this->getHeaderStore()->shouldReturn('_store');
    }
}
