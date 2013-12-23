<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsValidWsdlUrl extends Constraint
{
    public $message = 'The given magento url is not valid';

    public function validatedBy()
    {
        return 'is_valid_wsdl_url';
    }
}