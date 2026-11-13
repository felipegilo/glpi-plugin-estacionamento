<?php

use Glpi\Plugin\Hooks;
use GlpiPlugin\Estacionamento\Profile as EstProfile;
use GlpiPlugin\Estacionamento\Vaga;
use GlpiPlugin\Estacionamento\Veiculo;

define('PLUGIN_ESTACIONAMENTO_VERSION', '0.2.1');
define('PLUGIN_ESTACIONAMENTO_MIN_GLPI', '11.0.0');
define('PLUGIN_ESTACIONAMENTO_MAX_GLPI', '12.0.99');

function plugin_init_estacionamento()
{
    /** @var array $PLUGIN_HOOKS */
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['estacionamento'] = true;

    if (!Plugin::isPluginActive('estacionamento')) {
        return;
    }

    Plugin::registerClass(Vaga::class);
    Plugin::registerClass(Veiculo::class);
    Plugin::registerClass(EstProfile::class, ['addtabon' => Profile::class]);

    if (Session::haveRight(Vaga::$rightname, READ)) {
        $PLUGIN_HOOKS[Hooks::MENU_TOADD]['estacionamento'] = ['tools' => Vaga::class];
    }
}

function plugin_version_estacionamento()
{
    return [
        'name'         => 'Estacionamento',
        'version'      => PLUGIN_ESTACIONAMENTO_VERSION,
        'author'       => 'TI',
        'license'      => 'GPLv2+',
        'homepage'     => '',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_ESTACIONAMENTO_MIN_GLPI,
                'max' => PLUGIN_ESTACIONAMENTO_MAX_GLPI,
            ],
            'php'  => ['min' => '8.2'],
        ],
    ];
}
