<?php
/**
 * Pokédex Principal - Interface Mobile-First & Console Retrô
 * Inspirada na clássica Pokédex com tecnologia moderna (Tailwind CSS, WebRTC, PDO MySQL, Sessões Nativas).
 */

require_once __DIR__ . '/config/bd.php';

// Inclusão do Header estruturado
require_once __DIR__ . '/views/header.php';
?>

<main class="flex-1 max-w-7xl w-full mx-auto p-3 sm:p-6 flex flex-col gap-6">
  
  <!-- Grid Principal da Pokédex (Mobile-First: 1 Coluna no celular, 2 Colunas em telas médias/grandes) -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    
    <!-- LADO ESQUERDO: Visor Principal / Monitor de Bio-Dados (5 colunas no Desktop) -->
    <div class="lg:col-span-5 sticky top-20 z-10">
      <?php require_once __DIR__ . '/views/left_screen.php'; ?>
    </div>

    <!-- LADO DIREITO: Catálogo, Busca Dinâmica com Sugestões e Filtros (7 colunas no Desktop) -->
    <div class="lg:col-span-7 bg-slate-900/80 border-2 border-slate-800 rounded-3xl p-4 sm:p-6 shadow-2xl flex flex-col gap-5">
      
      <!-- Seção de Busca Dinâmica e Scanner -->
      <div class="flex flex-col gap-3">
        
        <div class="flex items-center justify-between">
          <label for="search-input" class="font-pixel text-xs text-slate-300 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
            <span>BUSCA DINÂMICA DE POKÉMONS</span>
          </label>
          <span class="text-[11px] font-mono text-slate-500 hidden sm:inline">Sugestões automáticas ao digitar</span>
        </div>

        <!-- Campo de Input com Caixa de Sugestões em Tempo Real -->
        <div class="relative">
          <div class="relative flex items-center">
            <span class="absolute left-3.5 text-slate-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input 
              type="text" 
              id="search-input" 
              placeholder="Digite o nome ou número (ex: Pikachu, Charizard, 25)..." 
              autocomplete="off"
              class="w-full pl-11 pr-24 py-3 bg-slate-950 border-2 border-slate-700 focus:border-red-500 rounded-2xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-red-500/20 transition-all shadow-inner"
            >
            <!-- Atalho Rápido de Câmera dentro do Input -->
            <button 
              onclick="PokedexScanner.openModal()"
              class="absolute right-2 px-3 py-1.5 rounded-xl bg-red-600/80 hover:bg-red-600 text-white text-xs font-semibold flex items-center gap-1.5 transition-all active:scale-95 shadow"
              title="Apontar Câmera para Escanear"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              <span>Scan</span>
            </button>
          </div>

          <!-- Dropdown Flutuante de Sugestões em Tempo Real -->
          <div 
            id="search-suggestions" 
            class="absolute top-full left-0 right-0 mt-2 bg-slate-800/95 border-2 border-slate-700 rounded-2xl shadow-2xl backdrop-blur-md overflow-hidden z-30 hidden max-h-72 overflow-y-auto"
          >
            <!-- Preenchido dinamicamente via JavaScript -->
          </div>
        </div>

      </div>

      <!-- Filtros Elementares Rápidos em Carrossel Horizontal -->
      <div class="flex flex-col gap-2">
        <span class="text-xs font-mono text-slate-400">Filtrar por Tipo Elemental:</span>
        <div class="flex items-center gap-1.5 overflow-x-auto pb-2 scrollbar-none text-xs">
          <button data-type="all" class="type-filter-btn px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold transition-all ring-2 ring-white scale-105 flex-shrink-0">
            Todos
          </button>
          <button data-type="Fire" class="type-filter-btn px-3 py-1.5 rounded-xl type-fire font-bold transition-all flex-shrink-0">
            Fogo
          </button>
          <button data-type="Water" class="type-filter-btn px-3 py-1.5 rounded-xl type-water font-bold transition-all flex-shrink-0">
            Água
          </button>
          <button data-type="Grass" class="type-filter-btn px-3 py-1.5 rounded-xl type-grass font-bold transition-all flex-shrink-0">
            Planta
          </button>
          <button data-type="Electric" class="type-filter-btn px-3 py-1.5 rounded-xl type-electric font-bold transition-all flex-shrink-0">
            Elétrico
          </button>
          <button data-type="Psychic" class="type-filter-btn px-3 py-1.5 rounded-xl type-psychic font-bold transition-all flex-shrink-0">
            Psíquico
          </button>
          <button data-type="Ghost" class="type-filter-btn px-3 py-1.5 rounded-xl type-ghost font-bold transition-all flex-shrink-0">
            Fantasma
          </button>
          <button data-type="Dragon" class="type-filter-btn px-3 py-1.5 rounded-xl type-dragon font-bold transition-all flex-shrink-0">
            Dragão
          </button>
          <button data-type="Dark" class="type-filter-btn px-3 py-1.5 rounded-xl type-dark font-bold transition-all flex-shrink-0">
            Sombrio
          </button>
          <button data-type="Normal" class="type-filter-btn px-3 py-1.5 rounded-xl type-normal font-bold transition-all flex-shrink-0">
            Normal
          </button>
        </div>
      </div>

      <!-- Grade de Pokémons -->
      <div class="relative min-h-[360px]">
        
        <!-- Indicador de Carregamento -->
        <div id="grid-loading" class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm rounded-2xl flex flex-col items-center justify-center gap-3 z-20 hidden">
          <div class="w-10 h-10 border-4 border-red-500 border-t-transparent rounded-full animate-spin"></div>
          <span class="font-pixel text-xs text-red-400">BUSCANDO POKÉDEX...</span>
        </div>

        <!-- Grade com os Cards de Pokémons -->
        <div id="pokemon-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
          <!-- Renderizado dinamicamente pelo JavaScript -->
        </div>

      </div>

      <!-- Controles de Paginação -->
      <div id="pagination-container" class="pt-2 border-t border-slate-800">
        <!-- Renderizado dinamicamente -->
      </div>

    </div>

  </div>

</main>

<?php 
// Modais do Sistema
require_once __DIR__ . '/views/scanner_modal.php';
require_once __DIR__ . '/views/soviet_modal.php';

// Inclusão do Rodapé estruturado
require_once __DIR__ . '/views/footer.php';
?>
