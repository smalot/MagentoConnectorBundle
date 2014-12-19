<?php

namespace Pim\Bundle\MagentoConnectorBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Mapping data controller. Will provide Akeneo side and Magento side data
 * to be used inside the mapping configuration
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MappingDataController extends ContainerAware
{
    /**
     * @Route("/pim-attributes")
     *
     * @return Response
     */
    public function pimAttributesAction()
    {
        $attributeRepo = $this->container->get('pim_catalog.repository.attribute');

        $attributes = $attributeRepo->findAll();

        $attributeData = [];

        foreach ($attributes as $attribute) {
            $attributeData[] = ['id' => $attribute->getCode(), 'text' => $attribute->getCode()];
        }

        return new Response(json_encode($attributeData));
    }

    /**
     * @Route("/magento-attributes")
     *
     * @param  Request $request
     *
     * @return Response
     */
    public function magentoAttributesAction(Request $request)
    {
        $webservice = $this->getMagentoWebservice($request->query);

        $magentoAttributes = $webservice->getAllAttributes();
        ksort($magentoAttributes);

        $magentoAttributeCodes = array_keys($magentoAttributes);

        $attributeData = [];

        foreach ($magentoAttributeCodes as $magentoAttributeCode) {
            $attributeData[] = ['id' => $magentoAttributeCode, 'text' => $magentoAttributeCode];
        }

        return new Response(json_encode($attributeData));
    }

    /**
     * @Route("/pim-locales")
     *
     * @return Response
     */
    public function pimLocalesAction()
    {
        $localeRepo = $this->container->get('pim_catalog.repository.locale');

        $locales = $localeRepo->getActivatedLocales();

        $localeData = [];

        foreach ($locales as $locale) {
            $localeData[] = ['id' => $locale->getCode(), 'text' => $locale->getCode()];
        }

        return new Response(json_encode($localeData));
    }

    /**
     * @Route("/magento-storeviews")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function magentoStoreViewsAction(Request $request)
    {
        $webservice = $this->getMagentoWebservice($request->query);

        $storeViews = $webservice->getStoreViewsList();
        ksort($storeViews);

        $storeViewData = [];

        foreach ($storeViews as $storeView) {
            $storeViewData[] = ['id' => $storeView['code'], 'text' => $storeView['code']];
        }

        return new Response(json_encode($storeViewData));
    }

    /**
     * Get the Magento webservice from the provided parameters
     *
     * @param ParameterBag $params
     *
     * @return Webservice
     */
    protected function getMagentoWebservice(ParameterBag $params)
    {
        $clientParameters = $this->getClientParameters($params);
        $webserviceGuesser = $this->container->get('pim_magento_connector.guesser.magento_webservice');

        return $webserviceGuesser->getWebservice($clientParameters);
    }

    /**
     * Get the magento soap client parameters
     *
     * @param ParameterBag $params
     *
     * @return MagentoSoapClientParametersRegistry
     */
    protected function getClientParameters(ParameterBag $params)
    {
        $paramsRegistry = $this->container->get('pim_magento_connector.webservice.magento_soap_client_parameters_registry');

        $clientParameters = $paramsRegistry->getInstance(
            $params->get('soapUsername'),
            $params->get('soapApiKey'),
            $params->get('magentoUrl'),
            $params->get('wsdlUrl'),
            $params->get('defaultStoreView'),
            $params->get('httpLogin'),
            $params->get('httpPassword')
        );

        return $clientParameters;
    }
}
