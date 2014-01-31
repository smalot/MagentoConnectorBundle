<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;

/**
 * Magento category processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryProcessor extends AbstractProcessor
{
    /**
     * @var string
     */
    protected $categoryMapping = '';

    /**
     * get categoryMapping
     *
     * @return string categoryMapping
     */
    public function getCategoryMapping()
    {
        return $this->categoryMapping;
    }

    /**
     * Set categoryMapping
     *
     * @param string $categoryMapping categoryMapping
     *
     * @return AbstractProcessor
     */
    public function setCategoryMapping($categoryMapping)
    {
        $this->categoryMapping = $categoryMapping;

        return $this;
    }

    /**
     * Get computed storeView mapping (string to array)
     * @return array
     */
    protected function getComputedCategoryMapping()
    {
        return $this->getComputedMapping($this->categoryMapping);
    }

    /**
     * Function called before all process
     */
    protected function beforeExecute()
    {
        parent::beforeExecute();

        $this->categoryNormalizer = $this->normalizerGuesser->getCategoryNormalizer($this->getClientParameters());

        $magentoCategories = $this->webservice->getCategoriesStatus();
        $magentoStoreViews = $this->webservice->getStoreViewsList();

        $this->globalContext = array(
            'magentoCategories'   => $magentoCategories,
            'magentoUrl'          => $this->soapUrl,
            'defaultLocale'       => $this->defaultLocale,
            'categoryMapping'     => $this->getComputedCategoryMapping(),
            'magentoStoreViews'   => $magentoStoreViews,
            'storeViewMapping'    => $this->getComputedStoreViewMapping(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function process($categories)
    {
        $this->beforeExecute();

        $normalizedCategories = array(
            'create'    => array(),
            'update'    => array(),
            'move'      => array(),
            'variation' => array()
        );

        $categories = is_array($categories) ? $categories : array($categories);

        foreach ($categories as $category) {
            if ($category->getParent()) {
                $normalizedCategory = $this->categoryNormalizer->normalize(
                    $category,
                    AbstractNormalizer::MAGENTO_FORMAT,
                    $this->globalContext
                );

                $normalizedCategories = array_merge_recursive($normalizedCategories, $normalizedCategory);
            }
        }

        return $normalizedCategories;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            array(
                'categoryMapping' => array(
                    'type'    => 'textarea',
                    'options' => array(
                        'required' => false
                    )
                )
            )
        );
    }
}
