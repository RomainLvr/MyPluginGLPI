<?php
namespace GlpiPlugin\Myplugin;

use \NotificationTarget;

class NotificationTargetSuperasset extends NotificationTarget
{

    function getEvents()
    {
        return [
            'my_event_key' => __('My event label', 'myplugin')
        ];
    }

    function getDatasForTemplate($event, $options = [])
    {
        $this->datas['##myplugin.name##'] = __('Name');
    }
}