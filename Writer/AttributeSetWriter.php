<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyMappingManager;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;

/**
 * Magento attribute set writer
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeSetWriter extends AbstractWriter
{
    const FAMILY_CREATED  = 'Families created';
    const ATTRIBUTE_ADDED = 'Attribute added correctly';

    /**
     * @var FamilyMappingManager
     */
    protected $familyMappingManager;

    /**
     * Constructor
     *
     * @param WebserviceGuesser      $webserviceGuesser
     * @param FamilyMappingManager   $familyMappingManager
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        FamilyMappingManager $familyMappingManager
    ) {
        parent::__construct($webserviceGuesser);

        $this->familyMappingManager = $familyMappingManager;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $this->beforeExecute();


        foreach ($items as $item) {
            try {
                $this->handleNewFamily($item);
                $this->handleAddingAttributeToAttributeSet($item);
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), array());
            }
        }
    }

    /**
     * Handle family creation
     * @param array $item
     */
    protected function handleNewFamily(array $item)
    {
        if (isset($item['create'])) {
            $pimFamily       = $item['family'];
            $magentoFamilyId = $this->webservice->createAttributeSet($item['create']['attributeSetName']);
            $magentoUrl      = $this->soapUrl;

            $this->familyMappingManager->registerFamilyMapping(
                $pimFamily,
                $magentoFamilyId,
                $magentoUrl
            );
            $this->stepExecution->incrementSummaryInfo(self::FAMILY_CREATED);
        }
    }

    /**
     * Handle adding attribute to the attribute set
     * @param array $item
     */
    protected function handleAddingAttributeToAttributeSet(array $item)
    {
        if (isset($item['create'])) {
            foreach ($item['attributeIds'] as $attributeId) {
                $this->webservice->addAttributeToAttributeSet($attributeId, $this->familyMappingManager->getIdFromFamily($item['family'],  $this->soapUrl));
            }
        }
    }
}
