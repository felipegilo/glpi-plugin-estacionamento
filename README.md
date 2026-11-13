# Plugin Estacionamento para GLPI

Plugin para o **GLPI 11 e 12** que gerencia as vagas de estacionamento da empresa e os veículos dos usuários.

## Funcionalidades

- **Vagas**: identificação, tipo (Comum, Moto, PCD, Idoso, Visitante, Diretoria, Elétrico), localização, usuário atribuído e status.
- **Veículos**: nome, placa, chassi, modelo, cor, tipo, proprietário e vaga.
- **Atribuição automática**: ao salvar um veículo com proprietário e vaga, a vaga é atribuída ao proprietário e passa a *Atribuída*. Ao trocar ou excluir o veículo, a vaga volta a *Livre* (se nenhum outro veículo do mesmo usuário a usa).
- **Status colorido** na lista: Livre (verde), Atribuída (azul), Em manutenção (laranja).
- **Lista de Vagas** com: nome, tipo, status, usuário, setor (localização do usuário), celular, número administrativo, carro e placa.
- **Lista de Veículos** com: nome, placa, proprietário e vaga.
- **Validações**: vaga e placa únicas, chassi único, vaga em manutenção ou de outro usuário não pode ser associada, vaga exige proprietário.
- No cadastro do veículo, o campo *Vaga* lista apenas vagas **livres** (mais a vaga atual ao editar).
- Histórico de alterações e permissões por perfil (aba *Estacionamento* em Administração > Perfis).

## Requisitos

| GLPI | PHP |
|------|-----|
| 11.0.x | 8.2 ou superior |
| 12.0.x | 8.3 ou superior (exigência do GLPI 12; ele também pede MariaDB 10.11 ou MySQL 8.0) |

O mesmo código funciona nas duas versões (testado em GLPI 11.0.7 e GLPI 12.0.2, ambos com PHP 8.4). Detalhes na seção *Compatibilidade GLPI 11 / 12*.

## Instalação

1. Copie a pasta para o diretório de plugins do GLPI. O nome da pasta deve ser `estacionamento`:

   ```bash
   cd /var/www/html/glpi/plugins
   git clone https://github.com/felipegilo/glpi-plugin-estacionamento.git estacionamento
   chown -R www-data:www-data estacionamento
   ```

2. No GLPI, vá em **Configurar > Plugins**, clique em **Instalar** e depois em **Ativar**.
   Ou pelo console (os nomes dos comandos mudaram no GLPI 12):

   ```bash
   # GLPI 11
   sudo -u www-data php bin/console glpi:plugin:install estacionamento --username=<usuario>
   sudo -u www-data php bin/console glpi:plugin:activate estacionamento

   # GLPI 12
   sudo -u www-data php bin/console plugin:install estacionamento --username=<usuario>
   sudo -u www-data php bin/console plugin:activate estacionamento
   ```

3. Em **Administração > Perfis**, abra o perfil desejado, aba **Estacionamento**, e conceda os direitos.
   Se instalar pelo console, confira se o seu perfil recebeu o direito.

4. Acesse em **Ferramentas > Vagas** e **Ferramentas > Veículos**.

## Atualização

Ao mudar a versão do plugin, o GLPI o coloca em "Para atualizar" e o menu some até a atualização. Depois de atualizar os arquivos, clique em **Instalar/Atualizar** e **Ativar** em *Configurar > Plugins* (ou use os comandos do console acima).

## Compatibilidade GLPI 11 / 12

- **Propriedades tipadas:** no GLPI 12 as propriedades `CommonDBTM::$dohistory` e `CommonGLPI::$rightname` passaram a ser tipadas (`bool` e `string`); no GLPI 11 não têm tipo. O PHP exige o mesmo tipo na classe filha, então o plugin usa uma classe base intermediária (`src/Compat/BaseItem.php`) que declara `$rightname` conforme a versão detectada por reflexão, e define `$dohistory` no construtor.
- **Assinaturas de métodos:** todos os métodos sobrescritos têm assinatura idêntica nas duas versões.
- **Console:** os comandos `glpi:plugin:*` do GLPI 11 viraram `plugin:*` no GLPI 12.
- **Reautenticação:** o GLPI 12 pede a senha novamente ao abrir *Administração > Perfis* (recurso do núcleo). Depois disso a aba **Estacionamento** aparece normalmente.

## Estrutura

```
estacionamento/
├── setup.php        # registro, versão, menu
├── hook.php         # instalação/atualização/remoção das tabelas
├── front/           # páginas de lista e formulário
├── src/             # classes (Vaga, Veiculo, Profile) e src/Compat/BaseItem.php
└── templates/       # formulários Twig
```

## Tabelas

- `glpi_plugin_estacionamento_vagas`
- `glpi_plugin_estacionamento_veiculos`

A desinstalação remove essas tabelas e os direitos do plugin.

## Licença

GPLv2+ (mesma licença do GLPI). Adicione o arquivo `LICENSE` antes de publicar, se desejar.
