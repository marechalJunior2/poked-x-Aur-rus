/**
 * Aplicação Central Pokédex
 * Gerenciamento de busca dinâmica, interface reativa, paginação e áudio.
 */

const PokedexApp = {
  currentPokemon: null,
  currentPage: 1,
  currentType: 'all',
  currentSearch: '',
  totalPages: 1,
  isLoading: false,
  debounceTimer: null,
  audioCtx: null,

  init() {
    this.bindEvents();
    this.loadPokemons();
    this.refreshSession();
  },

  bindEvents() {
    // Campo de busca dinâmica com debounce de 250ms
    const searchInput = document.getElementById('search-input');
    const suggestionsBox = document.getElementById('search-suggestions');

    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        const val = e.target.value.trim();
        this.currentSearch = val;
        clearTimeout(this.debounceTimer);

        if (val.length >= 1) {
          this.debounceTimer = setTimeout(() => {
            this.fetchSuggestions(val);
          }, 200);
        } else {
          if (suggestionsBox) suggestionsBox.classList.add('hidden');
          this.currentPage = 1;
          this.loadPokemons();
        }
      });

      searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          if (suggestionsBox) suggestionsBox.classList.add('hidden');
          this.currentPage = 1;
          this.loadPokemons();
        } else if (e.key === 'Escape') {
          if (suggestionsBox) suggestionsBox.classList.add('hidden');
        }
      });

      // Fecha sugestões ao clicar fora
      document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && suggestionsBox && !suggestionsBox.contains(e.target)) {
          suggestionsBox.classList.add('hidden');
        }
      });
    }

    // Filtros de Tipo
    const filterButtons = document.querySelectorAll('.type-filter-btn');
    filterButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        filterButtons.forEach(b => b.classList.remove('ring-2', 'ring-white', 'scale-105'));
        btn.classList.add('ring-2', 'ring-white', 'scale-105');
        this.currentType = btn.dataset.type;
        this.currentPage = 1;
        this.loadPokemons();
        this.playClickSound();
      });
    });

    // Tecla de atalho para o Easter Egg Revolucionário (Marx 1917)
    let secretKeyBuffer = '';
    window.addEventListener('keydown', (e) => {
      secretKeyBuffer += e.key.toLowerCase();
      if (secretKeyBuffer.length > 20) secretKeyBuffer = secretKeyBuffer.slice(-20);
      if (secretKeyBuffer.includes('marx') || secretKeyBuffer.includes('1917') || secretKeyBuffer.includes('urss')) {
        secretKeyBuffer = '';
        this.triggerSecretEasterEgg();
      }
    });
  },

  // Busca sugestões em tempo real para o autocomplete
  async fetchSuggestions(term) {
    const suggestionsBox = document.getElementById('search-suggestions');
    if (!suggestionsBox) return;

    try {
      const res = await fetch(`api/pokemons.php?suggest=1&q=${encodeURIComponent(term)}`);
      const data = await res.json();

      if (!data.success || !data.pokemons || data.pokemons.length === 0) {
        suggestionsBox.classList.add('hidden');
        return;
      }

      suggestionsBox.innerHTML = '';
      data.pokemons.forEach(poke => {
        const item = document.createElement('div');
        item.className = 'flex items-center gap-3 px-3 py-2.5 hover:bg-slate-700/80 cursor-pointer border-b border-slate-700/50 last:border-none transition-colors';
        item.innerHTML = `
          <img src="${poke.image_url}" alt="${poke.name}" class="w-9 h-9 object-contain bg-slate-800 rounded p-0.5 border border-slate-600" onerror="this.src='assets/img/fantasma_comunismo.svg'">
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
              <span class="text-sm font-semibold text-slate-100 truncate">${poke.name}</span>
              <span class="text-xs font-mono text-cyan-400">#${String(poke.national_id).padStart(3, '0')}</span>
            </div>
            <div class="flex gap-1 mt-0.5">
              <span class="text-[10px] px-1.5 py-0.2 rounded font-medium ${this.getTypeClass(poke.type1)}">${poke.type1}</span>
              ${poke.type2 ? `<span class="text-[10px] px-1.5 py-0.2 rounded font-medium ${this.getTypeClass(poke.type2)}">${poke.type2}</span>` : ''}
            </div>
          </div>
        `;

        item.addEventListener('click', () => {
          suggestionsBox.classList.add('hidden');
          const searchInput = document.getElementById('search-input');
          if (searchInput) searchInput.value = poke.name;
          this.selectPokemon(poke);
          this.playClickSound();
        });

        suggestionsBox.appendChild(item);
      });

      suggestionsBox.classList.remove('hidden');

    } catch (e) {
      console.error('Erro ao buscar sugestões:', e);
    }
  },

  // Carrega a listagem de Pokémons da API
  async loadPokemons() {
    if (this.isLoading) return;
    this.isLoading = true;

    const grid = document.getElementById('pokemon-grid');
    const loadingIndicator = document.getElementById('grid-loading');

    if (loadingIndicator) loadingIndicator.classList.remove('hidden');

    try {
      const url = `api/pokemons.php?page=${this.currentPage}&type=${this.currentType}&q=${encodeURIComponent(this.currentSearch)}`;
      const res = await fetch(url);
      const data = await res.json();

      if (data.notice && data.source === 'local_fallback' && this.currentPage === 1) {
        // Notifica de forma clara em Toast sem travar a navegação
        Toast.info(data.notice, 6000);
      }

      this.totalPages = data.total_pages || 1;
      this.renderGrid(data.pokemons || []);
      this.renderPagination();

      // Se for a primeira carga e nenhum selecionado, seleciona o primeiro
      if (!this.currentPokemon && data.pokemons && data.pokemons.length > 0) {
        this.selectPokemon(data.pokemons[0]);
      }

    } catch (err) {
      console.error(err);
      Toast.error('Erro ao carregar catálogo de Pokémons.');
      if (grid) {
        grid.innerHTML = `
          <div class="col-span-full py-12 text-center text-slate-400">
            <p class="font-medium text-red-400">Falha ao obter lista de Pokémons.</p>
            <p class="text-xs mt-1">Verifique o status do servidor PHP e do banco de dados.</p>
          </div>
        `;
      }
    } finally {
      this.isLoading = false;
      if (loadingIndicator) loadingIndicator.classList.add('hidden');
    }
  },

  // Renderiza os cards na grade de Pokémons
  renderGrid(pokemons) {
    const grid = document.getElementById('pokemon-grid');
    if (!grid) return;

    if (pokemons.length === 0) {
      grid.innerHTML = `
        <div class="col-span-full py-12 text-center text-slate-400">
          <p class="text-base font-semibold text-slate-300">Nenhum Pokémon encontrado.</p>
          <p class="text-xs mt-1">Tente outro termo de busca ou selecione outro tipo elemental.</p>
        </div>
      `;
      return;
    }

    grid.innerHTML = pokemons.map(poke => {
      const isSelected = this.currentPokemon && this.currentPokemon.id === poke.id;
      return `
        <div 
          class="pokemon-card group relative bg-slate-900/90 border ${isSelected ? 'border-red-500 ring-2 ring-red-500/50 bg-slate-800' : 'border-slate-800 hover:border-slate-600'} rounded-2xl p-3 flex flex-col items-center cursor-pointer transition-all duration-200 hover:-translate-y-1 shadow-lg hover:shadow-cyan-900/20"
          onclick="PokedexApp.selectPokemonById(${poke.id})"
        >
          <!-- Badge de ID -->
          <span class="absolute top-2.5 left-3 font-mono text-[10px] font-bold text-slate-400">
            #${String(poke.national_id).padStart(3, '0')}
          </span>

          <!-- Imagem do Pokémon com efeito suave -->
          <div class="w-20 h-20 my-2 flex items-center justify-center relative">
            <div class="absolute inset-0 bg-radial from-cyan-500/10 to-transparent rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <img 
              src="${poke.image_url}" 
              alt="${poke.name}" 
              loading="lazy"
              class="max-w-full max-h-full object-contain filter drop-shadow group-hover:scale-110 transition-transform duration-200"
              onerror="this.src='assets/img/fantasma_comunismo.svg'"
            >
          </div>

          <!-- Nome -->
          <h4 class="text-xs font-bold text-slate-100 truncate w-full text-center group-hover:text-red-400 transition-colors">
            ${poke.name}
          </h4>

          <!-- Tipos -->
          <div class="flex gap-1 mt-1.5 justify-center w-full">
            <span class="text-[9px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider ${this.getTypeClass(poke.type1)}">
              ${poke.type1}
            </span>
            ${poke.type2 ? `
              <span class="text-[9px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider ${this.getTypeClass(poke.type2)}">
                ${poke.type2}
              </span>
            ` : ''}
          </div>
        </div>
      `;
    }).join('');
  },

  // Seleciona um Pokémon por ID
  async selectPokemonById(id) {
    this.playClickSound();
    try {
      const res = await fetch(`api/pokemon.php?id=${id}`);
      const data = await res.json();
      if (data.success && data.pokemon) {
        this.selectPokemon(data.pokemon);
      }
    } catch (e) {
      console.error('Erro ao buscar detalhes do pokemon:', e);
    }
  },

  // Exibe o Pokémon no visor principal esquerdo da Pokédex
  selectPokemon(pokemon) {
    this.currentPokemon = pokemon;
    this.playPokemonCry();

    // Atualiza marcação ativa na grade
    document.querySelectorAll('.pokemon-card').forEach(card => {
      card.classList.remove('border-red-500', 'ring-2', 'ring-red-500/50', 'bg-slate-800');
    });

    // Atualiza elementos do painel esquerdo
    const screenImg = document.getElementById('detail-image');
    const screenName = document.getElementById('detail-name');
    const screenNumber = document.getElementById('detail-number');
    const screenTypes = document.getElementById('detail-types');
    const screenDesc = document.getElementById('detail-description');
    const screenTotal = document.getElementById('detail-total');

    if (screenImg) {
      screenImg.src = pokemon.image_url;
      screenImg.alt = pokemon.name;
    }
    if (screenName) screenName.textContent = pokemon.name;
    if (screenNumber) screenNumber.textContent = `#${String(pokemon.national_id).padStart(3, '0')}`;
    if (screenTotal) screenTotal.textContent = pokemon.total;
    if (screenDesc) screenDesc.textContent = pokemon.description || 'Nenhuma descrição biológica disponível.';

    if (screenTypes) {
      screenTypes.innerHTML = `
        <span class="px-2.5 py-1 rounded text-xs font-bold uppercase tracking-wider ${this.getTypeClass(pokemon.type1)}">${pokemon.type1}</span>
        ${pokemon.type2 ? `<span class="px-2.5 py-1 rounded text-xs font-bold uppercase tracking-wider ${this.getTypeClass(pokemon.type2)}">${pokemon.type2}</span>` : ''}
      `;
    }

    // Atualiza barras de estatísticas
    this.updateStatBar('stat-hp', pokemon.hp, 255);
    this.updateStatBar('stat-attack', pokemon.attack, 200);
    this.updateStatBar('stat-defense', pokemon.defense, 200);
    this.updateStatBar('stat-spatk', pokemon.sp_atk, 200);
    this.updateStatBar('stat-spdef', pokemon.sp_def, 200);
    this.updateStatBar('stat-speed', pokemon.speed, 200);

    // Se estiver no mobile, faz scroll suave para o visor
    if (window.innerWidth < 768) {
      const visor = document.getElementById('pokedex-left-screen');
      if (visor) visor.scrollIntoView({ behavior: 'smooth' });
    }
  },

  updateStatBar(elementId, value, maxVal = 200) {
    const el = document.getElementById(elementId);
    const valText = document.getElementById(`${elementId}-val`);
    if (valText) valText.textContent = value;
    if (el) {
      const percentage = Math.min(100, Math.round((value / maxVal) * 100));
      el.style.width = `${percentage}%`;
    }
  },

  // Renderiza controles de paginação
  renderPagination() {
    const paginationContainer = document.getElementById('pagination-container');
    if (!paginationContainer) return;

    if (this.totalPages <= 1) {
      paginationContainer.innerHTML = '';
      return;
    }

    paginationContainer.innerHTML = `
      <div class="flex items-center justify-between w-full text-xs font-medium text-slate-300">
        <button 
          class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
          ${this.currentPage <= 1 ? 'disabled' : ''}
          onclick="PokedexApp.changePage(${this.currentPage - 1})"
        >
          ← Anterior
        </button>

        <span class="font-mono text-slate-400">
          Página <strong class="text-white">${this.currentPage}</strong> de ${this.totalPages}
        </span>

        <button 
          class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
          ${this.currentPage >= this.totalPages ? 'disabled' : ''}
          onclick="PokedexApp.changePage(${this.currentPage + 1})"
        >
          Próxima →
        </button>
      </div>
    `;
  },

  changePage(newPage) {
    this.currentPage = newPage;
    this.loadPokemons();
    this.playClickSound();
  },

  // Atualiza contadores da sessão nativa do PHP
  async refreshSession() {
    try {
      const res = await fetch('api/session.php');
      const data = await res.json();
      const countEl = document.getElementById('session-scanned-count');
      const totalEl = document.getElementById('session-total-scans');
      const dbStatusEl = document.getElementById('db-status-badge');

      if (countEl) countEl.textContent = data.scanned_count;
      if (totalEl) totalEl.textContent = data.total_scans;

      if (dbStatusEl) {
        if (data.db_connected) {
          dbStatusEl.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> MySQL PDO Online';
          dbStatusEl.className = 'inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-mono bg-emerald-950/80 border border-emerald-500/40 text-emerald-300';
        } else {
          dbStatusEl.innerHTML = '<span class="w-2 h-2 rounded-full bg-amber-400"></span> Modo Contingência JSON';
          dbStatusEl.className = 'inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-mono bg-amber-950/80 border border-amber-500/40 text-amber-300 cursor-pointer';
          dbStatusEl.title = 'MySQL offline. Usando cache de semente. Configure config/bd.php para persistir no banco.';
        }
      }
    } catch (e) {}
  },

  // Dispara o scraping completo com confirmação em Modal
  async triggerScraping() {
    const confirmed = await PokedexModal.confirm(
      'Deseja sincronizar e raspar todos os dados atualizados diretamente de https://pokemondb.net/pokedex/all?\n\nIsso atualizará os Pokémons e atributos no banco de dados MySQL via PDO.',
      'Sincronização PokemonDB'
    );

    if (!confirmed) return;

    Toast.info('Iniciando raspagem de dados no PokemonDB. Aguarde...', 6000);

    try {
      const res = await fetch('backend/scraper.php?limit=50');
      const data = await res.json();

      if (data.success) {
        Toast.success(data.message, 6000);
        this.loadPokemons();
      } else {
        Toast.error(data.message || 'Falha ao sincronizar dados com o PokemonDB.');
      }
    } catch (e) {
      Toast.error('Erro na requisição do scraper. Verifique se o PHP tem acesso à internet.');
    }
  },

  // Aciona o Easter Egg do Fantasma do Comunismo (com código secreto ou clique na estrela)
  async triggerSecretEasterEgg() {
    Toast.warning('SINAL DE RÁDIO HISTÓRICO CAPTADO! FREQUÊNCIA 1917 KHZ...', 3000);
    try {
      const res = await fetch('api/scan.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ force_secret: '1917' })
      });
      const data = await res.json();
      if (data.pokemon) {
        PokedexScanner.triggerSovietLiberation(data.pokemon);
      }
    } catch (e) {
      console.error(e);
    }
  },

  getTypeClass(type) {
    if (!type) return 'bg-slate-700 text-white';
    return `type-${type.toLowerCase()}`;
  },

  // Sons de interface retro via Web Audio API
  playClickSound() {
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return;
    if (!this.audioCtx) this.audioCtx = new AudioCtx();
    if (this.audioCtx.state === 'suspended') this.audioCtx.resume();

    const osc = this.audioCtx.createOscillator();
    const gain = this.audioCtx.createGain();

    osc.type = 'triangle';
    osc.frequency.setValueAtTime(480, this.audioCtx.currentTime);
    gain.gain.setValueAtTime(0.08, this.audioCtx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.05);

    osc.connect(gain);
    gain.connect(this.audioCtx.destination);
    osc.start();
    osc.stop(this.audioCtx.currentTime + 0.05);
  },

  playPokemonCry() {
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return;
    if (!this.audioCtx) this.audioCtx = new AudioCtx();
    if (this.audioCtx.state === 'suspended') this.audioCtx.resume();

    const osc = this.audioCtx.createOscillator();
    const gain = this.audioCtx.createGain();

    osc.type = 'sawtooth';
    const now = this.audioCtx.currentTime;
    osc.frequency.setValueAtTime(260, now);
    osc.frequency.linearRampToValueAtTime(520, now + 0.1);
    osc.frequency.linearRampToValueAtTime(320, now + 0.2);

    gain.gain.setValueAtTime(0.12, now);
    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.25);

    osc.connect(gain);
    gain.connect(this.audioCtx.destination);
    osc.start();
    osc.stop(now + 0.25);
  }
};

window.PokedexApp = PokedexApp;

document.addEventListener('DOMContentLoaded', () => {
  PokedexApp.init();
});
