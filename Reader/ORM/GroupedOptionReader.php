<?php

namespace Pim\Bundle\MagentoConnectorBundle\Reader\ORM;

/**
 * Reads group option for attributes at once
 *
 * @author    Julien Sanchez <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupedOptionReader extends BulkEntityReader
{
    /**
     * @var array
     */
    protected $groupedOptions;

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->groupedOptions) {
            $options = parent::read();

            if (!is_array($options)) {
                return $options;
            }

            $this->groupedOptions = array();

            foreach ($options as $option) {
                $attributeCode = $option->getAttribute()->getCode();

                if (!in_array($attributeCode, $this->getIgnoredAttributes())) {
                    $this->groupedOptions[$attributeCode] =
                        isset($this->groupedOptions[$attributeCode]) ?
                            array_merge($this->groupedOptions[$attributeCode], array($option)) :
                            array($option);
                }
            }
        }

        return is_array($this->groupedOptions) ? array_shift($this->groupedOptions) : null;
    }

    /**
     * Get all ignored attributes
     * @return array
     */
    protected function getIgnoredAttributes()
    {
        return array(
            'visibility'
        );
    }
}
