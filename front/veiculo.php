<?php

use GlpiPlugin\Estacionamento\Veiculo;

include('../../../inc/includes.php');

Session::checkRight(Veiculo::$rightname, READ);

Html::header(Veiculo::getTypeName(Session::getPluralNumber()), '', 'tools', 'estacionamento_veiculos');
Search::show(Veiculo::class);
Html::footer();
