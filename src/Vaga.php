<?php

namespace GlpiPlugin\Estacionamento;

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Estacionamento\Compat\BaseItem;
use Session;

class Vaga extends BaseItem
{
    public function __construct()
    {
        parent::__construct();
        // Historico de alteracoes (propriedade tipada no GLPI 12: por isso e atribuida aqui)
        $this->dohistory = true;
    }

    public const STATUS_LIVRE      = 1;
    public const STATUS_ATRIBUIDA  = 2;
    public const STATUS_MANUTENCAO = 3;

    public static function getTypeName($nb = 0)
    {
        return _n('Vaga', 'Vagas', $nb);
    }

    public static function getIcon()
    {
        return 'ti ti-parking';
    }

    public static function getMenuName()
    {
        return 'Estacionamento';
    }

    public static function getTipos(): array
    {
        return [
            1 => 'Comum',
            2 => 'Moto',
            3 => 'PCD',
            4 => 'Idoso',
            5 => 'Visitante',
            6 => 'Diretoria',
            7 => 'Elétrico',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_LIVRE      => 'Livre',
            self::STATUS_ATRIBUIDA  => 'Atribuída',
            self::STATUS_MANUTENCAO => 'Em manutenção',
        ];
    }

    public static function getMenuContent()
    {
        return [
            'is_multi_entries' => true,
            'estacionamento_vagas' => [
                'title' => self::getTypeName(Session::getPluralNumber()),
                'page'  => self::getSearchURL(false),
                'icon'  => self::getIcon(),
                'links' => [
                    'search' => self::getSearchURL(false),
                    'add'    => self::getFormURL(false),
                ],
            ],
            'estacionamento_veiculos' => [
                'title' => Veiculo::getTypeName(Session::getPluralNumber()),
                'page'  => Veiculo::getSearchURL(false),
                'icon'  => Veiculo::getIcon(),
                'links' => [
                    'search' => Veiculo::getSearchURL(false),
                    'add'    => Veiculo::getFormURL(false),
                ],
            ],
        ];
    }
    public function rawSearchOptions()
    {
        $tab = [];
        $tab[] = ['id' => 'common', 'name' => self::getTypeName(Session::getPluralNumber())];
        $tab[] = [
            'id'            => '1',
            'table'         => self::getTable(),
            'field'         => 'name',
            'name'          => __('Name'),
            'datatype'      => 'itemlink',
            'massiveaction' => false,
        ];
        $tab[] = [
            'id'       => '2',
            'table'    => self::getTable(),
            'field'    => 'id',
            'name'     => __('ID'),
            'datatype' => 'number',
            'massiveaction' => false,
        ];
        $tab[] = [
            'id'       => '3',
            'table'    => self::getTable(),
            'field'    => 'tipo',
            'name'     => 'Tipo',
            'datatype' => 'specific',
            'searchtype' => ['equals', 'notequals'],
        ];
        $tab[] = [
            'id'       => '4',
            'table'    => self::getTable(),
            'field'    => 'status',
            'name'     => __('Status'),
            'datatype' => 'specific',
            'searchtype' => ['equals', 'notequals'],
        ];
        $tab[] = [
            'id'       => '5',
            'table'    => 'glpi_locations',
            'field'    => 'completename',
            'name'     => __('Location'),
            'datatype' => 'dropdown',
        ];
        $tab[] = [
            'id'       => '6',
            'table'    => 'glpi_users',
            'field'    => 'name',
            'name'     => 'Usuário atribuído',
            'datatype' => 'dropdown',
        ];
        $tab[] = [
            'id'            => '11',
            'table'         => 'glpi_locations',
            'field'         => 'completename',
            'name'          => 'Setor do usuário',
            'datatype'      => 'dropdown',
            'massiveaction' => false,
            'joinparams'    => [
                'beforejoin' => [
                    'table'      => 'glpi_users',
                    'joinparams' => ['jointype' => 'standard'],
                ],
            ],
        ];
        $tab[] = [
            'id'            => '12',
            'table'         => 'glpi_users',
            'field'         => 'mobile',
            'name'          => 'Celular do usuário',
            'datatype'      => 'string',
            'massiveaction' => false,
        ];
        $tab[] = [
            'id'            => '13',
            'table'         => 'glpi_users',
            'field'         => 'registration_number',
            'name'          => 'Número administrativo',
            'datatype'      => 'string',
            'massiveaction' => false,
        ];
        $tab[] = [
            'id'            => '14',
            'table'         => Veiculo::getTable(),
            'field'         => 'name',
            'name'          => 'Carro',
            'datatype'      => 'string',
            'forcegroupby'  => true,
            'massiveaction' => false,
            'joinparams'    => [
                'beforejoin' => [
                    'table'      => 'glpi_users',
                    'joinparams' => ['jointype' => 'standard'],
                ],
                'jointype'   => 'child',
            ],
        ];
        $tab[] = [
            'id'            => '15',
            'table'         => Veiculo::getTable(),
            'field'         => 'placa',
            'name'          => 'Placa',
            'datatype'      => 'string',
            'forcegroupby'  => true,
            'massiveaction' => false,
            'joinparams'    => [
                'beforejoin' => [
                    'table'      => 'glpi_users',
                    'joinparams' => ['jointype' => 'standard'],
                ],
                'jointype'   => 'child',
            ],
        ];
        $tab[] = [
            'id'       => '7',
            'table'    => self::getTable(),
            'field'    => 'comment',
            'name'     => __('Comments'),
            'datatype' => 'text',
        ];
        $tab[] = [
            'id'       => '19',
            'table'    => self::getTable(),
            'field'    => 'date_mod',
            'name'     => __('Last update'),
            'datatype' => 'datetime',
            'massiveaction' => false,
        ];
        $tab[] = [
            'id'       => '80',
            'table'    => 'glpi_entities',
            'field'    => 'completename',
            'name'     => __('Entity'),
            'datatype' => 'dropdown',
        ];
        return $tab;
    }

    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'tipo':
                return self::getTipos()[$values[$field]] ?? '';
            case 'status':
                $status = (int) $values[$field];
                $label  = self::getStatuses()[$status] ?? '';
                if ($label === '') {
                    return '';
                }
                $cores = [
                    self::STATUS_LIVRE      => '#2fb344', // verde
                    self::STATUS_ATRIBUIDA  => '#206bc4', // azul
                    self::STATUS_MANUTENCAO => '#f76707', // laranja
                ];
                return sprintf(
                    '<span class="badge" style="background-color:%s;color:#fff;font-weight:600">%s</span>',
                    $cores[$status] ?? '#667382',
                    htmlspecialchars($label, ENT_QUOTES)
                );
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }

    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;
        switch ($field) {
            case 'tipo':
                return \Dropdown::showFromArray($name, self::getTipos(), $options + ['value' => $values[$field]]);
            case 'status':
                return \Dropdown::showFromArray($name, self::getStatuses(), $options + ['value' => $values[$field]]);
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }

    public function defineTabs($options = [])
    {
        $tabs = [];
        $this->addDefaultFormTab($tabs);
        $this->addStandardTab(\Log::class, $tabs, $options);
        return $tabs;
    }

    public function showForm($ID, array $options = [])
    {
        $this->initForm($ID, $options);
        TemplateRenderer::getInstance()->display('@estacionamento/vaga.html.twig', [
            'item'     => $this,
            'params'   => $options,
            'tipos'    => self::getTipos(),
            'statuses' => self::getStatuses(),
        ]);
        return true;
    }

    private function normalize(array $input): array|false
    {
        if (isset($input['name'])) {
            $input['name'] = trim($input['name']);
            if ($input['name'] === '') {
                Session::addMessageAfterRedirect('Informe o número/identificação da vaga.', false, ERROR);
                return false;
            }
        }

        // Status acompanha a atribuição de usuário (exceto manutenção)
        $user   = (int) ($input['users_id'] ?? $this->fields['users_id'] ?? 0);
        $status = (int) ($input['status'] ?? $this->fields['status'] ?? self::STATUS_LIVRE);
        if ($status !== self::STATUS_MANUTENCAO && (isset($input['users_id']) || isset($input['status']))) {
            $input['status'] = $user > 0 ? self::STATUS_ATRIBUIDA : self::STATUS_LIVRE;
        }
        return $input;
    }

    private function isDuplicate(string $name, int $entity, int $exclude_id = 0): bool
    {
        return countElementsInTable(self::getTable(), [
            'name'        => $name,
            'entities_id' => $entity,
            'id'          => ['<>', $exclude_id],
        ]) > 0;
    }

    public function prepareInputForAdd($input)
    {
        $input = $this->normalize($input);
        if ($input === false) {
            return false;
        }
        if ($this->isDuplicate($input['name'], (int) ($input['entities_id'] ?? 0))) {
            Session::addMessageAfterRedirect('Já existe uma vaga com essa identificação.', false, ERROR);
            return false;
        }
        return $input;
    }

    public function prepareInputForUpdate($input)
    {
        $input = $this->normalize($input);
        if ($input === false) {
            return false;
        }
        if (isset($input['name']) && $this->isDuplicate($input['name'], (int) $this->fields['entities_id'], (int) $this->fields['id'])) {
            Session::addMessageAfterRedirect('Já existe uma vaga com essa identificação.', false, ERROR);
            return false;
        }
        return $input;
    }

    public function cleanDBonPurge()
    {
        // Desvincula veículos da vaga removida
        global $DB;
        $DB->update(Veiculo::getTable(), ['plugin_estacionamento_vagas_id' => 0], [
            'plugin_estacionamento_vagas_id' => $this->fields['id'],
        ]);
    }
}
