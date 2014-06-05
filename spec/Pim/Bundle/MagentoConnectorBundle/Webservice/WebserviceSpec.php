<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Webservice;

use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\MagentoConnectorBundle\Webservice\AttributeSetNotFoundException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class WebserviceSpec extends ObjectBehavior
{

    protected $attributeSetList =
        [0 =>
            [
                'set_id' => '4',
                'name'   => 'Default'
            ],
        1 =>
            [
                'set_id' => '9',
                'name'   => 'products_set'
            ]
        ];

    protected $attributeList =
        [0 =>
            [
                'attribute_id' => '71',
                'code'         => 'name',
                'type'         => 'select',
                'required'     => '1',
                'scope'        => 'store'
            ],
        1 =>
            [
                'attribute_id' => '72',
                'code'         => 'description',
                'type'         => 'textarea',
                'required'     => '1',
                'scope'        => 'store'
            ]
        ];

    protected $attributeOptionList =
        [1 =>
            [
                'value' => '5',
                'label' => 'blue'
            ],
        2 =>
            [
                'value' => '4',
                'label' => 'green'
            ],
        3 =>
            [
                'value' => '3',
                'label' => 'yellow'
            ]
        ];

    protected $productList =
        [0 =>
            [
                'product_id'   => '1',
                'sku'          => 'n2610',
                'name'         => 'Nokia 2610 Phone',
                'set'          =>  '4',
                'type'         => 'simple',
                'category_ids' => [0 => '4']
            ],
        1 =>
            [
                'product_id'   => '2',
                'sku'          => 'b8100',
                'name'         => 'BlackBerry 8100 Pearl',
                'set'          => '4',
                'type'         => 'simple',
                'category_ids' => [0 => '4']
            ]
        ];

    protected $results =
        [0 =>
            [
                'product_id'   => '1',
                'sku'          => 'n2610',
                'name'         => 'Nokia 2610 Phone',
                'set'          => '4',
                'type'         => 'simple',
                'category_ids' => [0 => '4']
            ],
        1 =>
            [
                'product_id'   => '2',
                'sku'          => 'b8100',
                'name'         => 'BlackBerry 8100 Pearl',
                'set'          => '4',
                'type'         => 'simple',
                'category_ids' => [0 => '4']
            ]
        ];

    protected $storeViewList =
        [0 =>
                [
                    'store_id'   => '1',
                    'code'       => 'default',
                    'website_id' => '1',
                    'group_id'   => '1',
                    'name'       => 'Default Store View',
                    'sort_order' => '0',
                    'is_active'  => '1'
                ],
        1 =>
                [
                    'store_id'   => '2',
                    'code'       => 'english',
                    'website_id' => '2',
                    'group_id'   => '2',
                    'name'       => 'English',
                    'sort_order' => '0',
                    'is_active'  => '1'
                ]
        ];

    protected $productMediaList =
        [0 =>
                [
                    'file'     => '/b/l/blackberry8100_2.jpg',
                    'label'    => '',
                    'position' => '1',
                    'exclude'  => '0',
                    'url'      => 'http://magentopath/blackberry8100_2.jpg',
                    'types'    =>
                        [
                            0  => 'image',
                            1  => 'small_image',
                            2  => 'thumbnail'
                        ]
                ]
        ];

    protected $productPart =
        [
            1,
            '/i/m/image.jpg',
            [
                'file' =>
                    [
                        'content' => '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAIBAQIBAQICAgxEAPwDLoooXP4DCiiigAooooAKKKKAP/Z',
                        'mime' => 'image/jpeg'
                    ],
                'label' => 'New label',
                'position' => '50',
                'types' => ['image'],
                'exclude' => 1
            ]
        ];

    function let(MagentoSoapClient $magentoSoapClient)
    {
        $this->beConstructedWith($magentoSoapClient);
    }

    function it_return_all_attribute_options($magentoSoapClient)
    {
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST)
            ->shouldBeCalled()
            ->willReturn($this->attributeSetList);
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_LIST, 4)
            ->shouldBeCalled()
            ->willReturn($this->attributeList);
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_LIST, 9)
            ->shouldBeCalled()
            ->willReturn($this->attributeList);

        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_ATTRIBUTE_OPTION_LIST, ['name', Webservice::ADMIN_STOREVIEW])
            ->shouldBeCalled()
            ->willReturn($this->attributeOptionList);

        $this->getAllAttributesOptions()->shouldReturn(['name' => ['blue' => '5', 'green' => '4', 'yellow' => '3']]);
    }

    function it_return_product_status($magentoSoapClient, Product $product1, Product $product2)
    {
        $product1->getIdentifier()->shouldBeCalled()->willReturn('n2610');
        $product2->getIdentifier()->shouldBeCalled()->willReturn('b8100');
        $products = [$product1, $product2];

        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_CATALOG_PRODUCT_LIST, Argument::any())
            ->shouldBeCalled()
            ->willReturn($this->productList);

        $this->getProductsStatus($products)->shouldReturn($this->results);
    }

    function it_return_empty_array__if_no_skus_are_found_for_product_status(
        $magentoSoapClient
    ){
        $products = [];

        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_CATALOG_PRODUCT_LIST, Argument::any())
            ->shouldBeCalled()
            ->willReturn([]);

        $this->getProductsStatus($products)->shouldReturn([]);
    }

    function it_return_configurables_status($magentoSoapClient, Group $group1, Group $group2)
    {
        $group1->getCode()->willReturn('n2610');
        $group2->getCode()->willReturn('b8100');

        $configurable1['group'] = $group1;
        $configurable2['group'] = $group2;
        $configurables = [$configurable1, $configurable2];
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_CATALOG_PRODUCT_LIST, Argument::any())
            ->shouldBeCalled()
            ->willReturn($this->productList);

        $this->getConfigurablesStatus($configurables)->shouldReturn($this->results);
    }

    function it_return_attribute_set_id($magentoSoapClient)
    {
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST)
            ->shouldBeCalled()
            ->willReturn($this->attributeSetList);

        $this->getAttributeSetId('Default')->shouldReturn("4");
    }

    function it_throw_an_exception_if_no_code_forAttribute_set_exists($magentoSoapClient)
    {
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST)
            ->shouldBeCalled()
            ->willReturn($this->attributeSetList);

        $this->shouldThrow(
            new AttributeSetNotFoundException(
                'The attribute set for code "foo" was not found on Magento. Please create it before proceed.'
            )
        )->during('getAttributeSetId', ['foo']);
    }

    function it_return_the_store_view_list($magentoSoapClient)
    {
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_STORE_LIST)
            ->shouldBeCalled()
            ->willReturn($this->storeViewList);

        $this->getStoreViewsList()->shouldReturn($this->storeViewList);
    }

    function it_return_images($magentoSoapClient)
    {
        $sku = 'b8100';
        $defaultLocalStore = 'bar';

        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_MEDIA_LIST, [$sku, $defaultLocalStore, 'sku'])
            ->shouldBeCalled()
            ->willReturn($this->productMediaList);

        $this->getImages($sku, $defaultLocalStore)->shouldReturn($this->productMediaList);
    }

    function it_return_empty_array_if_exception_is_thrown($magentoSoapClient)
    {
        $sku = 'b8100';
        $defaultLocalStore = 'bar';

        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_MEDIA_LIST, [$sku, $defaultLocalStore, 'sku'])
            ->shouldBeCalled()
            ->willThrow('\Exception');

        $this->getImages($sku, $defaultLocalStore)->shouldReturn([]);
    }

    function it_send_image($magentoSoapClient)
    {
        $file = [
            'content' => '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAACiiigAooooAKKKKAP/Z',
            'mime' => 'image/jpeg'
        ];

        $images = [$file];

        $magentoSoapClient
            ->addCall(
                [
                    Webservice::SOAP_ACTION_PRODUCT_MEDIA_CREATE,
                    $file
                ]
            )
            ->shouldBeCalled();

        $this->sendImages($images);
    }

    function it_delete_images($magentoSoapClient)
    {
        $sku = 'b8100';
        $imageFilename = 'img';

        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_MEDIA_REMOVE, [$sku, $imageFilename, 'sku'])
            ->shouldBeCalled()
            ->willReturn(true);

        $this->deleteImage($sku, $imageFilename)->shouldReturn(True);
    }

    function it_update_product_part($magentoSoapClient)
    {
        $magentoSoapClient
            ->addCall([Webservice::SOAP_ACTION_CATALOG_PRODUCT_UPDATE, $this->productPart])
            ->shouldBeCalled();

        $this->updateProductPart($this->productPart);
    }

    function it_update_product($magentoSoapClient)
    {
        $magentoSoapClient
            ->addCall([Webservice::SOAP_ACTION_CATALOG_PRODUCT_UPDATE, $this->productPart])
            ->shouldBeCalled();

        $this->sendProduct($this->productPart);
    }

    function it_create_product($magentoSoapClient)
    {
        $product = array_merge($this->productPart, ['', '']);
        $magentoSoapClient
            ->addCall([Webservice::SOAP_ACTION_CATALOG_PRODUCT_CREATE, $product])
            ->shouldBeCalled();

        $this->sendProduct($product);
    }

    function it_create_product_configurable($magentoSoapClient)
    {
        $product = array_merge($this->productPart, ['']);
        $magentoSoapClient
            ->addCall([Webservice::SOAP_ACTION_CATALOG_PRODUCT_CREATE, $product])
            ->shouldBeCalled();

        $this->sendProduct($product);
    }

    function it_disable_category($magentoSoapClient)
    {
        $categoryId = 1;

        $magentoSoapClient
            ->call(
                Webservice::SOAP_ACTION_CATEGORY_UPDATE,
                [
                    $categoryId,
                    [
                        'is_active'         => 0,
                        'available_sort_by' => 1,
                        'default_sort_by'   => 1
                    ]
                ]
            )
            ->shouldBeCalled()
            ->willReturn(true);

        $this->disableCategory($categoryId)->shouldReturn(true);
    }

    function it_delete_category($magentoSoapClient)
    {
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_CATEGORY_DELETE, [3])
            ->shouldBeCalled()
            ->willReturn(true);

        $this->deleteCategory(3)->shouldReturn(true);
    }

    function it_creates_option($magentoSoapClient)
    {
        $option = [
            "title" => "Custom Text Field Option Title",
            "type" => "field",
            "is_require" => 1,
            "sort_order" => 0,
            "additional_fields" => [
                [
                    "price" => 10.00,
                    "price_type" => "fixed",
                    "sku" => "custom_text_option_sku",
                    "max_characters" => 255
                ]
            ]
        ];
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_ATTRIBUTE_OPTION_ADD, $option)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->createOption($option)->shouldReturn(true);
    }

    function it_creates_attribute($magentoSoapClient)
    {
        $attribute = [
            "scope" => "global",
            "default_value" => "100",
            "frontend_input" => "text",
            "is_unique" => 0,
            "is_required" => 0,
            "is_configurable" => 0,
            "is_searchable" => 0,
            "is_visible_in_advanced_search" => 0,
            "used_in_product_listing" => 0,
            "additional_fields" => [
                "is_filterable" => 1,
                "is_filterable_in_search" => 1,
                "position" => 1,
                "used_for_sort_by" => 1
            ],
            "frontend_label" => [
                [
                    "store_id" => 0,
                    "label" => "Updated attribute"
                ]
            ]
        ];
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_ATTRIBUTE_CREATE, [$attribute])
            ->shouldBeCalled()
            ->willReturn(5);

        $this->createAttribute($attribute)->shouldReturn(5);
    }

    function it_updates_attribute($magentoSoapClient)
    {
        $attribute = [
            "scope" => "global",
            "default_value" => "100",
            "frontend_input" => "text",
            "is_unique" => 0,
            "is_required" => 0,
            "is_configurable" => 0,
            "is_searchable" => 0,
            "is_visible_in_advanced_search" => 0,
            "used_in_product_listing" => 0,
            "additional_fields" => [
                "is_filterable" => 1,
                "is_filterable_in_search" => 1,
                "position" => 1,
                "used_for_sort_by" => 1
            ],
            "frontend_label" => [
                [
                    "store_id" => 0,
                    "label" => "Updated attribute"
                ]
            ]
        ];
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_ATTRIBUTE_UPDATE, $attribute)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->updateAttribute($attribute)->shouldReturn(true);
    }

    function it_deletes_attribute($magentoSoapClient)
    {
        $attributeCode = 'foo';

        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_ATTRIBUTE_REMOVE, $attributeCode)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->deleteAttribute($attributeCode)->shouldReturn(true);
    }

    function it_deletes_option($magentoSoapClient)
    {
        $attributeCode = 'foo';
        $optionId = 5;

        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_ATTRIBUTE_OPTION_REMOVE, [$attributeCode, $optionId])
            ->shouldBeCalled()
            ->willReturn(true);

        $this->deleteOption($optionId, $attributeCode)->shouldReturn(true);
    }

    function it_adds_attribute_to_attribute_set($magentoSoapClient)
    {
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_ATTRIBUTE_ADD, [4, 18, null, false])
            ->shouldBeCalled()
            ->willReturn(true);

        $this->addAttributeToAttributeSet(4, 18)->shouldReturn(true);
    }

    function it_adds_attribute_to_attribute_set_in_group($magentoSoapClient)
    {
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_ATTRIBUTE_ADD, [4, 18, 11, false])
            ->shouldBeCalled()
            ->willReturn(true);

        $this->addAttributeToAttributeSet(4, 18, 11)->shouldReturn(true);
    }

    function it_adds_attribute_to_attribute_set_sort_enabled($magentoSoapClient)
    {
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_ATTRIBUTE_ADD, [4, 18, null, true])
            ->shouldBeCalled()
            ->willReturn(true);

        $this->addAttributeToAttributeSet(4, 18, null, true)->shouldReturn(true);
    }

    function it_removes_attribute_from_attribute_set($magentoSoapClient)
    {
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_ATTRIBUTE_REMOVE, [4, 18])
            ->shouldBeCalled()
            ->willReturn(true);

        $this->removeAttributeFromAttributeSet(4, 18)->shouldReturn(true);
    }

    function it_creates_attribute_set($magentoSoapClient)
    {
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_CREATE, ['foo', 4])
            ->shouldBeCalled()
            ->willReturn(58);

        $this->createAttributeSet('foo')->shouldReturn(58);
    }

    function it_creates_attribute_set_with_skeleton($magentoSoapClient)
    {
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_CREATE, ['foo', 2])
            ->shouldBeCalled()
            ->willReturn(58);

        $this->createAttributeSet('foo', 2)->shouldReturn(58);
    }

    function it_adds_a_group_to_an_attribute_set($magentoSoapClient)
    {
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_GROUP_ADD, [4, 'my_group_name'])
            ->shouldBeCalled()
            ->willReturn(1258);

        $this->addAttributeGroupToAttributeSet(4, 'my_group_name')->shouldReturn(1258);
    }

    function it_renames_a_group_in_attribute_set($magentoSoapClient)
    {
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_GROUP_RENAME, [4, 'my_group_new_name'])
            ->shouldBeCalled()
            ->willReturn(true);

        $this->renameAttributeGroupInAttributeSet(4, 'my_group_new_name')->shouldReturn(true);
    }

    function it_remove_an_attribute_set($magentoSoapClient)
    {
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_REMOVE, [15, null])
            ->shouldBeCalled()
            ->willReturn(true);

        $this->removeAttributeSet(15)->shouldReturn(true);
    }

    function it_remove_an_attribute_set_with_force($magentoSoapClient)
    {
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_REMOVE, [15, true])
            ->shouldBeCalled()
            ->willReturn(true);

        $this->removeAttributeSet(15, true)->shouldReturn(true);
    }

    function it_removes_a_group_to_an_attribute_set($magentoSoapClient)
    {
        $magentoSoapClient
            ->call(Webservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_GROUP_REMOVE, [4])
            ->shouldBeCalled()
            ->willReturn(true);

        $this->removeAttributeGroupFromAttributeSet(4)->shouldReturn(true);
    }

    function it_calls_soap_client_to_send_new_category($magentoSoapClient)
    {
        $magentoSoapClient->call(
            Webservice::SOAP_ACTION_CATEGORY_CREATE,
            ['foo']
        )->willReturn(12);

        $this->sendNewCategory(['foo'])->shouldReturn(12);
    }

    function it_calls_soap_client_to_send_category_update($magentoSoapClient)
    {
        $magentoSoapClient->call(
            Webservice::SOAP_ACTION_CATEGORY_UPDATE,
            ['foo']
        )->shouldBeCalled();

        $this->sendUpdateCategory(['foo']);
    }

    function it_calls_soap_client_to_send_category_move($magentoSoapClient)
    {
        $magentoSoapClient->call(
            Webservice::SOAP_ACTION_CATEGORY_MOVE,
            ['foo']
        )->shouldBeCalled();

        $this->sendMoveCategory(['foo']);
    }

    function it_calls_soap_client_to_get_categories_status($magentoSoapClient)
    {
        $tree = [
            'category_id' => 1,
            'children' => [
                [
                    'category_id' => 3,
                    'children' => []
                ]
            ]
        ];

        $flattenTree = [
            1 => [
                'category_id' => 1,
                'children' => [
                    [
                        'category_id' => 3,
                        'children' => []
                    ]
                ]
            ],
            3 => [
                'category_id' => 3,
                'children' => []
            ]
        ];

        $magentoSoapClient->call(
            Webservice::SOAP_ACTION_CATEGORY_TREE
        )->willReturn($tree);

        $this->getCategoriesStatus()->shouldReturn($flattenTree);
    }

    function it_gets_association_status_for_a_given_product($magentoSoapClient, ProductInterface $product)
    {
        $magentoSoapClient->call('catalog_product_link.list', ['up_sell', 'sku-012', 'sku'])->willReturn('up_sell');
        $magentoSoapClient->call('catalog_product_link.list', ['cross_sell', 'sku-012', 'sku'])->willReturn('cross_sell');
        $magentoSoapClient->call('catalog_product_link.list', ['related', 'sku-012', 'sku'])->willReturn('related');
        $magentoSoapClient->call('catalog_product_link.list', ['grouped', 'sku-012', 'sku'])->willReturn('grouped');

        $product->getIdentifier()->willReturn('sku-012');

        $this->getAssociationsStatus($product)->shouldReturn(
            [
                'up_sell'    => 'up_sell',
                'cross_sell' => 'cross_sell',
                'related'    => 'related',
                'grouped'    => 'grouped'
            ]
        );
    }

    function it_send_remove_call($magentoSoapClient)
    {
        $magentoSoapClient->call('catalog_product_link.remove', ['foo'])->shouldBeCalled();

        $this->removeProductAssociation(['foo']);
    }

    function it_send_create_call($magentoSoapClient)
    {
        $magentoSoapClient->call('catalog_product_link.assign', ['bar'])->shouldBeCalled();

        $this->createProductAssociation(['bar']);
    }

    function it_send_delete_call($magentoSoapClient)
    {
        $magentoSoapClient->call('catalog_product.delete', ['sku-000'])->shouldBeCalled();

        $this->deleteProduct('sku-000');
    }

    function it_send_disable_call($magentoSoapClient)
    {
        $magentoSoapClient->call('catalog_product.update', ['sku-001', ['status' => 2]])->shouldBeCalled();

        $this->disableProduct('sku-001');
    }
}
