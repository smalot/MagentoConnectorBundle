<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for product categories
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoryNormalizer implements NormalizerInterface
{
    /** @var CategoryManager */
    protected $categoryManager;

    /**
     * @param CategoryManager $categoryManager
     */
    public function __construct(CategoryManager $categoryManager)
    {
        $this->categoryManager = $categoryManager;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $defaultLocale = $context['defaultLocale'];

        if (!$object->isRoot()) {
            $categoryTree = $this->categoryManager->getEntityRepository()->getPath($object);

            foreach ($categoryTree as &$category) {
                $category = $this->getCategoryLabel($category, $defaultLocale);
            }

            $root = array_shift($categoryTree);

            $normalized = ['category' => implode('/', $categoryTree), 'root' => $root];
        } else {
            $normalized = ['category' => '', 'root' => $this->getCategoryLabel($object, $defaultLocale)];
        }

        return $normalized;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CategoryInterface && ProductNormalizer::API_IMPORT_FORMAT === $format;
    }

    /**
     * Get category label
     *
     * @param CategoryInterface $category
     * @param string            $localeCode
     *
     * @return string
     */
    protected function getCategoryLabel(CategoryInterface $category, $localeCode)
    {
        $category->setLocale($localeCode);

        return $category->getLabel();
    }
}