# Pokedex

## Objetivos
crie um site inspirado na pokedéx. todos os dados vão ser scrapeados da base https://pokemondb.net/pokedex/all 

### Stack Tecnlógico
- Backend: PHP estruturado com sessões nativas
- Banco de dados: MySQL (PDO para segurança)
- Frontend: HTML5, PHP, CSS, Tailwind CSS

#### Regras de negócio (CORE)
- apontar a câmera para o pokémon desejado, escanear o mesmo e visualiza-lo no painel.
- faça a busca de todos os pokémons neste site https://pokemondb.net/pokedex/all
- as buscas sem barras de busca ou por imagem devem ser dinâmicas. ex: ao digitar deve aparecer a sugestão do Pokémon.
- deve ter um Pokémon escondido super raro, o fantasma do comunismo, 0,0000001% de achar. e quem achar vai ser liberto do capitalismo ao hino da união sovietica.
##### Regras Globais
- Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections
- Mantenha o código limpo e comente apenas logicas complexas.
- Separe os arquivos de forma lógica: um arquivo para conexão com a base (bd.php) e scripts de backend isolados e views em HTML5/PHP, nunca faça o sistema como um monolito, deixe sempre separados todas as regras para facilitar os futuros upgrades.
 - Estilize as telas em Tailwind de forma responsiva priorizando o MobileFrist.
 - Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)
 - sempre trate as mensagens de caixa de mensagens nativas do navegador em um MODAL
