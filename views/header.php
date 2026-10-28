<?php
/**
 * Header da Pokédex: Carcaça superior clássica com Lente Azul e LEDs indicadores.
 */
?>
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Pokédex Nacional - Scanner Óptico & Reconhecimento</title>
  
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            pokedex: {
              red: '#dc2626',
              darkred: '#991b1b',
              burgundy: '#7f1d1d',
              screen: '#0f172a'
            }
          }
        }
      }
    }
  </script>

  <!-- Estilos Customizados da Pokédex -->
  <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="text-slate-100 flex flex-col min-h-screen antialiased selection:bg-red-500 selection:text-white">

  <!-- Carcaça Superior Pokédex (Chassi Retrô Kanto) -->
  <header class="bg-gradient-to-b from-red-600 via-red-700 to-red-800 border-b-4 border-red-900 shadow-xl sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
      
      <!-- Lente Óptica Principal & LEDs -->
      <div class="flex items-center gap-4">
        <!-- Lente Azul Principal com Reflexo -->
        <div class="relative group cursor-pointer" onclick="PokedexScanner.openModal()" title="Abrir Scanner Óptico">
          <div class="w-14 h-14 rounded-full big-blue-lens flex items-center justify-center transition-transform group-hover:scale-105 active:scale-95">
            <div class="w-4 h-4 rounded-full bg-white/60 -mt-3 -ml-3 blur-[1px]"></div>
          </div>
          <!-- Anel de pulso da lente -->
          <div class="absolute inset-0 rounded-full border-2 border-cyan-400 opacity-40 animate-ping pointer-events-none"></div>
        </div>

        <!-- Três LEDs Indicadores Clássicos (Vermelho, Amarelo, Verde) -->
        <div class="flex items-center gap-2">
          <span class="w-4 h-4 rounded-full bg-red-500 led-indicator text-red-500 animate-pulse" title="Sensor Alpha Ativo"></span>
          <span class="w-4 h-4 rounded-full bg-amber-400 led-indicator text-amber-400" title="Buffer de Dados OK"></span>
          <span class="w-4 h-4 rounded-full bg-emerald-400 led-indicator text-emerald-400" title="Sistema Operacional Online"></span>
        </div>
      </div>

      <!-- Título Central & Easter Egg Secret Button -->
      <div class="flex items-center gap-2 sm:gap-3">
        <h1 class="font-pixel text-xs sm:text-sm tracking-wider text-white flex items-center gap-1.5 drop-shadow">
          <span>POKÉDEX</span>
          <span class="text-amber-300 font-sans text-xs font-bold px-1.5 py-0.5 bg-black/30 rounded border border-amber-400/30">AUR-RUS</span>
        </h1>

        <!-- Botão Secreto da Foice e Martelo / Estrela Vermelha (Easter Egg 0.0000001%) -->
        <button 
          onclick="PokedexApp.triggerSecretEasterEgg()" 
          class="w-7 h-7 rounded-full bg-red-950 hover:bg-red-900 border border-amber-400/40 flex items-center justify-center text-amber-400 hover:text-amber-300 transition-all hover:scale-110 active:scale-90"
          title="Segredo da Pokédex (0,0000001% de chance ou acionamento revolucionário)"
        >
          <span class="text-xs">☭</span>
        </button>
      </div>

      <!-- Ações Rápidas: Scanner Câmera & Status do Banco -->
      <div class="flex items-center gap-2">
        <!-- Indicador de Status do MySQL PDO -->
        <div id="db-status-badge" class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-mono bg-slate-900/60 border border-slate-700 text-slate-300">
          <span class="w-2 h-2 rounded-full bg-slate-400 animate-pulse"></span> Verificando PDO...
        </div>

        <!-- Botão Abrir Câmera -->
        <button 
          onclick="PokedexScanner.openModal()"
          class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-950 border border-red-400 text-white font-semibold text-xs transition-all shadow-md active:scale-95"
        >
          <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <span class="hidden sm:inline">Escanear</span>
        </button>
      </div>

    </div>
  </header>
