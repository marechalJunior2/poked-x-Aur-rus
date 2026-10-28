<?php
/**
 * Modal do Scanner Óptico: Visor Holográfico com Câmera WebRTC e Identificação Visual
 */
?>
<div id="scanner-modal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/85 backdrop-blur-md hidden opacity-0 transition-opacity duration-300">
  
  <div id="scanner-box" class="relative w-full max-w-lg bg-slate-900 border-2 border-cyan-500/70 rounded-3xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col">
    
    <!-- Cabeçalho do Visor -->
    <div class="bg-gradient-to-r from-cyan-950 via-slate-900 to-cyan-950 px-5 py-3.5 border-b border-cyan-500/40 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-ping"></span>
        <h3 class="font-pixel text-xs text-cyan-300 tracking-wider">VISOR ÓPTICO DE BIO-ANÁLISE</h3>
      </div>
      <button onclick="PokedexScanner.closeModal()" class="text-slate-400 hover:text-white transition-colors p-1 rounded-lg hover:bg-slate-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <!-- Área de Visualização da Câmera com Retículo e Scan Laser -->
    <div class="relative bg-black h-72 sm:h-80 flex items-center justify-center overflow-hidden">
      
      <!-- Vídeo da Câmera WebRTC -->
      <video id="scanner-video" playsinline class="w-full h-full object-cover"></video>
      
      <!-- Canvas Oculto para Análise Visual -->
      <canvas id="scanner-canvas" class="hidden"></canvas>

      <!-- Grade Holográfica de Mira -->
      <div class="absolute inset-0 pointer-events-none flex flex-col justify-between p-6">
        
        <!-- Cantos da Mira Superior -->
        <div class="flex justify-between">
          <div class="w-8 h-8 border-t-2 border-l-2 border-cyan-400"></div>
          <div class="w-8 h-8 border-t-2 border-r-2 border-cyan-400"></div>
        </div>

        <!-- Centro da Mira -->
        <div class="self-center flex items-center justify-center relative">
          <div class="w-28 h-28 border border-dashed border-cyan-400/60 rounded-full animate-spin" style="animation-duration: 10s;"></div>
          <div class="absolute w-3 h-3 bg-cyan-400/80 rounded-full"></div>
          <div class="absolute w-36 h-0.5 bg-cyan-400/20"></div>
          <div class="absolute h-36 w-0.5 bg-cyan-400/20"></div>
        </div>

        <!-- Cantos da Mira Inferior -->
        <div class="flex justify-between">
          <div class="w-8 h-8 border-b-2 border-l-2 border-cyan-400"></div>
          <div class="w-8 h-8 border-b-2 border-r-2 border-cyan-400"></div>
        </div>

      </div>

      <!-- Laser Scanner Animado -->
      <div class="scan-laser-line pointer-events-none"></div>

      <!-- Faixa de Status do HUD -->
      <div class="absolute bottom-2 left-3 right-3 bg-slate-950/80 backdrop-blur-sm border border-cyan-500/30 rounded-lg px-3 py-1.5 flex items-center justify-between text-[11px] font-mono text-cyan-300">
        <span id="scanner-status-text" class="truncate">AGUARDANDO ALINHAMENTO DO ESPÉCIME...</span>
        <span class="flex-shrink-0 ml-2 animate-pulse text-emerald-400">● 1080p</span>
      </div>

    </div>

    <!-- Controles Inferiores do Scanner -->
    <div class="bg-slate-950 p-4 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
      
      <!-- Alternar Câmera ou Carregar Imagem -->
      <div class="flex items-center gap-2 w-full sm:w-auto justify-center">
        <!-- Alternar Câmera Frontal / Traseira -->
        <button 
          onclick="PokedexScanner.toggleCamera()" 
          class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 flex items-center gap-1.5 border border-slate-700 transition-all active:scale-95"
          title="Alternar entre câmera frontal e traseira"
        >
          <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          <span>Virar</span>
        </button>

        <!-- Input File Oculto & Botão para Carregar Foto / Card -->
        <input type="file" id="scanner-upload-input" accept="image/*" class="hidden" onchange="PokedexScanner.handleImageUpload(event)">
        <button 
          onclick="document.getElementById('scanner-upload-input').click()" 
          class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 flex items-center gap-1.5 border border-slate-700 transition-all active:scale-95"
          title="Fazer upload de foto ou card de Pokémon"
        >
          <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          <span>Arquivo</span>
        </button>
      </div>

      <!-- Botão Central de Disparo do Scanner -->
      <button 
        id="scanner-capture-btn"
        onclick="PokedexScanner.scanFrame()" 
        class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 active:scale-95 text-white font-bold text-sm flex items-center justify-center gap-2 shadow-lg shadow-red-900/50 transition-all"
      >
        <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
        <span>ESCANEAR ESPÉCIME</span>
      </button>

    </div>

  </div>

</div>
