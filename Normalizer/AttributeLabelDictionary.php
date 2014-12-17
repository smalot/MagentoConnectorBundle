<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

/**
 * This dictionary allows to manage constants about API Import attributes
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeLabelDictionary
{
    /** @staticvar string */
    const ID_HEADER = 'attribute_id';

    /** @staticvar string */
    const DEFAULT_VALUE_HEADER = 'default';

    /** @staticvar string */
    const BACKEND_TYPE_HEADER = 'type';

    /** @staticvar string */
    const INPUT_HEADER = 'input';

    /** @staticvar string */
    const LABEL_HEADER = 'label';

    /** @staticvar string */
    const REQUIRED_HEADER = 'required';

    /** @staticvar string */
    const GLOBAL_HEADER = 'global';

    /** @staticvar string */
    const VISIBLE_HEADER = 'visible_on_front';

    /** @staticvar string */
    const IS_UNIQUE_HEADER = 'unique';
}
