<?php

namespace Pim\Bundle\MagentoConnectorBundle\Tests\Unit\Normalizer;

use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductCreateNormalizer;

/**
 * Test related class
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCreateNormalizerTest extends AbstractProductNormalizerTest
{
    protected function setUp()
    {
        $this->channelManager = $this->getChannelManagerMock();
        $this->mediaManager   = $this->getMediaManagerMock();

        $this->normalizer = new ProductCreateNormalizer(
            $this->channelManager,
            $this->mediaManager
        );
    }

    public function testNormalize()
    {
        $context = $this->getContext();

        $product = $this->getProductMock($this->getSampleAttributes());

        $this->normalizer->normalize($product, null, $context);
    }
}