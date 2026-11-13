# Plugin Estacionamento para GLPI

Plugin para o **GLPI 11** que gerencia as vagas de estacionamento da empresa e os veículos dos usuários.

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

- GLPI 11.0.x
- PHP 8.2 ou superior

## Instalação

1. Copie a pasta para o diretório de plugins do GLPI. O nome da pasta deve ser `estacionamento`:

   ```bash
   cd /var/www/html/glpi/plugins
   git clone <URL-DO-REPOSITORIO> estacionamento
   chown -R www-data:www-data estacionamento
   ```

2. No GLPI, vá em **Configurar > Plugins**, clique em **Instalar** e depois em **Ativar**.
   Ou pelo console:

   ```bash
   sudo -u www-data php bin/console glpi:plugin:install estacionamento --username=<usuario>
   sudo -u www-data php bin/console glpi:plugin:activate estacionamento
   ```

3. Em **Administração > Perfis**, abra o perfil desejado, aba **Estacionamento**, e conceda os direitos.
   Se instalar pelo console, o direito não é concedido automaticamente a nenhum perfil.

4. Acesse em **Ferramentas > Vagas** e **Ferramentas > Veículos**.

## Atualização

Ao mudar a versão do plugin, o GLPI o coloca em "Para atualizar" e o menu some até a atualização. Depois de atualizar os arquivos, clique em **Instalar/Atualizar** e **Ativar** em *Configurar > Plugins* (ou use os comandos do console acima).

## Estrutura

```
estacionamento/
├── setup.php        # registro, versão, menu
├── hook.php         # instalação/atualização/remoção das tabelas
├── front/           # páginas de lista e formulário
├── src/             # classes (Vaga, Veiculo, Profile)
└── templates/       # formulários Twig
```

## Tabelas

- `glpi_plugin_estacionamento_vagas`
- `glpi_plugin_estacionamento_veiculos`

A desinstalação remove essas tabelas e os direitos do plugin.

## Licença

GPLv2+ (mesma licença do GLPI). Adicione o arquivo `LICENSE` antes de publicar, se desejar.
