<?php

namespace GlpiPlugin\Myplugin;

use CommonDBTM;
use CommonGLPI;
use Computer;
use DB;
use Glpi\Application\View\TemplateRenderer;

class Superasset_Item extends CommonDBTM
{

    // right management, we'll change this later
    static $rightname = 'myplugin::superasset';

    static function getTypeName($nb = 0)
    {
        return _n('Super-asset Item', 'Super-assets Items', $nb);
    }

    /**
     * Tabs title
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case Superasset::class:
                $nb = countElementsInTable(
                    self::getTable(),
                    [
                        'plugin_myplugin_superassets_id' => $item->getID()
                    ]
                );
                return self::createTabEntry(self::getTypeName($nb), $nb);
            case Computer::class:
                $nb = countElementsInTable(
                    self::getTable(),
                    [
                        'itemtype' => Computer::class,
                        'items_id' => $item->getID()
                    ]
                );
                return self::createTabEntry(self::getTypeName($nb), $nb);
        }
        return '';
    }

    /**
     * Display tabs content
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case Superasset::class:
                return self::showForSuperAssets($item, $withtemplate);
            case Computer::class:
                return self::showForComputers($item, $withtemplate);
        }

        return true;
    }

    /**
     * Specific function for display only computers of Superasset
     */
    static function showForSuperAssets(Superasset $superasset, $withtemplate = 0)
    {

        $superasset->showFormHeader(['withtemplate' => $withtemplate]);
        TemplateRenderer::getInstance()->display('@myplugin/superasset_item_.html.twig', [
            'superasset' => $superasset,
            'plugin_myplugin_superassets_id' => $superasset->getID(),
            'owned_computer_id' => $superasset->getSuperAssetItem()->getField('items_id')
        ]);
        $superasset->showFormButtons();
    }


    /**
     * Specific function for display only Superasset of computer
     */
    static function showForComputers(Computer $computer, $withtemplate = 0)
    {
        $superassetItem = new Superasset_Item();
        $computer_items = $superassetItem->find([
            'itemtype' => Computer::class,
            'items_id' => $computer->getID()
        ]);
        TemplateRenderer::getInstance()->display('@myplugin/superasset_item_computer.html.twig', [
            'header_rows' => [
                [
                    [
                        'content' => __('Id', 'myplugin'),
                        'style' => 'border: 1px solid black; padding-inline : 10px;'
                    ],

                    [
                        'content' => __('SuperAsset Id', 'myplugin'),
                        'style' => 'border: 1px solid black; padding-inline : 10px;'
                    ],

                    [
                        'content' => __('Type', 'myplugin'),
                        'style' => 'border: 1px solid black; padding-inline : 10px;'
                    ],

                    [
                        'content' => __('Item Id', 'myplugin'),
                        'style' => 'border: 1px solid black; padding-inline : 10px;'
                    ]
                ]
            ],
            'rows' => array_map(function ($item) {
                return [
                    'values' => [
                        [
                            'content' => $item['id'],
                            'style' => 'border: 1px solid black;'
                        ],
                        [
                            'content' => $item['plugin_myplugin_superassets_id'],
                            'style' => 'border: 1px solid black;'
                        ],
                        [
                            'content' => $item['itemtype'],
                            'style' => 'border: 1px solid black; padding-inline : 10px;'
                        ],
                        [
                            'content' => $item['items_id'],
                            'style' => 'border: 1px solid black;'
                        ]
                    ]
                ];
            }, $computer_items)
        ]);
    }
}
