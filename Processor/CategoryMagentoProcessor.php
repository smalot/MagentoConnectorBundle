<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\MagentoWebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\MagentoNormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;

/**
 * Magento category processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryMagentoProcessor extends AbstractMagentoProcessor
{
    /**
     * @var CategoryMappingManager
     */
    protected $categoryMappingManager;

    /**
     * @var string
     */
    protected $rootCategoryMapping = '';

    /**
     * get rootCategoryMapping
     *
     * @return string rootCategoryMapping
     */
    public function getRootCategoryMapping()
    {
        return $this->rootCategoryMapping;
    }

    /**
     * Set rootCategoryMapping
     *
     * @param string $rootCategoryMapping rootCategoryMapping
     *
     * @return AbstractMagentoProcessor
     */
    public function setRootCategoryMapping($rootCategoryMapping)
    {
        $this->rootCategoryMapping = $rootCategoryMapping;

        return $this;
    }

    /**
     * Get computed storeView mapping (string to array)
     * @return array
     */
    protected function getComputedRootCategoryMapping()
    {
        return $this->getComputedMapping($this->rootCategoryMapping);
    }

    /**
     * @param ChannelManager           $channelManager
     * @param MagentoWebserviceGuesser $magentoWebserviceGuesser
     * @param ProductNormalizerGuesser $magentoNormalizerGuesser
     */
    public function __construct(
        ChannelManager $channelManager,
        MagentoWebserviceGuesser $magentoWebserviceGuesser,
        MagentoNormalizerGuesser $magentoNormalizerGuesser,
        CategoryMappingManager $categoryMappingManager
    ) {
        parent::__construct($channelManager, $magentoWebserviceGuesser, $magentoNormalizerGuesser);

        $this->categoryMappingManager = $categoryMappingManager;
    }

    /**
     * Function called before all process
     */
    protected function beforeProcess()
    {
        $this->magentoWebservice  = $this->magentoWebserviceGuesser->getWebservice($this->getClientParameters());
        $this->categoryNormalizer = $this->magentoNormalizerGuesser->getCategoryNormalizer(
            $this->getClientParameters(),
            $this->categoryMappingManager
        );

        $magentoCategories = $this->magentoWebservice->getCategoriesStatus();
        $magentoStoreViews = $this->magentoWebservice->getStoreViewsList();

        $this->globalContext = array(
            'magentoCategories'   => $magentoCategories,
            'magentoUrl'          => $this->soapUrl,
            'defaultLocale'       => $this->defaultLocale,
            'channel'             => $this->channel,
            'rootCategoryMapping' => $this->getComputedRootCategoryMapping(),
            'magentoStoreViews'   => $magentoStoreViews,
            'storeViewMapping'    => $this->getComputedStoreViewMapping(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function process($categories)
    {
        $this->beforeProcess();

        $normalizedCategories = array(
            'create' => array(),
            'update' => array(),
            'move' => array(),
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
                'rootCategoryMapping' => array(
                    'type'    => 'textarea',
                    'options' => array(
                        'required' => false
                    )
                )
            )
        );
    }
}
