<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MagentoMappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\NormalizeException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;

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
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param ProductNormalizerGuesser            $normalizerGuesser
     * @param LocaleManager                       $localeManager
     * @param MagentoMappingMerger                $storeViewMappingMerger
     * @param MagentoMappingMerger                $categoryMappingMerger
     * @param MagentoSoapClientParametersRegistry $clientParametersRegistry
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        LocaleManager $localeManager,
        MagentoMappingMerger $storeViewMappingMerger,
        MagentoMappingMerger $categoryMappingMerger,
        MagentoSoapClientParametersRegistry $clientParametersRegistry
    ) {
        parent::__construct(
            $webserviceGuesser,
            $normalizerGuesser,
            $localeManager,
            $storeViewMappingMerger,
            $clientParametersRegistry
        );

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
            [
                'magentoCategories'   => $magentoCategories,
                'magentoUrl'          => $this->getSoapUrl(),
                'defaultLocale'       => $this->defaultLocale,
                'magentoStoreViews'   => $magentoStoreViews,
                'categoryMapping'     => $this->categoryMappingMerger->getMapping(),
                'defaultStoreView'    => $this->getDefaultStoreView()
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function process($categories)
    {
        $this->beforeExecute();

        $normalizedCategories = [
            'create'    => [],
            'update'    => [],
            'move'      => [],
            'variation' => []
        ];

        $categories = is_array($categories) ? $categories : [$categories];

        foreach ($categories as $category) {
            if ($category->getParent()) {
                try {
                    $normalizedCategory = $this->categoryNormalizer->normalize(
                        $category,
                        AbstractNormalizer::MAGENTO_FORMAT,
                        $this->globalContext
                    );
                } catch (NormalizeException $e) {
                    throw new InvalidItemException($e->getMessage(), array($category));
                }

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

        $this->categoryMappingMerger->setParameters($this->getClientParameters(), $this->getDefaultStoreView());
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
