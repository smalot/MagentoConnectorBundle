<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer\Dictionary;

/**
 * This dictionary allows to manage constants about API Import categories
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryLabelDictionary
{
    /** @staticvar string */
    const NAME_HEADER = 'name';

    /** @staticvar string */
    const ROOT_HEADER = '_root';

    /** @staticvar string */
    const ACTIVE_HEADER = 'is_active';

    /** @staticvar string */
    const DESCRIPTION_HEADER = 'description';

    /** @staticvar string */
    const POSITION_HEADER = 'position';

    /**
     * Path to the category with "parent category name/category name"
     * If this and STORE_HEADER are empty, Magento scope will be null and category will not be create
     *
     * @staticvar string
     */
    const CATEGORY_HEADER = '_category';

    /** @staticvar string */
    const STORE_HEADER = '_store';

    /** @staticvar string */
    const INCLUDE_IN_MENU_HEADER = 'include_in_menu';

    /** @staticvar string */
    const AVAILABLE_SORT_BY_HEADER = 'available_sort_by';

    /** @staticvar string */
    const DEFAULT_SORT_BY_HEADER = 'default_sort_by';

    /** @staticvar string */
    const SEPARATOR = '/';
}
