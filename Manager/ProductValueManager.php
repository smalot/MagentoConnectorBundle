<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Model\ProductValue;

/**
 * ProductValue manager
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueManager
{
    /**
     * @var string
     */
    protected $className = '';

    /**
     * Constructor
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    /**
     * Create a product value from a attribute default option
     * @param Attribute $attribute
     *
     * @return ProductValue
     */
    public function createProductValueForDefaultOption(Attribute $attribute)
    {
        $productValue = new $this->className();
        $productValue->setAttribute($attribute);
        $productValue->setOption($attribute->getDefaultValue());

        return $productValue;
    }
}
