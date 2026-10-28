/**
 * Pokédex Scanner Óptico & Reconhecimento Visual
 * Controla a captura da câmera WebRTC, processamento visual e integração com o backend.
 */

const PokedexScanner = {
  stream: null,
  videoElement: null,
  canvasElement: null,
  currentFacingMode: 'environment', // Traseira no celular por padrão
  isScanning: false,

  // Inicializa os elementos da câmera
  init() {
    this.videoElement = document.getElementById('scanner-video');
    this.canvasElement = document.getElementById('scanner-canvas');
  },

  // Abre a modal do scanner e inicia a câmera
  async openModal() {
    const modal = document.getElementById('scanner-modal');
    if (!modal) return;

    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
      modal.classList.remove('opacity-0');
      const box = document.getElementById('scanner-box');
      if (box) box.classList.remove('scale-95');
    });

    this.playAudioBeep(600, 0.1);
    await this.startCamera();
  },

  // Fecha a modal e interrompe a câmera
  closeModal() {
    this.stopCamera();
    const modal = document.getElementById('scanner-modal');
    if (!modal) return;

    modal.classList.add('opacity-0');
    const box = document.getElementById('scanner-box');
    if (box) box.classList.add('scale-95');
    setTimeout(() => {
      modal.classList.add('hidden');
    }, 250);
  },

  // Inicia o stream da câmera
  async startCamera() {
    this.init();
    const statusText = document.getElementById('scanner-status-text');

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      if (statusText) statusText.textContent = 'ERRO: Câmera não suportada neste navegador.';
      Toast.error('Seu navegador ou dispositivo não possui suporte para acesso direto à câmera.');
      return;
    }

    try {
      if (statusText) statusText.textContent = 'CONECTANDO AOS SENSORES ÓPTICOS...';

      if (this.stream) {
        this.stopCamera();
      }

      const constraints = {
        video: {
          facingMode: this.currentFacingMode,
          width: { ideal: 1280 },
          height: { ideal: 720 }
        },
        audio: false
      };

      this.stream = await navigator.mediaDevices.getUserMedia(constraints);
      if (this.videoElement) {
        this.videoElement.srcObject = this.stream;
        await this.videoElement.play();
      }

      if (statusText) statusText.textContent = 'MIRA ÓPTICA ATIVA - APONTE PARA O POKÉMON';
      Toast.info('Câmera conectada! Aponte para um Pokémon ou card para escanear.');
    } catch (err) {
      console.error('Falha ao acessar câmera:', err);
      if (statusText) statusText.textContent = 'PERMISSÃO DE CÂMERA NEGADA OU DISPOSITIVO INDISPONÍVEL';
      Toast.warning('Não foi possível ativar a câmera ao vivo. Você pode utilizar o botão "Carregar Imagem" abaixo.');
    }
  },

  // Interrompe o stream da câmera
  stopCamera() {
    if (this.stream) {
      this.stream.getTracks().forEach(track => track.stop());
      this.stream = null;
    }
    if (this.videoElement) {
      this.videoElement.srcObject = null;
    }
  },

  // Alterna entre câmera frontal e traseira no celular
  toggleCamera() {
    this.currentFacingMode = (this.currentFacingMode === 'environment') ? 'user' : 'environment';
    this.startCamera();
  },

  // Captura o frame atual da câmera ou processa imagem carregada
  async scanFrame() {
    if (this.isScanning) return;
    this.isScanning = true;

    const statusText = document.getElementById('scanner-status-text');
    const scanBtn = document.getElementById('scanner-capture-btn');
    if (scanBtn) scanBtn.disabled = true;

    this.playShutterSound();

    if (statusText) statusText.textContent = 'ANALISANDO ASSINATURA BIOLÓGICA...';

    let capturedColor = null;

    // Se o vídeo estiver ativo, desenha no canvas para analisar cor predominante
    if (this.videoElement && this.videoElement.readyState >= 2) {
      const canvas = this.canvasElement || document.createElement('canvas');
      canvas.width = 300;
      canvas.height = 300;
      const ctx = canvas.getContext('2d');
      // Captura a região central do visor
      ctx.drawImage(this.videoElement, 0, 0, canvas.width, canvas.height);
      capturedColor = this.getDominantColor(ctx, canvas.width, canvas.height);
    }

    try {
      // Mapeia a assinatura óptica identificada para espécimes compatíveis
      const mappedCandidate = this.guessPokemonByColor(capturedColor);

      const response = await fetch('api/scan.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          pokemon_name: mappedCandidate,
          color_signature: capturedColor
        })
      });

      const result = await response.json();

      if (!result.success) {
        throw new Error(result.message || 'Falha na identificação do espécime.');
      }

      this.closeModal();

      // Checagem do Easter Egg Revolucionário
      if (result.liberated_from_capitalism || (result.pokemon && result.pokemon.national_id === 9999)) {
        this.triggerSovietLiberation(result.pokemon);
      } else {
        // Exibição normal do Pokémon no painel
        Toast.success(`Identificado com sucesso: #${String(result.pokemon.national_id).padStart(3, '0')} ${result.pokemon.name}!`);
        if (window.PokedexApp && window.PokedexApp.selectPokemon) {
          window.PokedexApp.selectPokemon(result.pokemon);
        }
        if (window.PokedexApp && window.PokedexApp.refreshSession) {
          window.PokedexApp.refreshSession();
        }
      }

    } catch (err) {
      console.error(err);
      Toast.error(err.message || 'Erro ao processar escaneamento óptico.');
      if (statusText) statusText.textContent = 'FALHA NO ESCANEAMENTO. TENTE NOVAMENTE.';
    } finally {
      this.isScanning = false;
      if (scanBtn) scanBtn.disabled = false;
    }
  },

  // Carrega imagem de arquivo do usuário como alternativa para teste
  handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = async (e) => {
      const img = new Image();
      img.onload = async () => {
        const canvas = this.canvasElement || document.createElement('canvas');
        canvas.width = 300;
        canvas.height = 300;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, 300, 300);
        const color = this.getDominantColor(ctx, 300, 300);
        
        Toast.info('Imagem carregada! Analisando padrões visuais...');
        this.processDirectIdentification(this.guessPokemonByColor(color));
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  },

  async processDirectIdentification(pokemonName) {
    try {
      const response = await fetch('api/scan.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pokemon_name: pokemonName })
      });
      const result = await response.json();
      if (result.success) {
        this.closeModal();
        if (result.liberated_from_capitalism || (result.pokemon && result.pokemon.national_id === 9999)) {
          this.triggerSovietLiberation(result.pokemon);
        } else {
          Toast.success(`Identificado: ${result.pokemon.name}!`);
          if (window.PokedexApp && window.PokedexApp.selectPokemon) {
            window.PokedexApp.selectPokemon(result.pokemon);
          }
          if (window.PokedexApp && window.PokedexApp.refreshSession) {
            window.PokedexApp.refreshSession();
          }
        }
      } else {
        Toast.error(result.message);
      }
    } catch (e) {
      Toast.error('Erro ao conectar ao serviço de escaneamento.');
    }
  },

  // Análise de cor dominante na região central da imagem capturada
  getDominantColor(ctx, w, h) {
    try {
      const imgData = ctx.getImageData(w * 0.25, h * 0.25, w * 0.5, h * 0.5).data;
      let r = 0, g = 0, b = 0, count = 0;
      for (let i = 0; i < imgData.length; i += 16) {
        r += imgData[i];
        g += imgData[i + 1];
        b += imgData[i + 2];
        count++;
      }
      return {
        r: Math.round(r / count),
        g: Math.round(g / count),
        b: Math.round(b / count)
      };
    } catch (e) {
      return null;
    }
  },

  // Mapeia padrões visuais de cor para Pokémons conhecidos
  guessPokemonByColor(color) {
    if (!color) return null;
    const { r, g, b } = color;

    // Vermelho/Laranja marcante -> Fogo
    if (r > 160 && g < 130 && b < 130) {
      return 'Charizard';
    }
    // Azul marcante -> Água
    if (b > 150 && r < 140) {
      return 'Blastoise';
    }
    // Amarelo marcante -> Elétrico
    if (r > 160 && g > 160 && b < 100) {
      return 'Pikachu';
    }
    // Verde marcante -> Planta
    if (g > 140 && r < 120 && b < 120) {
      return 'Venusaur';
    }
    // Roxo/Sombra -> Fantasma/Psíquico
    if (r > 100 && b > 120 && g < 100) {
      return 'Gengar';
    }

    return null;
  },

  // Dispara a celebração do Fantasma do Comunismo e toca o Hino Soviético
  triggerSovietLiberation(pokemon) {
    const sovietModal = document.getElementById('soviet-modal');
    if (!sovietModal) return;

    sovietModal.classList.remove('hidden');
    requestAnimationFrame(() => {
      sovietModal.classList.remove('opacity-0');
    });

    if (window.SovietAnthem) {
      window.SovietAnthem.play();
    }

    if (window.PokedexApp && window.PokedexApp.selectPokemon) {
      window.PokedexApp.selectPokemon(pokemon);
    }
  },

  closeSovietModal() {
    const sovietModal = document.getElementById('soviet-modal');
    if (!sovietModal) return;

    sovietModal.classList.add('opacity-0');
    setTimeout(() => {
      sovietModal.classList.add('hidden');
    }, 300);

    if (window.SovietAnthem) {
      window.SovietAnthem.stop();
    }
  },

  // Efeito sonoro do obturador
  playShutterSound() {
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return;
    const ctx = new AudioCtx();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();

    osc.type = 'sine';
    osc.frequency.setValueAtTime(800, ctx.currentTime);
    osc.frequency.exponentialRampToValueAtTime(200, ctx.currentTime + 0.15);

    gain.gain.setValueAtTime(0.3, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15);

    osc.connect(gain);
    gain.connect(ctx.destination);

    osc.start();
    osc.stop(ctx.currentTime + 0.15);
  },

  playAudioBeep(freq = 440, duration = 0.1) {
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return;
    const ctx = new AudioCtx();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();

    osc.type = 'triangle';
    osc.frequency.setValueAtTime(freq, ctx.currentTime);
    gain.gain.setValueAtTime(0.15, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + duration);

    osc.connect(gain);
    gain.connect(ctx.destination);

    osc.start();
    osc.stop(ctx.currentTime + duration);
  }
};

window.PokedexScanner = PokedexScanner;
