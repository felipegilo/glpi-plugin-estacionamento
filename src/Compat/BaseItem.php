<?php

namespace GlpiPlugin\Estacionamento\Compat;

/*
 * Compatibilidade GLPI 11 / GLPI 12.
 *
 * No GLPI 12 a propriedade CommonGLPI::$rightname passou a ser tipada (string);
 * no GLPI 11 ela nao tem tipo. O PHP exige que a classe filha use exatamente o
 * mesmo tipo da classe base, entao a declaracao e escolhida em tempo de execucao.
 *
 * Todas as classes de dados do plugin (Vaga, Veiculo) herdam desta base e
 * compartilham o mesmo direito: plugin_estacionamento.
 */
if ((new \ReflectionProperty(\CommonGLPI::class, 'rightname'))->hasType()) {
    abstract class BaseItem extends \CommonDBTM
    {
        public static string $rightname = 'plugin_estacionamento';
    }
} else {
    abstract class BaseItem extends \CommonDBTM
    {
        public static $rightname = 'plugin_estacionamento';
    }
}
