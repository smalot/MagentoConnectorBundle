<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

/**
 * Occurs when a normalizer try to transform a variant group axis which is not a simple simple in a super attribute
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WrongAttributeTypeException extends \Exception
{
}
