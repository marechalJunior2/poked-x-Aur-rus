/**
 * Sistema de Modal Nativo da Pokédex
 * Substitui caixas de diálogo padrão do navegador (alert, confirm, prompt)
 * por modais estilizados, modernos e responsivos com Tailwind CSS.
 */

const PokedexModal = {
  container: null,

  init() {
    if (!this.container) {
      this.container = document.createElement('div');
      this.container.id = 'pokedex-modal-wrapper';
      this.container.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300';
      document.body.appendChild(this.container);
    }
  },

  alert(message, title = 'Pokédex Alerta') {
    return new Promise((resolve) => {
      this.init();

      this.container.innerHTML = `
        <div class="relative w-full max-w-md bg-slate-900 border-2 border-red-600 rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300" id="modal-card">
          <!-- Cabeçalho Pokédex -->
          <div class="bg-gradient-to-r from-red-600 to-red-800 px-5 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-3 h-3 rounded-full bg-cyan-400 animate-pulse"></span>
              <h3 class="font-pixel text-xs text-white uppercase tracking-wider">${title}</h3>
            </div>
            <span class="text-xs text-red-200 font-mono">SYS-MSG</span>
          </div>

          <!-- Corpo da Mensagem -->
          <div class="p-6 text-slate-200">
            <p class="text-sm leading-relaxed whitespace-pre-line">${message}</p>
          </div>

          <!-- Rodapé com Botão -->
          <div class="bg-slate-950/80 px-6 py-4 flex justify-end">
            <button id="modal-ok-btn" class="px-5 py-2 rounded-lg bg-red-600 hover:bg-red-500 active:scale-95 text-white font-semibold text-sm transition-all shadow-lg shadow-red-900/50">
              Confirmar
            </button>
          </div>
        </div>
      `;

      this.open();

      const okBtn = document.getElementById('modal-ok-btn');
      okBtn.focus();
      okBtn.onclick = () => {
        this.close();
        resolve(true);
      };
    });
  },

  confirm(message, title = 'Confirmação Pokédex') {
    return new Promise((resolve) => {
      this.init();

      this.container.innerHTML = `
        <div class="relative w-full max-w-md bg-slate-900 border-2 border-amber-500 rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300" id="modal-card">
          <div class="bg-gradient-to-r from-red-700 via-red-800 to-slate-900 px-5 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-3 h-3 rounded-full bg-amber-400 animate-ping"></span>
              <h3 class="font-pixel text-xs text-amber-300 uppercase tracking-wider">${title}</h3>
            </div>
            <span class="text-xs text-amber-200/70 font-mono">PROMPT</span>
          </div>

          <div class="p-6 text-slate-200">
            <p class="text-sm leading-relaxed whitespace-pre-line">${message}</p>
          </div>

          <div class="bg-slate-950/80 px-6 py-4 flex justify-end gap-3">
            <button id="modal-cancel-btn" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium text-sm transition-all">
              Cancelar
            </button>
            <button id="modal-confirm-btn" class="px-5 py-2 rounded-lg bg-amber-600 hover:bg-amber-500 active:scale-95 text-slate-950 font-bold text-sm transition-all shadow-lg shadow-amber-900/50">
              Prosseguir
            </button>
          </div>
        </div>
      `;

      this.open();

      const cancelBtn = document.getElementById('modal-cancel-btn');
      const confirmBtn = document.getElementById('modal-confirm-btn');

      confirmBtn.focus();

      cancelBtn.onclick = () => {
        this.close();
        resolve(false);
      };

      confirmBtn.onclick = () => {
        this.close();
        resolve(true);
      };
    });
  },

  open() {
    this.container.classList.remove('hidden');
    requestAnimationFrame(() => {
      this.container.classList.remove('opacity-0');
      const card = document.getElementById('modal-card');
      if (card) card.classList.remove('scale-95');
    });
  },

  close() {
    this.container.classList.add('opacity-0');
    const card = document.getElementById('modal-card');
    if (card) card.classList.add('scale-95');
    setTimeout(() => {
      this.container.classList.add('hidden');
    }, 250);
  }
};

// Sobrescreve as caixas nativas do navegador para assegurar estrita conformidade com os requisitos
window.alert = function(msg) {
  return PokedexModal.alert(msg);
};

window.confirm = function(msg) {
  return PokedexModal.confirm(msg);
};

window.PokedexModal = PokedexModal;
