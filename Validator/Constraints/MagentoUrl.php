<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
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
