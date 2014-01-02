<?php

namespace Pim\Bundle\MagentoConnectorBundle\Tests\Unit\Webservice;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebservice;

/**
 * Test related class
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoWebserviceTest extends WebserviceTestCase
{
    /**
     * Test the corresponding method
     */
    public function testGetAllAttributesOptions()
    {
        $calls = array(
            array(MagentoWebservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST, null, $this->getAttributeSetList()),
            array(MagentoWebservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_LIST, '4',  $this->getAttributeList()),
            array(MagentoWebservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_LIST, '9',  $this->getAttributeList()),
            array(MagentoWebservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_OPTIONS, array('colors'), $this->getOptions('colors')),
            array(MagentoWebservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_OPTIONS, array('size'), $this->getOptions('size')),
        );

        $magentoWebservice = $this->getMagentoWebserviceWithCallMap($calls);

        $attributesOptions = $magentoWebservice->getAllAttributesOptions();

        $this->assertEquals($attributesOptions, $this->getAllAttributesOptions());
    }

    /**
     * Test the corresponding method
     */
    public function testGetProductsStatus()
    {
        $magentoSoapClientMock = $this->getConnectedMagentoSoapClientMock();

        $calls = array(
            array(MagentoWebservice::SOAP_ACTION_CATALOG_PRODUCT_LIST, null, $this->getProductFilters()),
        );

        $magentoWebservice = $this->getMagentoWebserviceWithCallMap($calls);

        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');

        $product->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue('sku-000'));

        $result = $magentoWebservice->getProductsStatus(array($product));
    }

    /**
     * @expectedException Pim\Bundle\MagentoConnectorBundle\Webservice\AttributeSetNotFoundException
     */
    public function testGetAttributeSetId()
    {
        $calls = array(
            array(MagentoWebservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST, null, $this->getAttributeSetList()),
        );

        $magentoWebservice = $this->getMagentoWebserviceWithCallMap($calls);

        $magentoWebservice->getAttributeSetId('shoe');
    }

    /**
     * Test the corresponding method
     */
    public function testGetStoreViewsList()
    {
        $calls = array(
            array(MagentoWebservice::SOAP_ACTION_STORE_LIST, null, $this->getStoreViewsList()),
        );

        $magentoWebservice = $this->getMagentoWebserviceWithCallMap($calls);
        $storeViewsList    = $magentoWebservice->getStoreViewsList();

        $this->assertEquals($storeViewsList, $this->getStoreViewsList());
    }

    /**
     * Test the corresponding method
     */
    public function testGetImages()
    {
        $magentoSoapClientMock = $this->getConnectedMagentoSoapClientMock();

        $calls = array(
            array(MagentoWebservice::SOAP_ACTION_PRODUCT_MEDIA_LIST, 'sku-000', array('image')),
        );

        $magentoWebservice = $this->getMagentoWebserviceWithCallMap($calls);

        $images = $magentoWebservice->getImages('sku-000');

        $this->assertEquals($images, array('image'));
    }

    /**
     * Test the corresponding method with an unknown sku
     */
    public function testGetImagesUnknownSku()
    {
        $magentoSoapClientMock = $this->getConnectedMagentoSoapClientMock();

        $magentoSoapClientMock->expects($this->once())
            ->method('call')
            ->with(MagentoWebservice::SOAP_ACTION_PRODUCT_MEDIA_LIST, 'sku-000')
            ->will($this->throwException(new \SoapFault('100', 'Product not found')));

        $magentoWebservice = new MagentoWebservice($magentoSoapClientMock);

        $images = $magentoWebservice->getImages('sku-000');

        $this->assertEquals($images, array());
    }

    /**
     * Test the corresponding method
     */
    public function testSendImages()
    {
        $magentoSoapClientMock = $this->getConnectedMagentoSoapClientMock();

        $magentoSoapClientMock->expects($this->once())
            ->method('addCall')
            ->with(array(MagentoWebservice::SOAP_ACTION_PRODUCT_MEDIA_CREATE, array('image')), 1);

        $magentoWebservice = new MagentoWebservice($magentoSoapClientMock);

        $magentoWebservice->sendImages(array(array('image')));
    }

    /**
     * Test the corresponding method
     */
    public function testUpdateProductPart()
    {
        $magentoSoapClientMock = $this->getConnectedMagentoSoapClientMock();

        $magentoSoapClientMock->expects($this->once())
            ->method('addCall')
            ->with(array(MagentoWebservice::SOAP_ACTION_CATALOG_PRODUCT_UPDATE, array('productPart')), 1);

        $magentoWebservice = new MagentoWebservice($magentoSoapClientMock);

        $magentoWebservice->updateProductPart(array('productPart'));
    }

    /**
     * Test the corresponding method
     */
    public function testSendProductCreate()
    {
        $magentoSoapClientMock = $this->getConnectedMagentoSoapClientMock();

        $productPart = array(
            'productPart',
            'test',
            'create',
            'product',
            'test'
        );

        $magentoSoapClientMock->expects($this->once())
            ->method('addCall')
            ->with(array(MagentoWebservice::SOAP_ACTION_CATALOG_PRODUCT_CREATE, $productPart), 1);

        $magentoWebservice = new MagentoWebservice($magentoSoapClientMock);

        $magentoWebservice->sendProduct($productPart);
    }

    /**
     * Test the corresponding method
     */
    public function testSendProductUpdate()
    {
        $magentoSoapClientMock = $this->getConnectedMagentoSoapClientMock();

        $productPart = array(
            'productPart',
            'test',
            'update'
        );

        $magentoSoapClientMock->expects($this->once())
            ->method('addCall')
            ->with(array(MagentoWebservice::SOAP_ACTION_CATALOG_PRODUCT_UPDATE, $productPart), 1);

        $magentoWebservice = new MagentoWebservice($magentoSoapClientMock);

        $magentoWebservice->sendProduct($productPart);
    }

    /**
     * Test the corresponding method
     */
    public function testDeleteImage()
    {
        $magentoSoapClientMock = $this->getConnectedMagentoSoapClientMock();

        $calls = array(
            array(MagentoWebservice::SOAP_ACTION_PRODUCT_MEDIA_REMOVE, array(
                'product' => 'sku-000',
                'file'    => 'filename'
            ), true),
        );

        $magentoWebservice = $this->getMagentoWebserviceWithCallMap($calls);

        $magentoWebservice->deleteImage('sku-000', 'filename');
    }

    /**
     * Get a MagentoWebservice with the given call map
     * @param  array $callsMap
     * @param  bool $expects   specify manualy the expect count (array length otherwise)
     * @return MagentoWebservice
     */
    protected function getMagentoWebserviceWithCallMap($callsMap, $expects = null)
    {
        $magentoSoapClientMock = $this->getConnectedMagentoSoapClientMock();

        if ($expects === null) {
            $expects = count($callsMap);
        }

        $magentoSoapClientMock->expects($this->exactly($expects))
            ->method('call')
            ->will($this->returnValueMap(
                $callsMap
            ));

        return new MagentoWebservice($magentoSoapClientMock);
    }

    /**
     * Get an attribute set list sample
     */
    protected function getAttributeSetList()
    {
        return array(
            array(
                'set_id' => '4',
                'name'   => 'Default'
            ),
            array(
                'set_id' => '9',
                'name'   => 'shirt'
            )
        );
    }

    /**
     * Get an attribute list
     */
    protected function getAttributeList()
    {
        return array(
            array(
                'attribute_id' => '71',
                'code'         => 'name',
                'type'         => 'text',
                'required'     => '1',
                'scope'        => 'store',
            ),
            array(
                'attribute_id' => '90',
                'code'         => 'colors',
                'type'         => MagentoWebservice::MULTI_SELECT,
                'required'     => '1',
                'scope'        => 'store',
            ),
            array(
                'attribute_id' => '91',
                'code'         => 'size',
                'type'         => MagentoWebservice::SELECT,
                'required'     => '1',
                'scope'        => 'store',
            )
        );
    }

    /**
     * Get options for the given attributeCode
     * @param  string $attributeCode
     * @return array
     */
    protected function getOptions($attributeCode)
    {
        $options = array(
            'colors' => array(
                array('label' => '',     'value' => ''),
                array('label' => 'Blue', 'value' => '10'),
                array('label' => 'Red',  'value' => '11')
            ),
            'size' =>  array(
                array('label' => '',  'value' => ''),
                array('label' => 'L', 'value' => '6'),
                array('label' => 'M', 'value' => '7')
            )
        );

        return $options[$attributeCode];
    }

    /**
     * Get all attributes options (from Magento)
     * @return array
     */
    protected function getAllAttributesOptions()
    {
        return array(
            'colors' => array(
                '' => '',
                'Blue' => '10',
                'Red'  => '11'
            ),
            'size' => array(
                ''  => '',
                'L' => '6',
                'M' => '7'
            )
        );
    }

    /**
     * Get a product filter sample
     * @return StdClass
     */
    protected function getProductFilters()
    {
        $condition        = new \StdClass();
        $condition->key   = 'in';
        $condition->value = 'sku-000';

        $fieldFilter        = new \StdClass();
        $fieldFilter->key   = 'sku';
        $fieldFilter->value = $condition;

        $filters = new \StdClass();
        $filters->complex_filter = array(
            $fieldFilter
        );

        return $filters;
    }

    /**
     * Get a storeview list
     * @return array
     */
    protected function getStoreViewsList()
    {
        return array(
            array(
                'store_id'   => '1',
                'code'       => 'default',
                'website_id' => '1',
                'group_id'   => '1',
                'name'       => 'Default Store View',
                'sort_order' => '0',
                'is_active'  => '1'
            )
        );
    }
}
