<?php

namespace Pim\Bundle\MagentoConnectorBundle\Tests\Unit\Webservice;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;

/**
 * Test related class
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClientTest extends \PHPUnit_Framework_TestCase
{
    const LOGIN                   = 'login';
    const PASSWORD                = 'password';
    const URL                     = 'url';
    const BAD_ATTRIBUTE_SET_CODE  = 'bad';
    const GOOD_ATTRIBUTE_SET_CODE = 'good';
    const NAME                    = 'name';
    const SIZE                    = 'size';
    const SET_ID                  = 'set_id';
    const STORE_VIEW              = 'admin';
    const SKU                     = 'sku-000';
    const IMAGE_FILENAME          = 'test.jpg';

    /**
     * @var MagentoSoapClient
     */
    private $magentoSoapClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->magentoSoapClient = new MagentoSoapClient();
        $this->mockSoapClient = $this->getMockBuilder('\SoapClient')
            ->disableOriginalConstructor()
            ->setMethods(array('login', 'call', 'multiCall'))
            ->getMock();
        $this->clientParameters = new MagentoSoapClientParameters(self::LOGIN, self::PASSWORD, self::URL);
    }

    public function testIsConnectedInstanciated()
    {
        $this->assertFalse($this->magentoSoapClient->isConnected());
    }

    /**
     * @expectedException Pim\Bundle\MagentoConnectorBundle\Webservice\ConnectionErrorException
     */
    public function testConnectClientInstanciated()
    {
        $this->magentoSoapClient->connect();
    }

    /**
     * @expectedException Pim\Bundle\MagentoConnectorBundle\Webservice\ConnectionErrorException
     */
    public function testConnectClientWithoutParameters()
    {
        $this->magentoSoapClient->connect();
    }

    /**
     * @expectedException Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException
     */
    public function testConnectClientBadCredentials()
    {
        $this->mockSoapClient
            ->expects($this->once())
            ->method('login')
            ->with(
                self::LOGIN,
                self::PASSWORD
            )
            ->will($this->throwException(new \Exception('Bad credentials')));

        $this->magentoSoapClient->setParameters($this->clientParameters);
        $this->magentoSoapClient->setClient($this->mockSoapClient);

        $this->magentoSoapClient->connect();
    }

    public function testConnectClient()
    {
        $this->connectClient();
    }

    /**
     * @expectedException Pim\Bundle\MagentoConnectorBundle\Webservice\AttributeSetNotFoundException
     */
    public function testGetAttributeSetIdAttributeSetUnknow()
    {
        $this->connectClient();

        $this->mockSoapClient
            ->expects($this->once())
            ->method('call')
            ->with(
                true,
                MagentoSoapClient::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST,
                null
            )
            ->will($this->returnValue(
                array(
                    array(
                        self::NAME   => self::GOOD_ATTRIBUTE_SET_CODE,
                        self::SET_ID => 1
                    )
                )
            ));

        $this->magentoSoapClient->getAttributeSetId(self::BAD_ATTRIBUTE_SET_CODE);
    }

    public function testGetAllAttributesOptions()
    {
        $this->connectClient();

        $attributeSetList = array(
            self::GOOD_ATTRIBUTE_SET_CODE => array(
                self::NAME   => self::GOOD_ATTRIBUTE_SET_CODE,
                self::SET_ID => 1
            )
        );

        $attributeList = array(
            array(
                'code' => self::NAME,
                'type' => 'text'
            ),
            array(
                'code' => self::SIZE,
                'type' => 'select'
            )
        );

        $optionList = array(
            array(
                'label' => 'Red',
                'value' => '3'
            )
        );

        $values = array(
            array(true, MagentoSoapClient::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST, null, $attributeSetList),
            array(true, MagentoSoapClient::SOAP_ACTION_PRODUCT_ATTRIBUTE_LIST, 1, $attributeList),
            array(true, MagentoSoapClient::SOAP_ACTION_PRODUCT_ATTRIBUTE_OPTIONS, array('size'), $optionList)
        );

        $this->mockSoapClient
            ->expects($this->any())
            ->method('call')
            ->will($this->returnValueMap($values));

        $this->magentoSoapClient->getAllAttributesOptions();
    }

    public function testGetProductStatus()
    {
        $this->connectClient();

        $condition        = new \StdClass();
        $condition->key   = 'in';
        $condition->value = '1,2';

        $fieldFilter        = new \StdClass();
        $fieldFilter->key   = 'sku';
        $fieldFilter->value = $condition;

        $filters = new \StdClass();
        $filters->complex_filter = array(
            $fieldFilter
        );

        $product1 = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
        $product1->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue(1));
        $product2 = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
        $product2->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue(2));

        $products = array($product1, $product2);

        $this->mockSoapClient
            ->expects($this->once())
            ->method('call')
            ->with(
                true,
                MagentoSoapClient::SOAP_ACTION_CATALOG_PRODUCT_LIST,
                $filters
            );

        $this->magentoSoapClient->getProductsStatus($products);
    }

    public function testgetAttributeSetId()
    {
        $this->connectClient();

        $this->mockSoapClient
            ->expects($this->once())
            ->method('call')
            ->with(
                true,
                MagentoSoapClient::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST,
                null
            )
            ->will($this->returnValue(
                array(
                    array(
                        self::NAME   => self::GOOD_ATTRIBUTE_SET_CODE,
                        self::SET_ID => 1
                    )
                )
            ));

        $this->magentoSoapClient->getAttributeSetId(self::GOOD_ATTRIBUTE_SET_CODE);
    }

    /**
     * @expectedException Pim\Bundle\MagentoConnectorBundle\Webservice\NotConnectedException
     */
    public function testSendCallsInstanciated()
    {
        $this->magentoSoapClient->addCall(array());

        $this->magentoSoapClient->sendCalls();
    }

    public function testGetImages()
    {
        $this->connectClient();

        $this->mockSoapClient->expects($this->once())
            ->method('call')
            ->with(true, MagentoSoapClient::SOAP_ACTION_PRODUCT_MEDIA_LIST, self::SKU)
            ->will($this->returnValue(array()));

        $this->magentoSoapClient->getImages(self::SKU);
    }

    public function testGetImagesException()
    {
        $this->connectClient();

        $this->mockSoapClient->expects($this->once())
            ->method('call')
            ->with(true, MagentoSoapClient::SOAP_ACTION_PRODUCT_MEDIA_LIST, self::SKU)
            ->will($this->throwException(new \Exception()));

        $this->assertEquals($this->magentoSoapClient->getImages(self::SKU), array());
    }

    public function testDeleteImage()
    {
        $this->connectClient();

        $this->mockSoapClient->expects($this->once())
            ->method('call')
            ->with(true, MagentoSoapClient::SOAP_ACTION_PRODUCT_MEDIA_REMOVE, array(
                'product' => self::SKU,
                'file'    => self::IMAGE_FILENAME
            ))
            ->will($this->returnValue(self::IMAGE_FILENAME));

        $this->assertEquals($this->magentoSoapClient->deleteImage(self::SKU, self::IMAGE_FILENAME), self::IMAGE_FILENAME);
    }

    public function testSendCalls()
    {
        $this->connectClient();
        $this->magentoSoapClient->addCall(array());

        $this->mockSoapClient
            ->expects($this->once())
            ->method('multiCall')
            ->with(
                true,
                array(
                    array()
                )
            )
            ->will($this->returnValue(array()));

        $this->magentoSoapClient->sendCalls();
    }

    /**
     * @expectedException Pim\Bundle\MagentoConnectorBundle\Webservice\NotConnectedException
     */
    public function testCallInstanciated()
    {
        $this->magentoSoapClient->call(MagentoSoapClient::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST);
    }

    private function connectClient()
    {
        $this->mockSoapClient
            ->expects($this->once())
            ->method('login')
            ->with(
                self::LOGIN,
                self::PASSWORD
            )->will($this->returnValue(true));

        $this->magentoSoapClient->setParameters($this->clientParameters);
        $this->magentoSoapClient->setClient($this->mockSoapClient);

        $this->magentoSoapClient->connect();
    }

    public function testGetStoreViewsList()
    {
        $this->getAttributeListSoapClientMock();

        $this->magentoSoapClient->getStoreViewsList();
    }

    public function testGetStoreViewsListAllreadyCalled()
    {
        $this->getAttributeListSoapClientMock();

        $this->magentoSoapClient->getStoreViewsList();
        $this->magentoSoapClient->getStoreViewsList();
    }

    private function getAttributeListSoapClientMock()
    {
        $this->connectClient();

        $expectedResult = array(
            array(
                'store_id'   => '1',
                'code'       => 'default',
                'website_id' => '1',
                'group_id'   => '1',
                'name'       => 'Default Store View',
                'sort_order' => '0',
                'is_active'  => '1',
            ),
            array(
                'store_id'   => '2',
                'code'       => 'en_us',
                'website_id' => '1',
                'group_id'   => '1',
                'name'       => 'en_US',
                'sort_order' => '0',
                'is_active'  => '1',
            ),
            array(
                'store_id'   => '3',
                'code'       => 'fr_fr',
                'website_id' => '1',
                'group_id'   => '1',
                'name'       => 'fr_FR',
                'sort_order' => '0',
                'is_active'  => '1',
            )
        );

        $this->mockSoapClient
            ->expects($this->once())
            ->method('call')
            ->with(
                true,
                MagentoSoapClient::SOAP_ACTION_STORE_LIST,
                null
            )
            ->will($this->returnValue(
                $expectedResult
            ));
    }
}