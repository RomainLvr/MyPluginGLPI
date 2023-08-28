<?php

/**
 * -------------------------------------------------------------------------
 * myplugin plugin for GLPI
 * Copyright (C) 2023 by the myplugin Development Team.
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * --------------------------------------------------------------------------
 */

use GlpiPlugin\Myplugin\Superasset;
use GlpiPlugin\Myplugin\Superasset_Item;

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_myplugin_install()
{
    global $DB;

    $default_charset   = DBConnection::getDefaultCharset();
    $default_collation = DBConnection::getDefaultCollation();

    //instanciate migration with version
    $migration = new \Migration(PLUGIN_MYPLUGIN_VERSION);

    //Create table only if it does not exists yet!
    $table = GlpiPlugin\Myplugin\Superasset::getTable();
    $itemsTable = GlpiPlugin\Myplugin\Superasset_Item::getTable();
    if (!$DB->tableExists($table)) {
        //table creation query
        $query = "CREATE TABLE `$table` (
                  `id`         int unsigned NOT NULL AUTO_INCREMENT,
                  `is_deleted` TINYINT NOT NULL DEFAULT '0',
                  `name`      VARCHAR(255) NOT NULL,
                  PRIMARY KEY  (`id`)
                 ) ENGINE=MyISAM
                 DEFAULT CHARSET={$default_charset}
                 COLLATE={$default_collation}";
        $DB->queryOrDie($query, $DB->error());
    }
    // Set the migration for the table update
    else {
        // $migration->addField(
        //     $table,
        //     'fieldname',
        //     'string'
        // );

        // // missing index
        // $migration->addKey(
        //     $table,
        //     'fieldname'
        // );
    }
    if (!$DB->tableExists($itemsTable)) {
        $query = "CREATE TABLE `$itemsTable` (
            `id`         int unsigned NOT NULL AUTO_INCREMENT,
            `plugin_myplugin_superassets_id` int unsigned NOT NULL,
            `itemtype` VARCHAR(255) NOT NULL,
            `items_id` int unsigned NOT NULL,
            PRIMARY KEY  (`id`),
            FOREIGN KEY (`plugin_myplugin_superassets_id`) REFERENCES `$table`(`id`)
           ) ENGINE=MyISAM
           DEFAULT CHARSET={$default_charset}
           COLLATE={$default_collation}";
        $DB->queryOrDie($query, $DB->error());
    }

    //execute the whole migration
    $migration->executeMigration();


    // //Add search options
    // $setupdisplay = new DisplayPreference();

    // $setupdisplay->add(DisplayPreference::getSearchOptionsToAdd(
    //     Superasset::class,
    // ), true);


    \Config::setConfigurationValues('plugin:myplugin', [
        'myplugin_computer_tab' => 1,
        'myplugin_computer_form' => 1,
    ]);

    // add rights to current profile
    foreach (GlpiPlugin\Myplugin\Profile::getAllRights() as $right) {
        \ProfileRight::addProfileRights([$right['field']]);
    }

    \CronTask::register(
        'PluginMypluginSuperasset',
        'myaction',
        HOUR_TIMESTAMP,
        [
            'comment'   => '',
            'mode'      => \CronTask::MODE_EXTERNAL
        ]
    );

    return true;
}


/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_myplugin_uninstall()
{

    // //Delete search options
    // $setupdisplay = new DisplayPreference();

    // $setupdisplay->delete(DisplayPreference::getSearchOptionsToAdd(
    //     Superasset::class,
    // ), false, true, true);

    $config = new \Config();
    $config->deleteByCriteria(['context' => 'plugin:myplugin']);

    foreach (GlpiPlugin\Myplugin\Profile::getAllRights() as $right) {
        \ProfileRight::deleteProfileRights([$right['field']]);
    }

    global $DB;

    $tables = [
        GlpiPlugin\Myplugin\Superasset::getTable(),
        GlpiPlugin\Myplugin\Superasset_Item::getTable(),
    ];

    foreach ($tables as $table) {
        if ($DB->tableExists($table)) {
            $DB->queryOrDie(
                "DROP TABLE `$table`",
                $DB->error()
            );
        }
    }

    return true;
}

function plugin_myplugin_getAddSearchOptionsNew($itemtype)
{
    $sopt = [];

    if ($itemtype == 'Computer') {
        $sopt[] = [
            'id'           => 12345,
            'table'        => GlpiPlugin\Myplugin\Superasset::getTable(),
            'field'        => 'name',
            'name'         => __('Associated Superassets', 'myplugin'),
            'datatype'     => 'itemlink',
            'forcegroupby' => true,
            'usehaving'    => true,
            'joinparams'   => [
                'beforejoin' => [
                    'table'      => GlpiPlugin\Myplugin\Superasset_Item::getTable(),
                    'joinparams' => [
                        'jointype' => 'itemtype_item',
                    ]
                ]
            ]
        ];
    }

    return $sopt;
}

function mypluginPreItemUpdate(CommonDBTM $item)
{
    if ($item::getType() == Superasset::getType()) {
        $item->prepareInputForUpdate($item->input);
    }

    return true;
}

function hookCallback(\CommonDBTM $item)
{
    // if we need to stop the process (valid for pre* hooks)
    if ($item->getType() == Superasset::getType()) {
        // clean input
        $item->input = [];

        // store a message in session for warn user
        \Session::addMessageAfterRedirect('Action forbidden because...');

        return;
    }
}

function computerPurged(CommonDBTM $item)
{
    if ($item::getType() == Computer::getType()) {
        $superassets = new Superasset();
        $superassets->getFromDB($item->fields['id']);
        $superassets->delete($item->fields['id']);
    }
}

function plugin_myplugin_MassiveActions($type)
{
   $actions = [];
   switch ($type) {
      case \Computer::class:
         $class = GlpiPlugin\Myplugin\Superasset::class;
         $key   = 'addSuperAsset';
         $label = __("Add SuperAsset", 'myplugin');
         $actions[$class.\MassiveAction::CLASS_ACTION_SEPARATOR.$key] = $label;

         break;
   }
   return $actions;
}