<?php
use GlpiPlugin\Myplugin\Superasset;

include ('../../../inc/includes.php');

Html::header(Superasset::getTypeName(),
             $_SERVER['PHP_SELF'],
             "plugins",
             Superasset::class,
             "superasset");
\Search::show(Superasset::class);
\Html::footer();