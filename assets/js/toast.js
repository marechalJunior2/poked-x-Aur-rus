/**
 * Sistema de Notificações Toast para a Pokédex
 * Fornece feedback visual elegante, claro e responsivo.
 */

const Toast = {
  container: null,

  init() {
    if (!this.container) {
      this.container = document.createElement('div');
      this.container.id = 'pokedex-toast-container';
      this.container.className = 'fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none px-4 sm:px-0';
      document.body.appendChild(this.container);
    }
  },

  show(message, type = 'info', duration = 4000) {
    this.init();

    const toast = document.createElement('div');
    toast.className = `pointer-events-auto transform transition-all duration-300 ease-out translate-y-4 opacity-0 flex items-start gap-3 p-4 rounded-xl shadow-2xl border text-sm font-medium backdrop-blur-md ${this.getTypeStyles(type)}`;

    const icon = this.getTypeIcon(type);

    toast.innerHTML = `
      <div class="flex-shrink-0 mt-0.5 text-lg">${icon}</div>
      <div class="flex-1 text-slate-100 leading-snug break-words">${message}</div>
      <button class="flex-shrink-0 text-slate-400 hover:text-white transition-colors" onclick="this.parentElement.remove()">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    `;

    this.container.appendChild(toast);

    // Animação de entrada
    requestAnimationFrame(() => {
      toast.classList.remove('translate-y-4', 'opacity-0');
      toast.classList.add('translate-y-0', 'opacity-100');
    });

    // Auto-dismiss
    if (duration > 0) {
      setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-x-full');
        setTimeout(() => toast.remove(), 300);
      }, duration);
    }
  },

  success(msg, duration) {
    this.show(msg, 'success', duration);
  },

  error(msg, duration = 5000) {
    this.show(msg, 'error', duration);
  },

  warning(msg, duration) {
    this.show(msg, 'warning', duration);
  },

  info(msg, duration) {
    this.show(msg, 'info', duration);
  },

  getTypeStyles(type) {
    switch (type) {
      case 'success':
        return 'bg-emerald-950/90 border-emerald-500/50 text-emerald-200';
      case 'error':
        return 'bg-red-950/95 border-red-500/60 text-red-100 ring-1 ring-red-500/30';
      case 'warning':
        return 'bg-amber-950/90 border-amber-500/50 text-amber-200';
      default:
        return 'bg-slate-900/90 border-cyan-500/40 text-cyan-200';
    }
  },

  getTypeIcon(type) {
    switch (type) {
      case 'success':
        return '✅';
      case 'error':
        return '⚠️';
      case 'warning':
        return '⚡';
      default:
        return 'ℹ️';
    }
  }
};

window.Toast = Toast;
