// <?php

// namespace Pim\Bundle\MagentoConnectorBundle\Tests\Unit\Processor;

// use Pim\Bundle\MagentoConnectorBundle\Processor\ProductMagentoProcessor;

// /**
//  * Test related class
//  *
//  * @author    Julien Sanchez <julien@akeneo.com>
//  * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
//  * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
//  */
// class ProductMagentoProcessorTest extends \PHPUnit_Framework_TestCase
// {
//     protected $channelManager;
//     protected $magentoSoapClient;
//     protected $processor;

//     /**
//      * {@inheritdoc}
//      */
//     protected function setUp()
//     {
//         $this->channelManager    = $this->getChannelManagerMock();
//         $this->magentoSoapClient = $this->getMock('Pim\Bundle' . 
//             '\MagentoConnectorBundle\Webservice\magentoSoapClient');

//         $this->processor      = new ProductMagentoProcessor(
//             $this->channelManager,
//             $this->magentoSoapClient
//         );
//     }

//     /**
//      * Test instance of current instance tested
//      */
//     public function testInstanceOfMagentoProductProcessor()
//     {
//         $this->assertInstanceOf(
//             'Pim\\Bundle\\MagentoConnectorBundle\\Processor\\ProductMagentoProcessor',
//             $this->processor
//         );
//     }

//     /**
//      * @return PHPUnit_Framework_MockObject_MockObject
//      */
//     protected function getChannelManagerMock()
//     {
//         return $this
//             ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ChannelManager')
//             ->disableOriginalConstructor()
//             ->getMock();
//     }
// }