<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Model\Media;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductValueNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\AssociationTypeManager;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductNormalizerSpec extends ObjectBehavior
{
    protected $globalContext = array();

    function let(
        ChannelManager $channelManager,
        MediaManager $mediaManager,
        ProductValueNormalizer $productValueNormalizer,
        CategoryMappingManager $categoryMappingManager,
        AssociationTypeManager $associationTypeManager,
        MappingCollection $storeViewMapping,
        MappingCollection $categoryMapping,
        MappingCollection $attributeMapping,
        Product $product,
        ProductValue $productValue,
        ProductValue $imageValue,
        Channel $channel,
        Locale $localeFR,
        Locale $localeEN,
        Category $category
    ) {
        $this->beConstructedWith(
            $channelManager,
            $mediaManager,
            $productValueNormalizer,
            $categoryMappingManager,
            $associationTypeManager,
            1,
            4,
            'currency',
            'magento_url'
        );

        $this->globalContext = array(
            'attributeSetId'           => 0,
            'magentoAttributes'        => array(),
            'magentoAttributesOptions' => array(),
            'storeViewMapping'         => $storeViewMapping,
            'magentoStoreViews'        => array(array('code' => 'fr_fr')),
            'defaultLocale'            => 'default_locale',
            'website'                  => 'website',
            'channel'                  => 'channel',
            'categoryMapping'          => $categoryMapping,
            'attributeMapping'         => $attributeMapping,
            'create'                   => true,
            'pimGrouped'               => 'pim_grouped',
            'created_date'             => (new \DateTime()),
            'updated_date'             => (new \DateTime())
        );

        $attributeMapping->getTarget('visibility')->willReturn('visibility');
        $attributeMapping->getTarget('created_at')->willReturn('created_at');
        $attributeMapping->getTarget('updated_at')->willReturn('updated_at');
        $attributeMapping->getTarget('status')->willReturn('status');
        $attributeMapping->getTarget('categories')->willReturn('categories');

        $channelManager->getChannelByCode('channel')->willReturn($channel);
        $channel->getLocales()->willReturn(array($localeEN, $localeFR));
        $localeEN->getCode()->willReturn('default_locale');
        $localeFR->getCode()->willReturn('fr_FR');
        $channel->getCode()->willReturn('channel_code');

        $product->getCategories()->willReturn(array($category));
        $product->getIdentifier()->willReturn('sku-000');
        $product->getCreated()->willReturn($this->globalContext['created_date']);
        $product->getUpdated()->willReturn($this->globalContext['updated_date']);
        $product->getValues()->willReturn(new ArrayCollection(array($productValue, $imageValue)));
        $storeViewMapping->getTarget('default_locale')->willReturn('default_locale');
        $storeViewMapping->getTarget('fr_FR')->willReturn('fr_fr');

        $categoryMappingManager->getIdFromCategory($category, 'magento_url', $categoryMapping)->willReturn(2);

        $productValueNormalizer->normalize($productValue, Argument::cetera())->willReturn(array('value' => 'productValueNormalized'));
        $productValueNormalizer->normalize($imageValue, Argument::cetera())->willReturn(null);
    }

    function it_normalizes_the_given_new_product($product)
    {
        $this->normalize($product, 'MagentoArray', $this->globalContext)->shouldReturn(array(
            'default' => array(
                'simple',
                0,
                'sku-000',
                array(
                    'categories' => array(2),
                    'created_at' => $this->globalContext['created_date']->format('Y-m-d H:i:s'),
                    'status'     => 1,
                    'updated_at' => $this->globalContext['updated_date']->format('Y-m-d H:i:s'),
                    'value'      => 'productValueNormalized',
                    'visibility' => 4,
                    'websites'   => array('website'),
                ),
                'default'
            ),
            'fr_fr'  => array(
                'sku-000',
                array(
                    'categories' => array(2),
                    'created_at' => $this->globalContext['created_date']->format('Y-m-d H:i:s'),
                    'status'     => 1,
                    'updated_at' => $this->globalContext['updated_date']->format('Y-m-d H:i:s'),
                    'value'      => 'productValueNormalized',
                    'visibility' => 4,
                ),
                'fr_fr',
                'sku'
            )
        ));
    }

    function it_raises_an_exception_if_product_category_is_not_found($product, $categoryMappingManager, $category, $categoryMapping)
    {
        $categoryMappingManager->getIdFromCategory($category, 'magento_url', $categoryMapping)->willReturn(null);

        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\CategoryNotFoundException')->during('normalize', array($product, 'MagentoArray', $this->globalContext));
    }

    function it_normalizes_the_given_grouped_product($product, $associationTypeManager, AssociationType $associationType)
    {
        $associationTypeManager->getAssociationTypeByCode('pim_grouped')->willReturn($associationType);
        $product->getAssociationForType($associationType)->willReturn(new ArrayCollection(array('association')));

        $this->normalize($product, 'MagentoArray', $this->globalContext)->shouldReturn(array(
            'default' => array(
                'grouped',
                0,
                'sku-000',
                array(
                    'categories' => array(2),
                    'created_at' => $this->globalContext['created_date']->format('Y-m-d H:i:s'),
                    'status'     => 1,
                    'updated_at' => $this->globalContext['updated_date']->format('Y-m-d H:i:s'),
                    'value'      => 'productValueNormalized',
                    'visibility' => 4,
                    'websites'   => array('website'),
                ),
                'default'
            ),
            'fr_fr'  => array(
                'sku-000',
                array(
                    'categories' => array(2),
                    'created_at' => $this->globalContext['created_date']->format('Y-m-d H:i:s'),
                    'status'     => 1,
                    'updated_at' => $this->globalContext['updated_date']->format('Y-m-d H:i:s'),
                    'value'      => 'productValueNormalized',
                    'visibility' => 4,
                ),
                'fr_fr',
                'sku'
            )
        ));
    }

    function it_raises_an_exception_if_a_storeview_is_missing($product)
    {
        $this->globalContext['magentoStoreViews'] = array();
        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\LocaleNotMatchedException')->during('normalize', array($product, 'MagentoArray', $this->globalContext));
    }



    function it_normalizes_images_for_given_product($product, $imageValue, Media $image, ArrayCollection $productValues, $mediaManager)
    {
        $product->getValues()->willReturn($productValues);
        $productValues->filter(Argument::any())->willReturn(array($imageValue));
        $imageValue->getData()->willReturn($image);

        $mediaManager->getBase64($image)->willReturn('image_data');

        $image->getFilename()->willReturn('image_filename');
        $image->getMimeType()->willReturn('jpeg');

        $this->getNormalizedImages($product)->shouldReturn(array(
            array(
                'sku-000',
                array(
                    'file' => array(
                        'name'    => 'image_filename',
                        'content' => 'image_data',
                        'mime'    => 'jpeg',
                    ),
                    'label'    => 'image_filename',
                    'position' => 0,
                    'types'    => array('small_image', 'image', 'thumbnail'),
                    'exclude'  => 0
                ),
                0,
                'sku'
            )
        ));
    }

    function it_normalizes_the_given_updated_product($product)
    {
        $this->globalContext['create'] = false;

        $this->normalize($product, 'MagentoArray', $this->globalContext)->shouldReturn(array(
            'default' => array(
                'sku-000',
                array(
                    'categories' => array(2),
                    'created_at' => $this->globalContext['created_date']->format('Y-m-d H:i:s'),
                    'status'     => 1,
                    'updated_at' => $this->globalContext['updated_date']->format('Y-m-d H:i:s'),
                    'value'      => 'productValueNormalized',
                    'visibility' => 4,
                    'websites'   => array('website'),
                ),
                'default',
                'sku'
            ),
            'fr_fr'  => array(
                'sku-000',
                array(
                    'categories' => array(2),
                    'created_at' => $this->globalContext['created_date']->format('Y-m-d H:i:s'),
                    'status'     => 1,
                    'updated_at' => $this->globalContext['updated_date']->format('Y-m-d H:i:s'),
                    'value'      => 'productValueNormalized',
                    'visibility' => 4,
                ),
                'fr_fr',
                'sku'
            )
        ));
    }
}
