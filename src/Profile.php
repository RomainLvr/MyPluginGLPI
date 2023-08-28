<?php
namespace GlpiPlugin\Myplugin;

use CommonDBTM;
use CommonGLPI;
use Glpi\Application\View\TemplateRenderer;
use Html;

class Profile extends CommonDBTM
{
    public static $rightname = 'profile';

    static function getTypeName($nb = 0)
    {
        return __("My plugin", 'myplugin');
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (
            $item instanceof \Profile
            && $item->getField('id')
        ) {
            return self::createTabEntry(self::getTypeName());
        }
        return '';
    }

    static function displayTabContentForItem(
        CommonGLPI $item,
        $tabnum = 1,
        $withtemplate = 0
    ) {
        if (
            $item instanceof \Profile
            && $item->getField('id')
        ) {
            return self::showForProfile($item->getID());
        }

        return true;
    }

    static function getAllRights($all = false)
    {
        $rights = [
            [
                'itemtype' => Superasset::class,
                'label'    => Superasset::getTypeName(),
                'field'    => 'myplugin::superasset'
            ]
        ];

        return $rights;
    }


    static function showForProfile($profiles_id = 0)
    {
        $profile = new \Profile();
        $profile->getFromDB($profiles_id);

        TemplateRenderer::getInstance()->display('@myplugin/profile.html.twig', [
            'can_edit' => self::canUpdate(),
            'profile'  => $profile,
            'rights'   => self::getAllRights()
        ]);
    }
}