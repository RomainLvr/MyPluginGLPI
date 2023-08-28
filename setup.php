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

define('PLUGIN_MYPLUGIN_VERSION', '0.0.2');

// Minimal GLPI version, inclusive
define("PLUGIN_MYPLUGIN_MIN_GLPI_VERSION", "10.0.0");
// Maximum GLPI version, exclusive
define("PLUGIN_MYPLUGIN_MAX_GLPI_VERSION", "10.0.99");

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_myplugin()
{
    global $PLUGIN_HOOKS;
    $PLUGIN_HOOKS['csrf_compliant']['myplugin'] = true;

    // add menu hook
    $PLUGIN_HOOKS['menu_toadd']['myplugin'] = [
        // insert into 'plugin menu'
        'plugins' => GlpiPlugin\Myplugin\Superasset::class
    ];

    $PLUGIN_HOOKS['pre_item_update']['myplugin'] = [
        GlpiPlugin\Myplugin\Superasset::class => [
            GlpiPlugin\Myplugin\Superasset_Item::class, 'mypluginPreItemUpdate'
        ]
    ];

    // callback a function (declared in hook.php)
    $PLUGIN_HOOKS['item_update']['myplugin'] = [
        'Computer' => 'myplugin_computer_updated'
    ];

    // callback a class method
    $PLUGIN_HOOKS['item_add']['myplugin'] = [
        'Computer' => [
            GlpiPlugin\Myplugin\Superasset::class, 'computerUpdated'
        ]
    ];

    $PLUGIN_HOOKS['pre_item_purge']['myplugin'] = [
        'Computer' => [
            GlpiPlugin\Myplugin\Superasset::class, 'computerPurged'
        ]
    ];


    Plugin::registerClass(GlpiPlugin\Myplugin\Superasset_Item::class, [
        'addtabon' => 'Computer'
    ]);

    // css & js
    $PLUGIN_HOOKS['add_css']['myplugin'] = 'myplugin.css';
    $PLUGIN_HOOKS['add_javascript']['myplugin'] = [
        'js/common.js',
    ];

    // on ticket page (in edition)
    if (
        strpos($_SERVER['REQUEST_URI'], "ticket.form.php") !== false
        && isset($_GET['id'])
    ) {
        $PLUGIN_HOOKS['add_javascript']['myplugin'][] = 'js/ticket.js.php';
    }

    $PLUGIN_HOOKS['pre_item_form']['myplugin'] = [
        GlpiPlugin\Myplugin\Superasset::class, 'preItemFormComputer'
    ];

    Plugin::registerClass(GlpiPlugin\Myplugin\Config::class, [
        'addtabon' => 'Config'
    ]);

    \Plugin::registerClass(GlpiPlugin\Myplugin\Profile::class, [
        'addtabon' => \Profile::class
    ]);

    $PLUGIN_HOOKS['use_massive_action']['myplugin'] = 1;

    \Plugin::registerClass(GlpiPlugin\Myplugin\Superasset::class, [
        'notificationtemplates_types' => true
    ]);

    $PLUGIN_HOOKS['dashboard_types']['myplugin'] = [
        GlpiPlugin\Myplugin\Dashboard::class => 'getTypes',
    ];

    // add new cards to the dashboard
    $PLUGIN_HOOKS['dashboard_cards']['myplugin'] = [
        GlpiPlugin\Myplugin\Dashboard::class => 'getCards',
    ];
}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_myplugin()
{
    return [
        'name'           => 'myplugin',
        'version'        => PLUGIN_MYPLUGIN_VERSION,
        'author'         => '<a href="http://www.teclib.com">Teclib\'</a>',
        'license'        => '',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_MYPLUGIN_MIN_GLPI_VERSION,
                'max' => PLUGIN_MYPLUGIN_MAX_GLPI_VERSION,
            ]
        ]
    ];
}

/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_myplugin_check_prerequisites()
{
    return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_myplugin_check_config($verbose = false)
{
    if (true) { // Your configuration check
        return true;
    }

    if ($verbose) {
        echo __('Installed / not configured', 'myplugin');
    }
    return false;
}
