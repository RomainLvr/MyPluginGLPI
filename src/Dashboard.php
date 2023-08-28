<?php

namespace GlpiPlugin\Myplugin;

class Dashboard
{
    static function getTypes()
    {
        return [
            'example' => [
                'label'    => __("Plugin Example", 'myplugin'),
                'function' => __class__ . "::cardWidget",
                'image'    => "https://via.placeholder.com/100x86?text=example",
            ],
            'example_static' => [
                'label'    => __("Plugin Example (static)", 'myplugin'),
                'function' => __class__ . "::cardWidgetWithoutProvider",
                'image'    => "https://via.placeholder.com/100x86?text=example+static",
            ],
        ];
    }

    static function getCards($cards = [])
    {
        if (is_null($cards)) {
            $cards = [];
        }
        $new_cards =  [
            'plugin_example_card' => [
                'widgettype'   => ["example"],
                'label'        => __("Plugin Example card"),
                'provider'     => "PluginExampleExample::cardDataProvider",
            ],
            'plugin_example_card_without_provider' => [
                'widgettype'   => ["example_static"],
                'label'        => __("Plugin Example card without provider"),
            ],
            'plugin_example_card_with_core_widget' => [
                'widgettype'   => ["bigNumber"],
                'label'        => __("Plugin Example card with core provider"),
                'provider'     => "PluginExampleExample::cardBigNumberProvider",
            ],
        ];

        return array_merge($cards, $new_cards);
   }

    static function cardWidget(array $params = [])
    {
        $default = [
            'data'  => [],
            'title' => '',
            // this property is "pretty" mandatory,
            // as it contains the colors selected when adding widget on the grid send
            // without it, your card will be transparent
            'color' => '',
        ];
        $p = array_merge($default, $params);

        // you need to encapsulate your html in div.card to benefit core style
        $html = "<div class='card' style='background-color: {$p["color"]};'>";
        $html.= "<h2>{$p['title']}</h2>";
        $html.= "<ul>";
        foreach ($p['data'] as $line) {
            $html.= "<li>$line</li>";
        }
        $html.= "</ul>";
        $html.= "</div>";

        return $html;
    }

    static function cardWidgetWithoutProvider(array $params = [])
    {
      $default = [
         // this property is "pretty" mandatory,
         // as it contains the colors selected when adding widget on the grid send
         // without it, your card will be transparent
         'color' => '',
      ];
      $p = array_merge($default, $params);

      // you need to encapsulate your html in div.card to benefit core style
      $html = "<div class='card' style='background-color: {$p["color"]};'>
                  static html (+optional javascript) as card is not matched with a data provider
                  <img src='https://www.linux.org/images/logo.png'>
               </div>";

      return $html;
   }

    static function cardBigNumberProvider(array $params = [])
    {
        $default_params = [
            'label' => null,
            'icon'  => null,
        ];
        $params = array_merge($default_params, $params);

        return [
            'number' => rand(),
            'url'    => "https://www.linux.org/",
            'label'  => "plugin example - some text",
            'icon'   => "fab fa-linux", // font awesome icon
        ];
   }
}