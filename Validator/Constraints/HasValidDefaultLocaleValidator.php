<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

/**
 * Validator for default locale
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HasValidDefaultLocaleValidator extends ConstraintValidator
{
    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @param ChannelManager $channelManager
     */
    public function __construct(ChannelManager $channelManager)
    {
        $this->channelManager = $channelManager;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param AbstractConfigurableStepElement $protocol   The value that should be validated
     * @param Constraint                      $constraint The constraint for the validation
     *
     * @api
     *
     * @return mixed
     */
    public function validate($protocol, Constraint $constraint)
    {
        if ($channel = $this->channelManager->getChannelByCode($protocol->getChannel())) {
            foreach ($channel->getLocales() as $locale) {
                if ($locale->getCode() === $protocol->getDefaultLocale()) {
                    return true;
                }
            }
        }

        $this->context->addViolationAt('defaultLocale', $constraint->message, array('defaultLocale'));
    }
}
