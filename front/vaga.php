<?php

use GlpiPlugin\Estacionamento\Vaga;

include('../../../inc/includes.php');

Session::checkRight(Vaga::$rightname, READ);

Html::header(Vaga::getTypeName(Session::getPluralNumber()), '', 'tools', 'estacionamento_vagas');
Search::show(Vaga::class);
Html::footer();
