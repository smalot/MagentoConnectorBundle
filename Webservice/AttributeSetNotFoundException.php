<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * Exception thrown during attribute set to family code matching
 *
 * @author    Julien Sanchez <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeSetNotFoundException extends \Exception
{
    /**
     * Constructor
     *
     * @param string $message
     */
    public function __construct($message = 'Attribute set not found on magento platform')
    {
        parent::__construct($message);
    }
}
