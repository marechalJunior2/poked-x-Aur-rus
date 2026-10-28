<?php
/**
 * Visor Principal Esquerdo da Pokédex (Display CRT/LCD com estatísticas e detalhes)
 */
?>
<section id="pokedex-left-screen" class="bg-red-700/80 p-4 sm:p-6 rounded-3xl border-4 border-red-900 shadow-2xl flex flex-col gap-4">
  
  <!-- Moldura Chanfrada do Visor Principal -->
  <div class="bg-slate-200 p-3 sm:p-4 rounded-2xl border-4 border-slate-400 shadow-inner">
    
    <!-- Topo da moldura: LEDs e mini-orifícios de áudio -->
    <div class="flex items-center justify-between mb-2 px-2">
      <div class="flex items-center gap-2">
        <span class="w-3 h-3 rounded-full bg-red-600 border border-slate-400"></span>
        <span class="w-3 h-3 rounded-full bg-red-600 border border-slate-400"></span>
      </div>
      <div class="flex gap-1">
        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
      </div>
    </div>

    <!-- Tela LCD / CRT com Scanlines -->
    <div class="relative bg-slate-900 scanlines border-2 border-slate-800 rounded-xl p-4 overflow-hidden min-h-[260px] flex flex-col items-center justify-center">
      
      <!-- Fundo Holográfico Sutil -->
      <div class="absolute inset-0 bg-radial from-cyan-900/20 via-transparent to-black/60 pointer-events-none"></div>

      <!-- Imagem do Pokémon Ativo -->
      <div class="relative w-44 h-44 flex items-center justify-center z-10">
        <img 
          id="detail-image" 
          src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/home/1.png" 
          alt="Carregando Pokémon..." 
          class="max-w-full max-h-full object-contain filter drop-shadow-xl transition-all duration-300"
          onerror="this.src='assets/img/fantasma_comunismo.svg'"
        >
      </div>

      <!-- Status Bar Inferior da Tela -->
      <div class="w-full mt-2 pt-2 border-t border-slate-800 flex items-center justify-between text-xs z-10">
        <div class="flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
          <span id="detail-number" class="font-pixel text-emerald-400 text-[10px]">#001</span>
        </div>
        <div id="detail-types" class="flex gap-1.5">
          <!-- Tipos dinâmicos inseridos via JavaScript -->
          <span class="px-2 py-0.5 rounded text-[10px] font-bold type-grass">Grass</span>
          <span class="px-2 py-0.5 rounded text-[10px] font-bold type-poison">Poison</span>
        </div>
      </div>

    </div>

    <!-- Base da moldura: Botão Redondo Grande & Speaker Grill -->
    <div class="flex items-center justify-between mt-3 px-2">
      <button 
        onclick="PokedexApp.playPokemonCry()" 
        class="w-8 h-8 rounded-full bg-red-600 hover:bg-red-500 border-2 border-red-800 shadow-md active:scale-95 flex items-center justify-center text-white"
        title="Reproduzir Brado do Pokémon"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
      </button>

      <!-- Grade de Saída de Som (Speaker Grill) -->
      <div class="flex flex-col gap-1 w-16">
        <div class="h-1 bg-slate-600 rounded-full"></div>
        <div class="h-1 bg-slate-600 rounded-full"></div>
        <div class="h-1 bg-slate-600 rounded-full"></div>
        <div class="h-1 bg-slate-600 rounded-full"></div>
      </div>
    </div>

  </div>

  <!-- Painel de Dados Biológicos e Estatísticas (Stats) -->
  <div class="bg-slate-900/90 border-2 border-slate-800 rounded-2xl p-4 flex flex-col gap-3 shadow-inner">
    
    <!-- Nome do Pokémon e Total de Pontos -->
    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
      <h2 id="detail-name" class="text-lg font-bold text-white tracking-wide">Bulbasaur</h2>
      <div class="text-right">
        <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-mono">Total Stats</span>
        <span id="detail-total" class="font-pixel text-xs text-amber-400">318</span>
      </div>
    </div>

    <!-- Descrição Biológica -->
    <p id="detail-description" class="text-xs text-slate-300 leading-relaxed italic bg-slate-950/60 p-2.5 rounded-lg border border-slate-800/80">
      Há uma semente de planta nas suas costas desde o dia em que nasceu. A semente cresce lentamente com ele.
    </p>

    <!-- Barras de Atributos (Base Stats) -->
    <div class="space-y-1.5 font-hud text-xs font-semibold">
      
      <!-- HP -->
      <div class="flex items-center gap-2">
        <span class="w-12 text-slate-400">HP</span>
        <div class="flex-1 bg-slate-800 rounded-full h-2.5 overflow-hidden">
          <div id="stat-hp" class="bg-emerald-500 h-full rounded-full transition-all duration-500" style="width: 22.5%"></div>
        </div>
        <span id="stat-hp-val" class="w-8 text-right font-mono text-slate-200">45</span>
      </div>

      <!-- Ataque -->
      <div class="flex items-center gap-2">
        <span class="w-12 text-slate-400">ATAQUE</span>
        <div class="flex-1 bg-slate-800 rounded-full h-2.5 overflow-hidden">
          <div id="stat-attack" class="bg-red-500 h-full rounded-full transition-all duration-500" style="width: 24.5%"></div>
        </div>
        <span id="stat-attack-val" class="w-8 text-right font-mono text-slate-200">49</span>
      </div>

      <!-- Defesa -->
      <div class="flex items-center gap-2">
        <span class="w-12 text-slate-400">DEFESA</span>
        <div class="flex-1 bg-slate-800 rounded-full h-2.5 overflow-hidden">
          <div id="stat-defense" class="bg-blue-500 h-full rounded-full transition-all duration-500" style="width: 24.5%"></div>
        </div>
        <span id="stat-defense-val" class="w-8 text-right font-mono text-slate-200">49</span>
      </div>

      <!-- Sp. Atk -->
      <div class="flex items-center gap-2">
        <span class="w-12 text-slate-400">SP. ATK</span>
        <div class="flex-1 bg-slate-800 rounded-full h-2.5 overflow-hidden">
          <div id="stat-spatk" class="bg-purple-500 h-full rounded-full transition-all duration-500" style="width: 32.5%"></div>
        </div>
        <span id="stat-spatk-val" class="w-8 text-right font-mono text-slate-200">65</span>
      </div>

      <!-- Sp. Def -->
      <div class="flex items-center gap-2">
        <span class="w-12 text-slate-400">SP. DEF</span>
        <div class="flex-1 bg-slate-800 rounded-full h-2.5 overflow-hidden">
          <div id="stat-spdef" class="bg-yellow-500 h-full rounded-full transition-all duration-500" style="width: 32.5%"></div>
        </div>
        <span id="stat-spdef-val" class="w-8 text-right font-mono text-slate-200">65</span>
      </div>

      <!-- Velocidade -->
      <div class="flex items-center gap-2">
        <span class="w-12 text-slate-400">VELOC.</span>
        <div class="flex-1 bg-slate-800 rounded-full h-2.5 overflow-hidden">
          <div id="stat-speed" class="bg-pink-500 h-full rounded-full transition-all duration-500" style="width: 22.5%"></div>
        </div>
        <span id="stat-speed-val" class="w-8 text-right font-mono text-slate-200">45</span>
      </div>

    </div>

  </div>

</section>
