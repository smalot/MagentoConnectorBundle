<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer\Dictionary;

/**
 * This dictionary allows to manage constants about API Import attribute sets (Akeneo families)
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FamilyLabelDictionary
{
    /** @staticvar string */
    const ATTRIBUTE_SET_NAME_HEADER = 'attribute_set_name';

    /** @staticvar string */
    const ATTRIBUTE_SET_ID_HEADER = 'attribute_set_id';

    /** @staticvar string */
    const ATTRIBUTE_GROUP_ID_HEADER = 'attribute_group_id';

    /** @staticvar string */
    const ATTRIBUTE_GROUP_GENERAL = 'General';
}
