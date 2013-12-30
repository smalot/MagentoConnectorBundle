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
    public function testGetAllAttributesOptions()
    {
        $magentoSoapClientMock = $this->getConnectedMagentoSoapClientMock();

        $calls = array(
            array(MagentoWebservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST, null, $this->getAttributeSetList()),
            array(MagentoWebservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_LIST, '4',  $this->getAttributeList()),
            array(MagentoWebservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_LIST, '9',  $this->getAttributeList()),
            array(MagentoWebservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_OPTIONS, array('colors'), $this->getOptions('colors')),
            array(MagentoWebservice::SOAP_ACTION_PRODUCT_ATTRIBUTE_OPTIONS, array('size'), $this->getOptions('size')),
        );

        $magentoSoapClientMock->expects($this->exactly(5))
            ->method('call')
            ->will($this->returnValueMap(
                $calls
            ));

        $magentoWebservice = new MagentoWebservice($magentoSoapClientMock);

        $attributesOptions = $magentoWebservice->getAllAttributesOptions();

        $this->assertEquals($attributesOptions, $this->getAllAttributesOptions());
    }

    public function testGetProductsStatus()
    {

    }

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

    protected function getAllAttributesOptions()
    {
        return array(
            'colors' => array(
                '' => '',
                'Blue' => '10',
                'Red' => '11'
            ),
            'size' => array(
                '' => '',
                'L' => '6',
                'M' => '7'
            )
        );
    }
}
