<?php

namespace QfArticleRegionRestriction\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\CustomerService;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Enlight_Components_Session_Namespace;

class ListProductService implements ListProductServiceInterface
{
    private $listProductService;

    private $customerService;

    private $session;

    private $templateManager;

    private $pluginDir;

    /**
     * ListProductService constructor.
     *
     * @param ListProductServiceInterface $listProductService
     * @param CustomerService $customerService
     * @param Enlight_Components_Session_Namespace $session
     * @param \Enlight_Template_Manager $templateManager
     * @param $pluginDir
     */
    public function __construct(
        ListProductServiceInterface $listProductService,
        CustomerService $customerService,
        Enlight_Components_Session_Namespace $session,
        \Enlight_Template_Manager $templateManager,
        $pluginDir
    )
    {
        $this->listProductService = $listProductService;
        $this->customerService = $customerService;
        $this->session = $session;
        $this->templateManager = $templateManager;
        $this->pluginDir = $pluginDir;
    }

    /**
     * Generate list of products for listing view.
     *
     * @param array $numbers
     * @param Struct\ProductContextInterface $context
     * @return Struct\ListProduct[]
     */
    public function getList(array $numbers, Struct\ProductContextInterface $context)
    {
        if ($this->session->get('sUserId')) {
            $userId = $this->session->get('sUserId');

            /** @var array $customer */
            $customer = $this->getCustomer($userId);

            foreach ($numbers as $key => $number) {
                $product = $this->listProductService->get($number, $context);

                if ($product) {
                    /** @var array $mainCodes */
                    $mainCodes = $this->getMainCodes($product);

                    if ($mainCodes['qfMainCountryCodes'][0] && $mainCodes['qfMainRegionCodes'][0]) {
                        if (in_array($customer['customerBillingAddressCountryCode'], $mainCodes['qfMainCountryCodes'])) {
                            if (in_array($customer['customerBillingAddressStateCode'], $mainCodes['qfMainRegionCodes'])) {
                                continue;
                            } else {
                                unset($numbers[$key]);
                            }
                        } else {
                            unset($numbers[$key]);
                        }
                    }
                }
            }

            $this->templateManager->addTemplateDir($this->pluginDir . '/Resources/views');
        }

        $listProducts = $this->listProductService->getList($numbers, $context);

        return $listProducts;
    }

    /**
     * Get single product for listing view.
     *
     * @param string $number
     * @param Struct\ProductContextInterface $context
     * @return Struct\ListProduct|null
     */
    public function get($number, Struct\ProductContextInterface $context)
    {
        $listProduct = $this->listProductService->get($number, $context);

        return $listProduct;
    }

    /**
     * Get customer country and state code in ISO format.
     *
     * @param $userId
     * @return array
     */
    private function getCustomer($userId)
    {
        $customer = $this->customerService->getList([$userId])[$userId];
        $customerBillingAddressCountryCode = $customer->getBillingAddress()->getCountry()->getIso();

        // TODO: Get the ISO code from plugin config.
        if ($customerBillingAddressCountryCode === 'DE') {
            $customerBillingAddressStateCode = $customer->getBillingAddress()->getState()->getCode();
        } else {
            $customerBillingAddressStateCode = [];
        }

        return [
            'customerBillingAddressCountryCode' => $customerBillingAddressCountryCode,
            'customerBillingAddressStateCode' => $customerBillingAddressStateCode
        ];
    }

    /**
     * Get main codes for product in ISO format.
     *
     * @param $product
     * @return array
     */
    private function getMainCodes($product)
    {
        $attributes = $product->getAttribute('core');

        $qfMainCountryCodes = explode('##', rtrim(ltrim($attributes->get('qf_main_country_codes'), '#'), '#'));
        $qfMainRegionCodes = explode('##', rtrim(ltrim($attributes->get('qf_main_region_codes'), '#'), '#'));

        return [
            'qfMainCountryCodes' => $qfMainCountryCodes,
            'qfMainRegionCodes' => $qfMainRegionCodes
        ];
    }
}