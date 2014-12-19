<?php

namespace Pim\Bundle\MagentoConnectorBundle\Reader\ORM;

use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Item\MagentoItemStep;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;

/**
 * Attribute reader
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeReader extends MagentoItemStep implements ItemReaderInterface
{
    const IMAGE_ATTRIBUTE_TYPE = 'pim_catalog_image';

    /** @var AbstractQuery */
    protected $query;

    /** @var string */
    protected $attributeCodeMapping;

    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var boolean */
    protected $queryExecuted = false;

    /**
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param MagentoSoapClientParametersRegistry $clientParametersRegistry
     * @param AttributeRepository                 $attributeRepository
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        MagentoSoapClientParametersRegistry $clientParametersRegistry,
        AttributeRepository $attributeRepository
    ) {
        parent::__construct(
            $webserviceGuesser,
            $clientParametersRegistry
        );

        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Set raw (json encoded) attribute code mapping
     *
     * @param string $attributeCodeMapping
     *
     * @return AttributeReader
     */
    public function setAttributeCodeMapping($attributeCodeMapping)
    {
        $this->attributeCodeMapping = $attributeCodeMapping;

        return $this;
    }

    /**
     * Get raw (json encoded) attribute code mapping
     *
     * @return string
     */
    public function getAttributeCodeMapping()
    {
        if (empty($this->attributeCodeMapping)) {
            return json_encode([]);
        } else {
            return $this->attributeCodeMapping;
        }
    }

    /**
     * Get decoded attribute mapping
     *
     * @return array
     */
    public function geDecodedtAttributeCodeMapping()
    {
        return json_decode($this->attributeCodeMapping, true);
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $this->beforeExecute();

        if (!$this->queryExecuted) {
            $this->attributes = new \ArrayIterator($this->getQuery()->execute());
            $this->queryExecuted = true;
        }

        if ($attribute = $this->attributes->current()) {
            $this->attributes->next();
        }

        while ($attribute !== null && $this->isAttributeIgnored($attribute)) {
            if ($attribute = $this->attributes->current()) {
                $this->attributes->next();
            }
        }

        $this->stepExecution->incrementSummaryInfo('read');

        return $attribute;
    }

    /**
     * Is the given attribute ignored ?
     *
     * @param AbstractAttribute $attribute
     *
     * @return boolean
     */
    protected function isAttributeIgnored(AbstractAttribute $attribute)
    {
        $attributeCodeMapping = $this->getAttributeCodeMapping();

        if (isset($attributeCodeMapping[$attribute->getCode()])) {
            $magentoAttributeCode = $attributeCodeMapping[$attribute->getCode()];
        } else {
            $magentoAttributeCode = $attribute->getCode();
        }

        $magentoAttributeCode = strtolower($magentoAttributeCode);

        return in_array($magentoAttributeCode, $this->getIgnoredAttributes())
            || $attribute->getAttributeType() === self::IMAGE_ATTRIBUTE_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        if (!$this->query) {
            $this->query = $this->attributeRepository
                ->createQueryBuilder('a')
                ->join('a.families', 'PimCatalogBundle:Family')
                ->getQuery();
        }

        return $this->query;
    }

    /**
     * Get all ignored attributes
     *
     * @return array
     */
    protected function getIgnoredAttributes()
    {
        return [
            'sku',
            'name',
            'description',
        ];
    }

    /**
     * Return all Magento attribute codes
     *
     * @return array
     */
    public function getMagentoAttributeCodes()
    {
        return [
            'allowAddition' => true,
            'targets' => $this->getIgnoredAttributes()
        ];
        $attributeCodes = [];

        if (null != $this->webservice) {
            $attributes = $this->webservice->getAllAttributes();
        }

        return [
            'allowAddition' => true,
            'targets' => $attributeCodes
        ];
    }

    /**
     * Return all Akeneo attribute codes
     *
     * @return array
     */
    public function getAkeneoAttributeCodes()
    {
        $attributes = $this->attributeRepo->findAll();
        $attributeCodes = [];

        foreach ($attributes as $attribute) {
            $attributeCodes[] = $attribute->getCode();
        }

        return ['sources' => $attributeCodes];
    }

    /**
     * {@inheritdoc}
     *
     * TODO: make URL work in dev and production mode
     */
    public function getConfigurationFields()
    {
        $dataTargets = array_merge(
            ['route' => 'magento-attributes'],
            $this->getMagentoParamsForMapping()
        );

        return array_merge(
            parent::getConfigurationFields(),
            [
                'attributeCodeMapping' => [
                    'type'    => 'textarea',
                    'options' => [
                        'label' => 'pim_magento_connector.export.attributeCodeMapping.label',
                        'help'  => 'pim_magento_connector.export.attributeCodeMapping.help',
                        'required' => false,
                        'attr'     => [
                            'class' => 'mapping-field',
                            'data-sources' => json_encode([
                                'route' => 'pim-attributes'
                            ]),
                            'data-targets' => json_encode($dataTargets),
                            'data-name'   => 'attributeCode'
                        ]
                    ]
                ]
            ]
        );
    }
}
