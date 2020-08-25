<?php

namespace QfArticleRegionRestriction;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;

class QfArticleRegionRestriction extends Plugin
{
    /**
     * Install the plugin and add necessary article attributes to database.
     *
     * @param InstallContext $context
     */
    public function install(InstallContext $context)
    {
        $attributeService = $this->container->get('shopware_attribute.crud_service');

        try {
            $attributeService->update(
                's_articles_attributes',
                'qf_main_country_codes',
                'string',
                [
                    'label' => 'Codes der Kernländer',
                    'helpText' => 'Bitte fügen Sie hier die Codes der Kernländer ein.',
                    'translatable' => false,
                    'position' => '0',
                    'displayInBackend' => true
                ]
            );
        } catch (\Exception $e) {
            print_r($e);
        }

        try {
            $attributeService->update(
                's_articles_attributes',
                'qf_main_region_codes',
                'string',
                [
                    'label' => 'Codes der Kernregionen',
                    'helpText' => 'Bitte fügen Sie hier die Codes der Kernregionen ein.',
                    'translatable' => false,
                    'position' => '0',
                    'displayInBackend' => true
                ]
            );
        } catch (\Exception $e) {
            print_r($e);
        }
    }

    /**
     * Uninstall the plugin and delete necessary article attributes from database.
     *
     * @param UninstallContext $context
     */
    public function uninstall(UninstallContext $context)
    {
        $attributeService = $this->container->get('shopware_attribute.crud_service');

        try {
            $attributeService->delete(
                's_articles_attributes',
                'qf_main_country_codes'
            );
        } catch (\Exception $e) {
            print_r($e);
        }

        try {
            $attributeService->delete(
                's_articles_attributes',
                'qf_main_region_codes'
            );
        } catch (\Exception $e) {
            print_r($e);
        }
    }
}