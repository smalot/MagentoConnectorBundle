<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Is the given field a Magneto url ?
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Annotation
 */
class MagentoUrl extends Constraint
{
    public $message = 'The given magento url is not valid';

    /**
     *{@inheritDoc}
     */
    public function validatedBy()
    {
        return 'magento_url';
    }
}
