<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Doctrine\ORM\EntityManager;

/**
 * Magento option cleaner
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class OptionCleaner extends Cleaner
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $attributeClassName;

    /**
     * @var string
     */
    protected $optionClassName;

    /**
     * @param WebserviceGuesser $webserviceGuesser
     * @param EntityManager     $em
     * @param string            $attributeClassName
     * @param string            $optionClassName
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        EntityManager $em,
        $attributeClassName,
        $optionClassName
    ) {
        parent::__construct($webserviceGuesser);

        $this->em                 = $em;
        $this->attributeClassName = $attributeClassName;
        $this->optionClassName    = $optionClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        parent::beforeExecute();

        $magentoOptions = $this->webservice->getAllAttributesOptions();

        foreach ($magentoOptions as $attributeCode => $options) {
            $attribute = $this->getAttribute($attributeCode);

            $this->cleanOptions($options, $attribute);
        }
    }

    /**
     * Clean options
     * @param array     $options
     * @param Attribute $attribute
     *
     * @throws InvalidItemException If clean doesn't goes well
     */
    protected function cleanOptions(array $options, Attribute $attribute = null)
    {
        foreach ($options as $optionLabel => $optionValue) {
            if ($attribute != null &&
                !in_array($attribute->getCode(), $this->getIgnoredAttributes()) &&
                $this->getOption($optionLabel, $attribute) === null
            ) {
                try {
                    $this->handleOptionNotInPimAnymore($optionValue, $attribute->getCode());
                } catch (SoapCallException $e) {
                    throw new InvalidItemException($e->getMessage(), array($optionLabel));
                }
            }
        }
    }

    /**
     * Handle deletion or disableing of options which are not in PIM anymore
     * @param string $optionId
     * @param string $attributeCode
     */
    protected function handleOptionNotInPimAnymore($optionId, $attributeCode)
    {
        if ($this->notInPimAnymoreAction === self::DELETE) {
            try {
                $this->webservice->deleteOption($optionId, $attributeCode);
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), array($optionId));
            }
        }
    }

    /**
     * Get attribute for attribute code
     * @param string $attributeCode
     *
     * @return mixed
     */
    protected function getAttribute($attributeCode)
    {
        return $this->em->getRepository($this->attributeClassName)->findOneBy(array('code' => $attributeCode));
    }

    /**
     * Get option for option label and attribute
     * @param string    $optionLabel
     * @param Attribute $attribute
     *
     * @return mixed
     */
    protected function getOption($optionLabel, Attribute $attribute)
    {
        return $this->em->getRepository($this->optionClassName)->findOneBy(
            array('code' => $optionLabel, 'attribute' => $attribute)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        $configurationFields = parent::getConfigurationFields();

        $configurationFields['notInPimAnymoreAction']['options']['choices'] = array(
            Cleaner::DO_NOTHING => 'pim_magento_connector.clean.do_nothing.label',
            Cleaner::DELETE     => 'pim_magento_connector.clean.delete.label'
        );

        $configurationFields['notInPimAnymoreAction']['options']['help'] =
            'pim_magento_connector.clean.notInPimAnymoreAction.help';
        $configurationFields['notInPimAnymoreAction']['options']['label'] =
            'pim_magento_connector.clean.notInPimAnymoreAction.label';

        return $configurationFields;
    }

    /**
     * Get all ignored attributes
     * @return array
     */
    protected function getIgnoredAttributes()
    {
        return array(
            'visibility',
            'tax_class_id'
        );
    }
}
