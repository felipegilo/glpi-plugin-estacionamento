<?php

use GlpiPlugin\Estacionamento\Vaga;

include('../../../inc/includes.php');

Session::checkLoginUser();

$item = new Vaga();

if (isset($_POST['add'])) {
    $item->check(-1, CREATE, $_POST);
    if ($newID = $item->add($_POST)) {
        if ($_SESSION['glpibackcreated']) {
            Html::redirect($item->getLinkURL());
        }
    }
    Html::back();
} elseif (isset($_POST['update'])) {
    $item->check($_POST['id'], UPDATE);
    $item->update($_POST);
    Html::back();
} elseif (isset($_POST['delete'])) {
    $item->check($_POST['id'], DELETE);
    $item->delete($_POST);
    $item->redirectToList();
} elseif (isset($_POST['restore'])) {
    $item->check($_POST['id'], DELETE);
    $item->restore($_POST);
    $item->redirectToList();
} elseif (isset($_POST['purge'])) {
    $item->check($_POST['id'], PURGE);
    $item->delete($_POST, true);
    $item->redirectToList();
}

Vaga::displayFullPageForItem($_GET['id'] ?? 0, ['tools', 'estacionamento_vagas'], [
    'formoptions' => "data-track-changes='true'",
]);
