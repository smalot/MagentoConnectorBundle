<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MagentoMappingMerger;

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
    protected $categoryMapping;

    /**
     * @var MagentoMappingMerger
     */
    protected $categoryMappingMerger;

    /**
     * @param WebserviceGuesser        $webserviceGuesser
     * @param ProductNormalizerGuesser $normalizerGuesser
     * @param LocaleManager            $localeManager
     * @param MagentoMappingMerger     $storeViewMappingMerger
     * @param MagentoMappingMerger     $categoryMappingMerger
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        LocaleManager $localeManager,
        MagentoMappingMerger $storeViewMappingMerger,
        MagentoMappingMerger $categoryMappingMerger
    ) {
        parent::__construct($webserviceGuesser, $normalizerGuesser, $localeManager, $storeViewMappingMerger);

        $this->categoryMappingMerger = $categoryMappingMerger;
    }

    /**
     * get categoryMapping
     *
     * @return string categoryMapping
     */
    public function getCategoryMapping()
    {
        return json_encode($this->categoryMappingMerger->getMapping()->toArray());
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
        $this->categoryMappingMerger->setMapping(json_decode($categoryMapping, true));

        return $this;
    }

    /**
     * Function called before all process
     */
    protected function beforeExecute()
    {
        parent::beforeExecute();

        $this->categoryNormalizer = $this->normalizerGuesser->getCategoryNormalizer($this->getClientParameters());

        $magentoStoreViews = $this->webservice->getStoreViewsList();
        $magentoCategories = $this->webservice->getCategoriesStatus();

        $this->globalContext = array_merge(
            $this->globalContext,
            array(
                'magentoCategories'   => $magentoCategories,
                'magentoUrl'          => $this->getSoapUrl(),
                'defaultLocale'       => $this->defaultLocale,
                'magentoStoreViews'   => $magentoStoreViews,
                'categoryMapping'     => $this->categoryMappingMerger->getMapping()
            )
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
     * Called after the configuration is set
     */
    protected function afterConfigurationSet()
    {
        parent::afterConfigurationSet();

        $this->categoryMappingMerger->setParameters($this->getClientParameters());
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            $this->categoryMappingMerger->getConfigurationField()
        );
    }
}
