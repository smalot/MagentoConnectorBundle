<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * This constraint allows to validate if Magento is reachable with parameters of MagentoConfiguration entity
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoReachable extends Constraint
{
    public $messageNotReachableUrl         = 'pim_magento_connector.export.validator.url_not_reachable';
    public $messageInvalidSoapUrl          = 'pim_magento_connector.export.validator.soap_url_not_valid';
    public $messageAccessDenied            = 'pim_magento_connector.export.validator.access_denied';
    public $messageXmlNotValid             = 'pim_magento_connector.export.validator.xml_not_valid';
    public $messageUndefinedSoapException  = 'pim_magento_connector.export.validator.undefined_exception';
    public $messageUserHasNoPermission     = 'pim_magento_connector.export.validator.user_has_no_permission';

    /**
     * Returns alias of the MagentoReachable service
     *
     * @return string
     */
    public function validatedBy()
    {
        return 'magento_reachable';
    }

    /**
     * @{inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
