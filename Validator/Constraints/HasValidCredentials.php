<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class HasValidCredentials extends Constraint
{
    public $messageBadCredentials  = 'The given magento credentials are invalid';
    public $messageConnectionError = 'The given magento url seems to be invalid';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'has_valid_magento_credentials';
    }
}