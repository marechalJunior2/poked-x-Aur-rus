<?php
/**
 * Rodapé da Pokédex: Controles de Sessão Nativa, Estatísticas e Inclusão de Scripts
 */
?>
  <!-- Rodapé Pokédex (Controles & Sessão Nativa) -->
  <footer class="bg-gradient-to-t from-red-950 via-slate-950 to-slate-900 border-t-2 border-red-900 mt-auto py-5 px-4 text-xs text-slate-400">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
      
      <!-- Informações da Sessão Nativa do PHP -->
      <div class="flex items-center gap-4 text-center md:text-left">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
          <span>Sessão Nativa PHP: <strong id="session-scanned-count" class="text-white">0</strong> descobertos</span>
        </div>
        <span class="text-slate-600">|</span>
        <div>
          <span>Scans realizados: <strong id="session-total-scans" class="text-white">0</strong></span>
        </div>
      </div>

      <!-- Ações do Sistema -->
      <div class="flex items-center gap-2">
        <!-- Sincronizar via Scraper de https://pokemondb.net/pokedex/all -->
        <button 
          onclick="PokedexApp.triggerScraping()" 
          class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center gap-1.5 transition-all active:scale-95 text-xs"
          title="Raspar e sincronizar dados com pokemondb.net"
        >
          <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          <span>Sincronizar PokemonDB</span>
        </button>

        <!-- Resetar Histórico da Sessão -->
        <button 
          onclick="PokedexModal.confirm('Deseja realmente reiniciar o histórico de Pokémons escaneados nesta sessão?', 'Resetar Pokédex').then(ok => { if(ok) fetch('api/session.php?action=reset').then(() => { PokedexApp.refreshSession(); Toast.info('Sessão resetada com sucesso.'); }); })" 
          class="px-3 py-1.5 rounded-lg bg-red-950/80 hover:bg-red-900 border border-red-800 text-red-200 transition-all active:scale-95 text-xs"
          title="Limpar Pokémons escaneados na sessão"
        >
          Limpar Sessão
        </button>
      </div>

      <!-- Créditos e Fonte -->
      <div class="text-center md:text-right font-mono text-[11px] text-slate-500">
        Base de Dados: <a href="https://pokemondb.net/pokedex/all" target="_blank" rel="noopener" class="text-cyan-400 hover:underline">PokemonDB</a> • PDO MySQL • Mobile-First
      </div>

    </div>
  </footer>

  <!-- Inclusão Modular dos Scripts da Pokédex -->
  <script src="assets/js/toast.js"></script>
  <script src="assets/js/modal.js"></script>
  <script src="assets/js/soviet_anthem.js"></script>
  <script src="assets/js/scanner.js"></script>
  <script src="assets/js/app.js"></script>

</body>
</html>
