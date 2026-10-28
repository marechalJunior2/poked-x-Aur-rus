# 🔴 Pokédex Aur-rus

Aplicação web inspirada na Pokédex oficial, desenvolvida em conformidade estrita com todos os requisitos de arquitetura, segurança e regras de negócio de `requisitos.md`.

---

## 🚀 Tecnologias Utilizadas

- **Backend**: PHP Estruturado com sessões nativas (`session_start()`).
- **Banco de Dados**: MySQL com **PDO** para máxima segurança e proteção contra SQL Injection via Prepared Statements.
- **Frontend**: HTML5, PHP, CSS3, e **Tailwind CSS** com design Mobile-First responsivo.
- **Áudio & Visão**: Web Audio API (chiptune synth do Hino Soviético e efeitos sonoros da Pokédex) e WebRTC (`navigator.mediaDevices.getUserMedia` para escaneamento óptico com a câmera).
- **Dados & Scraping**: Base de dados scrapeada e sincronizada de [https://pokemondb.net/pokedex/all](https://pokemondb.net/pokedex/all).

---

## 🎯 Funcionalidades Implementadas

1. **Scanner Óptico com Câmera ao Vivo**:
   - Aponte a câmera do celular ou notebook para o Pokémon ou card desejado.
   - Retículo de mira com animação de varredura a laser (HUD de alta tecnologia).
   - Botão para alternar entre câmera frontal e traseira no celular.
   - Suporte a envio de imagens/cards como alternativa direta.
   - Exibição instantânea do Pokémon identificado no visor principal da Pokédex.

2. **Busca Dinâmica & Sugestões em Tempo Real**:
   - Busca sem recarregamento de página.
   - Enquanto você digita o nome ou número (ex: `Pikachu`, `Charizard`, `25`), as sugestões surgem em tempo real com ícone, tipos e ID.
   - Filtros instantâneos por tipo elemental (Fogo, Água, Planta, Elétrico, etc.).

3. **Easter Egg Especial: O Fantasma do Comunismo (0,0000001% de chance)**:
   - Pokémon lendário secreto `#9999 - Fantasma do Comunismo` (*Ghost / Revolutionary*).
   - Probabilidade nativa calculada no backend em cada scan (1 em 1.000.000.000).
   - **Gatilho de Demonstração**: Clique no botão com o símbolo `☭` no topo da Pokédex ou digite a palavra `marx` no teclado.
   - Ao ser encontrado, dispara um alerta visual soviético, concede libertação do capitalismo e executa o **Hino da União Soviética** sintetizado via Web Audio API.

4. **Tratamento de Alertas e Notificações**:
   - Todas as mensagens de erro e sucesso são retornadas em **TOAST** claro e não-intrusivo.
   - Todas as confirmações de ações nativas (`alert` e `confirm`) foram substituídas por **MODAL** customizado no estilo Pokédex.

5. **Scraper Estruturado do PokemonDB**:
   - `backend/scraper.php`: Raspa automaticamente todos os dados de `https://pokemondb.net/pokedex/all` (números nacionais, nomes, tipos, total, HP, ataque, defesa, sp. atk, sp. def, velocidade e imagens).
   - Pode ser acionado diretamente pelo botão **"Sincronizar PokemonDB"** no rodapé ou via terminal CLI.

---

## 📁 Estrutura de Arquivos Modular

```
pokedex/
├── bd.php                       # Ponto de entrada padrão para a conexão com a base
├── index.php                    # Dashboard principal (Mobile-First)
├── preview.html                 # Visualização estática direta para teste no navegador
├── config/
│   └── bd.php                   # Configuração e inicialização segura do PDO MySQL
├── database/
│   ├── schema.sql               # Estrutura das tabelas (pokemons, scan_history)
│   ├── seed.sql                 # Dados iniciais e Pokémon secreto
│   └── pokemons_seed.json       # Cache de contingência caso o MySQL esteja offline
├── backend/
│   └── scraper.php              # Scraper cURL / DOMXPath do PokemonDB
├── api/
│   ├── pokemons.php             # Busca dinâmica, autocomplete e paginação (PDO)
│   ├── pokemon.php              # Detalhes de um espécime individual (PDO)
│   ├── scan.php                 # Processamento do escaneamento da câmera e sorteio secreto
│   └── session.php              # Gerenciamento das sessões nativas PHP
├── views/
│   ├── header.php               # Chassi superior, lente azul e LEDs indicadores
│   ├── left_screen.php          # Visor principal com atributos (HP, Atk, Def, etc.)
│   ├── scanner_modal.php        # HUD da câmera WebRTC e visor óptico
│   ├── soviet_modal.php         # Modal da libertação soviética
│   └── footer.php               # Controles de sessão e scripts
└── assets/
    ├── css/custom.css           # Estilos retro CRT, scanlines e badges de tipos
    ├── img/
    │   └── fantasma_comunismo.svg # Ilustração vetorial HD do Pokémon secreto
    └── js/
        ├── app.js               # Lógica central da aplicação e busca dinâmica
        ├── modal.js             # Substituição de alerts/confirms por modais Tailwind
        ├── toast.js             # Notificações Toast elegantes
        ├── scanner.js           # Gerenciador da câmera WebRTC e visão computacional
        └── soviet_anthem.js     # Sintetizador Web Audio API do Hino Soviético
```

---

## 🛠️ Como Executar

### 1. Configuração do Banco de Dados MySQL
1. Abra seu cliente MySQL (phpMyAdmin, MySQL Workbench ou terminal).
2. Execute o script `database/schema.sql` para criar a base `pokedex_db` e as tabelas.
3. Execute o script `database/seed.sql` para carregar a base inicial com os Pokémons e o Fantasma do Comunismo.
4. Ajuste as credenciais (usuário e senha) no arquivo `config/bd.php` se necessário (o padrão é `root` sem senha na porta `3306`).

### 2. Iniciar o Servidor PHP
No terminal da pasta do projeto:
```bash
php -S localhost:8000
```
Acesse no seu navegador:
```
http://localhost:8000
```

> **Dica**: Caso queira testar a interface imediatamente sem iniciar o MySQL ou PHP, basta abrir o arquivo `preview.html` diretamente em qualquer navegador moderno.
