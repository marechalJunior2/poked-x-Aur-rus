<?php
/**
 * Modal Revolucionário: O Fantasma do Comunismo (Easter Egg 0,0000001%)
 * Ao encontrar, liberta o usuário do capitalismo ao som do hino da União Soviética.
 */
?>
<div id="soviet-modal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-red-950/90 backdrop-blur-md hidden opacity-0 transition-opacity duration-500 overflow-y-auto">
  
  <div class="relative w-full max-w-lg bg-slate-950 border-4 border-amber-400 rounded-3xl shadow-2xl soviet-glow overflow-hidden p-6 text-center my-auto flex flex-col items-center gap-4">
    
    <!-- Estrela Vermelha e Foice e Martelo de Fundo -->
    <div class="absolute -top-10 -right-10 text-9xl text-red-600/10 pointer-events-none select-none">
      ☭
    </div>
    <div class="absolute -bottom-10 -left-10 text-9xl text-amber-500/10 pointer-events-none select-none">
      ★
    </div>

    <!-- Título Retrô Revolucionário -->
    <div class="space-y-1 z-10">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-900/80 border border-amber-400/50 text-amber-300 text-xs font-mono font-bold tracking-widest uppercase">
        <span>★</span> PROLETÁRIOS DE TODOS OS PAÍSES, UNI-VOS! <span>★</span>
      </div>
      <h2 class="font-pixel text-lg sm:text-xl text-amber-400 drop-shadow-md mt-2">
        VOCÊ FOI LIBERTO DO CAPITALISMO!
      </h2>
      <p class="text-xs text-red-300 font-medium">
        Espécime ultrarraro capturado com probabilidade astronômica de 0,0000001%
      </p>
    </div>

    <!-- Imagem Heroica do Fantasma do Comunismo -->
    <div class="relative w-48 h-48 my-2 z-10 flex items-center justify-center">
      <div class="absolute inset-0 bg-red-600/30 rounded-full blur-2xl animate-pulse"></div>
      <img 
        src="assets/img/fantasma_comunismo.svg" 
        alt="Fantasma do Comunismo" 
        class="w-full h-full object-contain filter drop-shadow-[0_0_20px_rgba(255,215,0,0.6)] animate-bounce" 
        style="animation-duration: 3s;"
      >
    </div>

    <!-- Ficha Técnica do Pokémon Revolucionário -->
    <div class="w-full bg-red-950/60 border border-amber-500/40 rounded-2xl p-4 text-left z-10 space-y-2">
      <div class="flex items-center justify-between border-b border-red-800/80 pb-2">
        <div>
          <span class="font-pixel text-[10px] text-amber-400">#9999</span>
          <h3 class="text-base font-bold text-white">Fantasma do Comunismo</h3>
        </div>
        <div class="flex gap-1.5">
          <span class="px-2 py-0.5 rounded text-[10px] font-bold type-ghost">Ghost</span>
          <span class="px-2 py-0.5 rounded text-[10px] font-bold type-revolutionary">Revolutionary</span>
        </div>
      </div>

      <p class="text-xs text-amber-100 leading-relaxed italic">
        "Um espectro ronda a Pokédex! Que as classes dominantes tremam diante da revolução dos Pokémons! Os treinadores nada têm a perder a não ser suas correntes. Têm um mundo a ganhar!"
      </p>

      <!-- Atributos Lendários -->
      <div class="grid grid-cols-3 gap-2 pt-1 font-mono text-[11px] text-center">
        <div class="bg-black/40 p-1.5 rounded border border-red-700/50">
          <span class="text-red-400 block text-[9px]">TOTAL</span>
          <strong class="text-amber-300 text-xs">1917</strong>
        </div>
        <div class="bg-black/40 p-1.5 rounded border border-red-700/50">
          <span class="text-red-400 block text-[9px]">HP / ATK</span>
          <strong class="text-white">200 / 191</strong>
        </div>
        <div class="bg-black/40 p-1.5 rounded border border-red-700/50">
          <span class="text-red-400 block text-[9px]">STATUS</span>
          <strong class="text-emerald-400">LIBERTO</strong>
        </div>
      </div>
    </div>

    <!-- Controles do Hino da União Soviética -->
    <div class="w-full bg-black/60 border border-red-800 rounded-xl p-3 flex items-center justify-between z-10">
      <div class="flex items-center gap-2">
        <span class="text-xl">🎺</span>
        <div class="text-left">
          <span class="text-xs font-bold text-amber-300 block">Hino da União Soviética</span>
          <span class="text-[10px] text-slate-400 font-mono">Tocando via Web Audio Sintetizador</span>
        </div>
      </div>
      <div class="flex gap-2">
        <button 
          onclick="SovietAnthem.play()" 
          class="px-2.5 py-1 rounded bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition-colors"
          title="Tocar Novamente"
        >
          ▶ Tocar
        </button>
        <button 
          onclick="SovietAnthem.stop()" 
          class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium text-xs transition-colors"
          title="Pausar Hino"
        >
          ⏹ Parar
        </button>
      </div>
    </div>

    <!-- Botão Fechar Modal -->
    <button 
      onclick="PokedexScanner.closeSovietModal()" 
      class="w-full py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 active:scale-95 text-slate-950 font-bold text-sm tracking-wider uppercase transition-all shadow-xl shadow-amber-950/50 z-10"
    >
      Continuar Jornada Sem Mais Exploração
    </button>

  </div>

</div>
