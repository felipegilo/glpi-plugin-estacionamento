<?php

use GlpiPlugin\Estacionamento\Vaga;
use GlpiPlugin\Estacionamento\Veiculo;

function plugin_estacionamento_install()
{
    /** @var DBmysql $DB */
    global $DB;

    $charset   = DBConnection::getDefaultCharset();
    $collation = DBConnection::getDefaultCollation();
    $sign      = DBConnection::getDefaultPrimaryKeySignOption();

    $vagas = Vaga::getTable();
    if (!$DB->tableExists($vagas)) {
        $DB->doQuery("CREATE TABLE `$vagas` (
            `id` int {$sign} NOT NULL AUTO_INCREMENT,
            `entities_id` int {$sign} NOT NULL DEFAULT 0,
            `is_recursive` tinyint NOT NULL DEFAULT 0,
            `name` varchar(255) DEFAULT NULL,
            `locations_id` int {$sign} NOT NULL DEFAULT 0,
            `tipo` int NOT NULL DEFAULT 1,
            `status` int NOT NULL DEFAULT 1,
            `users_id` int {$sign} NOT NULL DEFAULT 0,
            `comment` text,
            `date_creation` timestamp NULL DEFAULT NULL,
            `date_mod` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `entities_id` (`entities_id`),
            KEY `locations_id` (`locations_id`),
            KEY `users_id` (`users_id`),
            KEY `status` (`status`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation} ROW_FORMAT=DYNAMIC;");
    }

    $veiculos = Veiculo::getTable();
    if (!$DB->tableExists($veiculos)) {
        $DB->doQuery("CREATE TABLE `$veiculos` (
            `id` int {$sign} NOT NULL AUTO_INCREMENT,
            `entities_id` int {$sign} NOT NULL DEFAULT 0,
            `is_recursive` tinyint NOT NULL DEFAULT 0,
            `name` varchar(255) DEFAULT NULL,
            `placa` varchar(20) DEFAULT NULL,
            `chassi` varchar(50) DEFAULT NULL,
            `modelo` varchar(255) DEFAULT NULL,
            `cor` varchar(100) DEFAULT NULL,
            `tipo` int NOT NULL DEFAULT 1,
            `users_id` int {$sign} NOT NULL DEFAULT 0,
            `plugin_estacionamento_vagas_id` int {$sign} NOT NULL DEFAULT 0,
            `comment` text,
            `date_creation` timestamp NULL DEFAULT NULL,
            `date_mod` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `entities_id` (`entities_id`),
            KEY `placa` (`placa`),
            KEY `chassi` (`chassi`),
            KEY `users_id` (`users_id`),
            KEY `plugin_estacionamento_vagas_id` (`plugin_estacionamento_vagas_id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation} ROW_FORMAT=DYNAMIC;");
    } else {
        // Upgrade 0.1.0 -> 0.2.0: placa e chassi proprios
        if (!$DB->fieldExists($veiculos, 'placa')) {
            $DB->doQuery("ALTER TABLE `$veiculos` MODIFY `name` varchar(255) DEFAULT NULL,
                ADD `placa` varchar(20) DEFAULT NULL AFTER `name`,
                ADD `chassi` varchar(50) DEFAULT NULL AFTER `placa`,
                ADD KEY `placa` (`placa`), ADD KEY `chassi` (`chassi`)");
            $DB->doQuery("UPDATE `$veiculos` SET `placa` = `name`");
        }
    }

    // Direitos: cria e concede tudo ao perfil atual (Super-Admin na instalacao)
    $novo_direito = countElementsInTable('glpi_profilerights', ['name' => Vaga::$rightname]) === 0;
    if ($novo_direito) {
        ProfileRight::addProfileRights([Vaga::$rightname]);
    }
    $profile_id = $_SESSION['glpiactiveprofile']['id'] ?? 0;
    if ($novo_direito && $profile_id) {
        ProfileRight::updateProfileRights($profile_id, [Vaga::$rightname => ALLSTANDARDRIGHT]);
        $_SESSION['glpiactiveprofile'][Vaga::$rightname] = ALLSTANDARDRIGHT;
    }

    // Colunas padrao da lista de vagas (preferencia global)
    $prefs_table = 'glpi_displaypreferences';
    if (countElementsInTable($prefs_table, ['itemtype' => Vaga::class, 'users_id' => 0]) === 0) {
        // nome, tipo, status, usuario, setor, celular, numero administrativo, carro, placa
        foreach ([1, 3, 4, 6, 11, 12, 13, 14, 15] as $rank => $num) {
            $DB->insert($prefs_table, [
                'itemtype' => Vaga::class,
                'num'      => $num,
                'rank'     => $rank + 1,
                'users_id' => 0,
            ]);
        }
    }

    if (countElementsInTable($prefs_table, ['itemtype' => Veiculo::class, 'users_id' => 0]) === 0) {
        // nome, placa, usuario, vaga
        foreach ([1, 9, 6, 11] as $rank => $num) {
            $DB->insert($prefs_table, [
                'itemtype' => Veiculo::class,
                'num'      => $num,
                'rank'     => $rank + 1,
                'users_id' => 0,
            ]);
        }
    }

    return true;
}

function plugin_estacionamento_uninstall()
{
    /** @var DBmysql $DB */
    global $DB;

    foreach ([Veiculo::getTable(), Vaga::getTable()] as $table) {
        $DB->doQuery("DROP TABLE IF EXISTS `$table`");
    }

    ProfileRight::deleteProfileRights([Vaga::$rightname]);
    $DB->delete('glpi_displaypreferences', ['itemtype' => [Vaga::class, Veiculo::class]]);

    return true;
}
