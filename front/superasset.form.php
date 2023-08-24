<?php

use GlpiPlugin\Myplugin\Superasset;
use GlpiPlugin\Myplugin\Superasset_Item;

include('../../../inc/includes.php');

$supperasset = new Superasset();
$superasset_item = new Superasset_Item();

if (isset($_POST["add"])) {
    $newID = $supperasset->add($_POST);

    if ($_SESSION['glpibackcreated']) {
        \Html::redirect(Superasset::getFormURL() . "?id=" . $newID);
    }
    \Html::back();
} else if (isset($_POST["delete"])) {
    $supperasset->delete($_POST);
    $supperasset->redirectToList();
} else if (isset($_POST["restore"])) {
    $supperasset->restore($_POST);
    $supperasset->redirectToList();
} else if (isset($_POST["purge"])) {
    $supperasset->delete($_POST, 1);
    $supperasset->redirectToList();
} else if (isset($_POST["update"])) {
    if (isset($_POST["items_id"])) {
        if ($superasset_item->getFromDB($_POST["id"])) {
            if($superasset_item->getFromDB($_POST["items_id"]) == 0){
                $superasset_item->delete($_POST);
            }else{
                $superasset_item->update($_POST);
            }
        } else {
            $superasset_item->add($_POST);
        }
    } else {
        $supperasset->update($_POST);
    }
    \Html::back();
} else {
    // fill id, if missing
    isset($_GET['id'])
        ? $ID = intval($_GET['id'])
        : $ID = 0;

    // display form
    \Html::header(
        Superasset::getTypeName(),
        $_SERVER['PHP_SELF'],
        "plugins",
        Superasset::class,
        "superasset"
    );
    $supperasset->display(['id' => $ID]);
    \Html::footer();
}
