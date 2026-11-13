<?php

namespace GlpiPlugin\Estacionamento;

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Estacionamento\Compat\BaseItem;
use Session;

class Veiculo extends BaseItem
{
    public function __construct()
    {
        parent::__construct();
        // Historico de alteracoes (propriedade tipada no GLPI 12: por isso e atribuida aqui)
        $this->dohistory = true;
    }

    public static function getTypeName($nb = 0)
    {
        return _n('Veículo', 'Veículos', $nb);
    }

    public static function getIcon()
    {
        return 'ti ti-car';
    }

    public static function getTipos(): array
    {
        return [1 => 'Carro', 2 => 'Moto', 3 => 'Outro'];
    }

    public function rawSearchOptions()
    {
        $t = self::getTable();
        $tab = [];
        $tab[] = ['id' => 'common', 'name' => self::getTypeName(Session::getPluralNumber())];
        $tab[] = ['id' => '1', 'table' => $t, 'field' => 'name', 'name' => __('Name'), 'datatype' => 'itemlink', 'massiveaction' => false];
        $tab[] = ['id' => '9', 'table' => $t, 'field' => 'placa', 'name' => 'Placa', 'datatype' => 'string'];
        $tab[] = ['id' => '10', 'table' => $t, 'field' => 'chassi', 'name' => 'Chassi', 'datatype' => 'string'];
        $tab[] = ['id' => '2', 'table' => $t, 'field' => 'id', 'name' => __('ID'), 'datatype' => 'number', 'massiveaction' => false];
        $tab[] = ['id' => '3', 'table' => $t, 'field' => 'modelo', 'name' => 'Modelo', 'datatype' => 'string'];
        $tab[] = ['id' => '4', 'table' => $t, 'field' => 'cor', 'name' => 'Cor', 'datatype' => 'string'];
        $tab[] = ['id' => '5', 'table' => $t, 'field' => 'tipo', 'name' => 'Tipo', 'datatype' => 'specific', 'searchtype' => ['equals', 'notequals']];
        $tab[] = ['id' => '6', 'table' => 'glpi_users', 'field' => 'name', 'name' => 'Proprietário', 'datatype' => 'dropdown'];
        $tab[] = [
            'id'            => '7',
            'table'         => Vaga::getTable(),
            'field'         => 'name',
            'name'          => 'Vaga vinculada',
            'datatype'      => 'dropdown',
            'forcegroupby'  => true,
            'joinparams'    => ['jointype' => 'standard'],
        ];
        $tab[] = [
            'id'            => '11',
            'table'         => Vaga::getTable(),
            'field'         => 'name',
            'name'          => 'Vaga',
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
        $tab[] = ['id' => '8', 'table' => $t, 'field' => 'comment', 'name' => __('Comments'), 'datatype' => 'text'];
        $tab[] = ['id' => '19', 'table' => $t, 'field' => 'date_mod', 'name' => __('Last update'), 'datatype' => 'datetime', 'massiveaction' => false];
        $tab[] = ['id' => '80', 'table' => 'glpi_entities', 'field' => 'completename', 'name' => __('Entity'), 'datatype' => 'dropdown'];
        return $tab;
    }

    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        if ($field === 'tipo') {
            return self::getTipos()[$values[$field]] ?? '';
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }

    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;
        if ($field === 'tipo') {
            return \Dropdown::showFromArray($name, self::getTipos(), $options + ['value' => $values[$field]]);
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
        TemplateRenderer::getInstance()->display('@estacionamento/veiculo.html.twig', [
            'item'   => $this,
            'params' => $options,
            'tipos'  => self::getTipos(),
        ]);
        return true;
    }

    private function normalize(array $input, bool $is_add): array|false
    {
        if (isset($input['placa'])) {
            $input['placa'] = strtoupper(preg_replace('/[\s\-]/', '', $input['placa']));
        }
        if (isset($input['chassi'])) {
            $input['chassi'] = strtoupper(preg_replace('/\s/', '', $input['chassi']));
        }
        if (isset($input['name'])) {
            $input['name'] = trim($input['name']);
        }

        $placa = $input['placa'] ?? ($is_add ? '' : $this->fields['placa']);
        if ($placa === '') {
            Session::addMessageAfterRedirect('Informe a placa do veículo.', false, ERROR);
            return false;
        }

        // Nome opcional: se vazio, usa "Modelo - PLACA"
        $name = $input['name'] ?? ($is_add ? '' : $this->fields['name']);
        if ($name === '' || $name === null) {
            $modelo = trim($input['modelo'] ?? ($is_add ? '' : ($this->fields['modelo'] ?? '')));
            $input['name'] = trim($modelo . ' - ' . $placa, ' -');
        }
        return $input;
    }

    private function isDuplicate(string $field, string $value, int $exclude_id = 0): bool
    {
        if ($value === '') {
            return false;
        }
        return countElementsInTable(self::getTable(), [
            $field => $value,
            'id'   => ['<>', $exclude_id],
        ]) > 0;
    }

    private function validateUnique(array $input, int $id): bool
    {
        if (isset($input['placa']) && $this->isDuplicate('placa', $input['placa'], $id)) {
            Session::addMessageAfterRedirect('Já existe um veículo com essa placa.', false, ERROR);
            return false;
        }
        if (isset($input['chassi']) && $this->isDuplicate('chassi', $input['chassi'], $id)) {
            Session::addMessageAfterRedirect('Já existe um veículo com esse chassi.', false, ERROR);
            return false;
        }
        return true;
    }

    /**
     * Valida a vaga escolhida: exige proprietario, vaga fora de manutencao
     * e nao ocupada por outro usuario.
     */
    private function validateVaga(array $input, bool $is_add): bool
    {
        $vaga_id = (int) ($input['plugin_estacionamento_vagas_id'] ?? ($is_add ? 0 : $this->fields['plugin_estacionamento_vagas_id']));
        $user_id = (int) ($input['users_id'] ?? ($is_add ? 0 : $this->fields['users_id']));
        if ($vaga_id <= 0) {
            return true;
        }
        if ($user_id <= 0) {
            Session::addMessageAfterRedirect('Para associar uma vaga, informe o proprietário do veículo.', false, ERROR);
            return false;
        }
        $vaga = new Vaga();
        if (!$vaga->getFromDB($vaga_id)) {
            Session::addMessageAfterRedirect('Vaga não encontrada.', false, ERROR);
            return false;
        }
        if ((int) $vaga->fields['status'] === Vaga::STATUS_MANUTENCAO) {
            Session::addMessageAfterRedirect('A vaga selecionada está em manutenção.', false, ERROR);
            return false;
        }
        $dono = (int) $vaga->fields['users_id'];
        if ($dono > 0 && $dono !== $user_id) {
            Session::addMessageAfterRedirect('A vaga selecionada já está atribuída a outro usuário.', false, ERROR);
            return false;
        }
        return true;
    }

    /** Atribui a vaga ao usuario (se ainda nao estiver). */
    public static function atribuirVaga(int $vaga_id, int $user_id): void
    {
        if ($vaga_id <= 0 || $user_id <= 0) {
            return;
        }
        $vaga = new Vaga();
        if ($vaga->getFromDB($vaga_id) && (int) $vaga->fields['users_id'] !== $user_id) {
            $vaga->update(['id' => $vaga_id, 'users_id' => $user_id]);
        }
    }

    /**
     * Libera a vaga se ela estava atribuida a esse usuario e nenhum outro
     * veiculo dele continua apontando para ela.
     */
    private function liberarVaga(int $vaga_id, int $user_id): void
    {
        if ($vaga_id <= 0 || $user_id <= 0) {
            return;
        }
        $vaga = new Vaga();
        if (!$vaga->getFromDB($vaga_id) || (int) $vaga->fields['users_id'] !== $user_id) {
            return;
        }
        $outros = countElementsInTable(self::getTable(), [
            'plugin_estacionamento_vagas_id' => $vaga_id,
            'users_id'                       => $user_id,
            'id'                             => ['<>', (int) $this->fields['id']],
        ]);
        if ($outros === 0) {
            $vaga->update(['id' => $vaga_id, 'users_id' => 0]);
        }
    }

    public function prepareInputForAdd($input)
    {
        $input = $this->normalize($input, true);
        if ($input === false || !$this->validateUnique($input, 0) || !$this->validateVaga($input, true)) {
            return false;
        }
        return $input;
    }

    public function post_addItem()
    {
        self::atribuirVaga(
            (int) $this->fields['plugin_estacionamento_vagas_id'],
            (int) $this->fields['users_id']
        );
    }

    public function prepareInputForUpdate($input)
    {
        $input = $this->normalize($input, false);
        if ($input === false || !$this->validateUnique($input, (int) $this->fields['id']) || !$this->validateVaga($input, false)) {
            return false;
        }
        return $input;
    }

    public function post_updateItem($history = true)
    {
        $nova_vaga = (int) $this->fields['plugin_estacionamento_vagas_id'];
        $novo_user = (int) $this->fields['users_id'];
        $velha_vaga = (int) ($this->oldvalues['plugin_estacionamento_vagas_id'] ?? $nova_vaga);
        $velho_user = (int) ($this->oldvalues['users_id'] ?? $novo_user);

        if ($velha_vaga !== $nova_vaga || $velho_user !== $novo_user) {
            $this->liberarVaga($velha_vaga, $velho_user);
        }
        self::atribuirVaga($nova_vaga, $novo_user);
    }

    public function post_purgeItem()
    {
        $this->liberarVaga(
            (int) $this->fields['plugin_estacionamento_vagas_id'],
            (int) $this->fields['users_id']
        );
    }
}