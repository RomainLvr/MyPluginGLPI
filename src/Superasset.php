<?php

namespace GlpiPlugin\Myplugin;

use CommonDBTM;
use Computer;
use Glpi\Application\View\TemplateRenderer;
use Glpi\Dashboard\Grid;
use Html;
use Notepad;
use Log;
use MassiveAction;
use Toolbox;

class Superasset extends CommonDBTM
{
    // right management, we'll change this later
    static $rightname = 'myplugin::superasset';

    // permits to automaticaly store logs for this itemtype
    // in glpi_logs table
    public $dohistory = true;

    const RIGHT_ONE = 128;

    /**
     *  Name of the itemtype
     */
    static function getTypeName($nb = 0)
    {
        return _n('Super-asset', 'Super-assets', $nb);
    }

    function getRights($interface = 'central')
    {
        // if we need to keep standard rights
        $rights = parent::getRights();

        // define an additional right
        $rights[self::RIGHT_ONE] = __("My specific rights", "myplugin");

        return $rights;
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

    function getSuperAssetItem()
    {
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

    /*
    Ajouter en entête du formulaire d’édition des ordinateurs indiquant le nombre de Super asset associés.
        Ce nombre devrait être un lien vers l’onglet ajouté précédemment aux objets ordinateurs.
        Le lien pointera vers la même page mais avec un paramètre forcetab=PluginMypluginSuperasset$1.
    */

    public static function preItemFormComputer($params)
    {
        $item = $params['item'];
        $config = Config::getConfig();
        if ($item::getType() == Computer::getType()) {
            if ($config['myplugin_computer_form'] == 1) {
                $superassets = new Superasset();
                $superassets->getFromDB($item->fields['id']);
                $count = countElementsInTable(
                    Superasset_Item::getTable(),
                    [
                        'itemtype' => Computer::class,
                        'items_id' => $item->getID()
                    ]
                );
                echo "<a href='?id=" . $item->fields['id'] . "&forcetab=GlpiPlugin\Myplugin\Superasset_Item$1'>" . $count . "</a>";
            }
        }
    }

    function getSpecificMassiveActions($checkitem = null)
    {
        switch ($checkitem) {
            case null:
                $actions = parent::getSpecificMassiveActions($checkitem);

                $myclass = self::class;
                $action_key = 'addComputer';
                $action_label = __('Add computer', 'myplugin');
                $actions[$myclass . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;

                break;
        }
        return $actions;
    }

    static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        switch ($ma->getAction()) {
            case 'addComputer':
                echo __("Choisir un ordinateur à ajouter", 'myplugin') . " : ";
                echo Computer::dropdown();

                break;

            case 'addSuperAsset':
                echo __("Choisir un Super Asset à ajouter", 'myplugin') . " : ";
                echo Superasset::dropdown();
                echo "<input type='hidden' name='computer_id' value='" . $ma->getInput('computer_id')['items']['Computer'][1] . "'>";

                break;
        }

        return parent::showMassiveActionsSubForm($ma);
    }

    static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {
        switch ($ma->getAction()) {
            case 'addComputer':
                $input = $ma->getInput();

                foreach ($ids as $id) {

                    if (
                        $item->getFromDB($id)
                        && $item->doIt($input)
                    ) {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                    } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        $ma->addMessage(__("Something went wrong"));
                    }
                }
                return;
            case 'addSuperAsset':
                $input = $ma->getInput();

                foreach ($ids as $id) {

                    if (
                        $item->getFromDB($id)
                        && self::doIt($input)
                    ) {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                    } else {
                        Toolbox::logDebug($item->getFromDB($id));
                        Toolbox::logDebug(Superasset::doIt($input));
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        $ma->addMessage(__("Something went wrong"));
                    }
                }
                return;
        }

        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    static function doIt($input)
    {
        Toolbox::logDebug($input);
        switch ($input) {
            case 'computers_id':
                $computer = new Computer();
                $computer->getFromDB($input['computers_id']);

                $superassetItem = new Superasset_Item();
                $superassetItem->add([
                    'plugin_myplugin_superassets_id' => $superassetItem->getID(),
                    'itemtype'                      => Computer::class,
                    'items_id'                      => $computer->getID()
                ]);

                return true;
            
            case 'plugin_myplugin_superassets_id':
                $computer = new Computer();
                $computer->getFromDB($input['computer_id']);

                $superassetItem = new Superasset_Item();
                $superassetItem->add([
                    'plugin_myplugin_superassets_id' => $input['plugin_myplugin_superassets_id'],
                    'itemtype'                      => Computer::class,
                    'items_id'                      => $computer->getID()
                ]);

                return true;
        }
    }

    static function cronInfo($name)
    {
        Toolbox::logDebug($name);
        switch ($name) {
            case 'myaction':
                return ['description' => __('action desc', 'myplugin')];
        }
        return [];
    }

    static function cronmyaction($task = NULL)
    {
        Toolbox::logDebug($task);
        $superasset = new Superasset();
        $superasset->add([
            'name' => 'Superasset',
            'is_deleted' => 0,
        ]);


        return true;
    }
}
