<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Guesser\MagentoWebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;

/**
 * Magento category writer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class CategoryMagentoWriter extends AbstractMagentoWriter
{
    /**
     * @var CategoryMappingManager
     */
    protected $categoryMappingManager;

    /**
     * Constructor
     *
     * @param ChannelManager           $channelManager
     * @param MagentoWebserviceGuesser $magentoWebserviceGuesser
     * @param CategoryMappingManager   $categoryMappingManager
     */
    public function __construct(
        ChannelManager $channelManager,
        MagentoWebserviceGuesser $magentoWebserviceGuesser,
        CategoryMappingManager $categoryMappingManager
    ) {
        parent::__construct($channelManager, $magentoWebserviceGuesser);

        $this->categoryMappingManager = $categoryMappingManager;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $batches)
    {
        $this->beforeWrite();

        //creation for each product in the admin storeView (with default locale)
        foreach ($batches as $batch) {
            $this->handleNewCategory($batch);
            $this->handleUpdateCategory($batch);
            $this->handleMoveCategory($batch);
            $this->handleVariationCategory($batch);
        }
    }

    /**
     * Handle category creation
     * @param array $batch
     */
    protected function handleNewCategory(array $batch)
    {
        if (isset($batch['create'])) {
            foreach ($batch['create'] as $newCategory) {
                $pimCategory       = $newCategory['pimCategory'];
                $magentoCategoryId = $this->magentoWebservice->sendNewCategory($newCategory['magentoCategory']);
                $magentoUrl        = $this->soapUrl;

                $this->categoryMappingManager->registerCategoryMapping(
                    $pimCategory,
                    $magentoCategoryId,
                    $magentoUrl
                );
            }
        }
    }

    /**
     * handle category update
     * @param array $batch
     */
    protected function handleUpdateCategory(array $batch)
    {
        if (isset($batch['update'])) {
            foreach ($batch['update'] as $updateCategory) {
                $this->magentoWebservice->sendUpdateCategory($updateCategory);
            }
        }
    }

    /**
     * handle category move
     * @param array $batch
     */
    protected function handleMoveCategory(array $batch)
    {
        if (isset($batch['move'])) {
            foreach ($batch['move'] as $moveCategory) {
                $this->magentoWebservice->sendMoveCategory($moveCategory);
            }
        }
    }

    /**
     * handle category variation update
     * @param array $batch
     */
    protected function handleVariationCategory(array $batch)
    {
        if (isset($batch['variation'])) {
            foreach ($batch['variation'] as $variationCategory) {
                $pimCategory        = $variationCategory['pimCategory'];
                $magentoCategoryId  = $this->categoryMappingManager->getIdFromCategory($pimCategory, $this->soapUrl);
                $magentoCategory    = $variationCategory['magentoCategory'];
                $magentoCategory[0] = $magentoCategoryId;

                $this->magentoWebservice->sendUpdateCategory($magentoCategory);
            }
        }
    }
}
