<?php

namespace GlpiPlugin\Myplugin;

use CommonDBTM;
use Glpi\Application\View\TemplateRenderer;
use Notepad;
use Log;

class Superasset extends CommonDBTM
{
    // right management, we'll change this later
    static $rightname = 'computer';

    // permits to automaticaly store logs for this itemtype
    // in glpi_logs table
    public $dohistory = true;

    /**
     *  Name of the itemtype
     */
    static function getTypeName($nb = 0)
    {
        return _n('Super-asset', 'Super-assets', $nb);
    }

    function showForm($ID, $options = [])
    {
        $this->initForm($ID, $options);
        // @myplugin est un raccourci pour indiquer d'aller chercher
        // dans le dossier **templates** de votre propre plugin
        TemplateRenderer::getInstance()->display('@myplugin/superasset.form.html.twig', [
            'item'   => $this,
            'params' => $options,
        ]);

        return true;
    }

    /**
     * Define menu name
     */
    static function getMenuName($nb = 0)
    {
        // call class label
        return self::getTypeName($nb);
    }

    /**
     * Define additionnal links used in breacrumbs and sub-menu
     */
    static function getMenuContent()
    {
        $title  = self::getMenuName(2);
        $search = self::getSearchURL(false);
        $form   = self::getFormURL(false);

        // define base menu
        $menu = [
            'title' => __("My plugin", 'myplugin'),
            'page'  => $search,

            // define sub-options
            // we may have multiple pages under the "Plugin > My type" menu
            'options' => [
                'superasset' => [
                    'title' => $title,
                    'page'  => $search,

                    //define standard icons in sub-menu
                    'links' => [
                        'search' => $search,
                        'add'    => $form,
                    ]
                ]
            ]
        ];

        return $menu;
    }

    function defineTabs($options = [])
    {
        $tabs = [];
        $this->addDefaultFormTab($tabs)
            ->addStandardTab(Superasset_Item::class, $tabs, $options)
            ->addStandardTab(Log::class, $tabs, $options)
            ->addStandardTab(Notepad::class, $tabs, $options);

        return $tabs;
    }

    function getSuperAssetItem(){
        $superassetId = $this->getID();
        $superassetItem = new Superasset_Item();
        $superassetItem->getFromDB($superassetId);

        return $superassetItem;
    }

    function rawSearchOptions()
    {
        $options = [];

        $options[] = [
            'id'   => 'common',
            'name' => __('Characteristics')
        ];

        $options[] = [
            'id'    => 1,
            'table' => self::getTable(),
            'field' => 'name',
            'name'  => __('Name'),
            'datatype' => 'itemlink'
        ];

        $options[] = [
            'id'    => 2,
            'table' => self::getTable(),
            'field' => 'id',
            'name'  => __('ID')
        ];

        $options[] = [
            'id'           => 3,
            'table'        => Superasset_Item::getTable(),
            'field'        => 'id',
            'name'         => __('Number of associated assets', 'myplugin'),
            'datatype'     => 'count',
            'forcegroupby' => true,
            'usehaving'    => true,
            'joinparams'   => [
                'jointype' => 'child',
            ]
        ];

        return $options;
    }
}
