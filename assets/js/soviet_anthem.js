/**
 * Sintetizador Web Audio API: Hino da União Soviética
 * Reproduz com fidelidade a melodia monumental do hino soviético
 * de forma 100% autônoma, sem dependência de conexões externas.
 */

const SovietAnthem = {
  ctx: null,
  isPlaying: false,
  activeNodes: [],
  intervalId: null,

  initContext() {
    if (!this.ctx) {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      this.ctx = new AudioCtx();
    }
    if (this.ctx.state === 'suspended') {
      this.ctx.resume();
    }
  },

  // Frequências das notas em Hertz
  notes: {
    'C3': 130.81, 'D3': 146.83, 'E3': 164.81, 'F3': 174.61, 'G3': 196.00, 'A3': 220.00, 'B3': 246.94,
    'C4': 261.63, 'D4': 293.66, 'E4': 329.63, 'F4': 349.23, 'G4': 392.00, 'A4': 440.00, 'B4': 493.88,
    'C5': 523.25, 'D5': 587.33, 'E5': 659.25, 'F5': 698.46, 'G5': 783.99, 'A5': 880.00
  },

  // Partitura da melodia principal do Hino da União Soviética (Alexander Alexandrov)
  score: [
    // Compassos 1 e 2: "Soyuz nerushimyy respublik svobodnykh"
    { note: 'G4', dur: 0.8 }, { note: 'C5', dur: 1.1 }, { note: 'G4', dur: 0.5 }, { note: 'A4', dur: 0.5 }, { note: 'B4', dur: 0.8 },
    { note: 'E4', dur: 0.8 }, { note: 'E4', dur: 0.4 }, { note: 'A4', dur: 0.8 }, { note: 'G4', dur: 0.5 }, { note: 'F4', dur: 0.5 },
    { note: 'G4', dur: 1.0 }, { note: 'C4', dur: 0.8 }, { note: 'C4', dur: 0.4 },

    // Compassos 3 e 4: "Splotila naveki velikaya Rus"
    { note: 'D4', dur: 0.8 }, { note: 'D4', dur: 0.4 }, { note: 'E4', dur: 0.5 }, { note: 'F4', dur: 0.5 }, { note: 'F4', dur: 0.8 },
    { note: 'G4', dur: 0.5 }, { note: 'A4', dur: 0.5 }, { note: 'B4', dur: 0.8 }, { note: 'C5', dur: 0.5 }, { note: 'D5', dur: 0.5 },
    { note: 'E5', dur: 1.6 },

    // Refrão triunfal: "Slav'sya, Otechestvo nashe svobodnoye"
    { note: 'G4', dur: 0.8 }, { note: 'E5', dur: 1.2 }, { note: 'D5', dur: 0.5 }, { note: 'C5', dur: 0.5 }, { note: 'D5', dur: 0.8 },
    { note: 'B4', dur: 0.8 }, { note: 'G4', dur: 0.6 }, { note: 'C5', dur: 1.0 }, { note: 'B4', dur: 0.5 }, { note: 'A4', dur: 0.5 },
    { note: 'B4', dur: 0.8 }, { note: 'E4', dur: 0.8 }, { note: 'E4', dur: 0.6 },

    // Clímax revolucionário
    { note: 'A4', dur: 0.8 }, { note: 'G4', dur: 0.5 }, { note: 'F4', dur: 0.5 }, { note: 'G4', dur: 0.9 }, { note: 'C4', dur: 0.8 },
    { note: 'D4', dur: 0.5 }, { note: 'E4', dur: 0.5 }, { note: 'F4', dur: 0.8 }, { note: 'G4', dur: 0.5 }, { note: 'A4', dur: 0.5 },
    { note: 'B4', dur: 0.8 }, { note: 'C5', dur: 2.2 }
  ],

  play() {
    this.stop();
    this.initContext();
    this.isPlaying = true;

    let currentTime = this.ctx.currentTime + 0.1;
    const tempoMultiplier = 0.9;

    this.score.forEach(item => {
      const freq = this.notes[item.note] || 440;
      const duration = item.dur * tempoMultiplier;

      // Oscilador Principal (Melodia Dourada)
      const osc = this.ctx.createOscillator();
      osc.type = 'sawtooth';
      osc.frequency.setValueAtTime(freq, currentTime);

      // Oscilador Secundário (Harmonia Sub-oitava para peso de coro)
      const subOsc = this.ctx.createOscillator();
      subOsc.type = 'square';
      subOsc.frequency.setValueAtTime(freq / 2, currentTime);

      // Filtro Lowpass quente analógico
      const filter = this.ctx.createBiquadFilter();
      filter.type = 'lowpass';
      filter.frequency.setValueAtTime(1400, currentTime);

      // Envelope de Volume (Gain)
      const gain = this.ctx.createGain();
      gain.gain.setValueAtTime(0.001, currentTime);
      gain.gain.exponentialRampToValueAtTime(0.22, currentTime + 0.05);
      gain.gain.exponentialRampToValueAtTime(0.18, currentTime + duration * 0.8);
      gain.gain.exponentialRampToValueAtTime(0.001, currentTime + duration - 0.02);

      // Conexões de Áudio
      osc.connect(filter);
      subOsc.connect(filter);
      filter.connect(gain);
      gain.connect(this.ctx.destination);

      osc.start(currentTime);
      osc.stop(currentTime + duration);

      subOsc.start(currentTime);
      subOsc.stop(currentTime + duration);

      this.activeNodes.push(osc, subOsc, gain);

      currentTime += duration;
    });

    // Se terminar o loop, pode reiniciar se ainda estiver marcado como playing
    const totalDuration = (currentTime - this.ctx.currentTime) * 1000;
    this.intervalId = setTimeout(() => {
      if (this.isPlaying) {
        this.play();
      }
    }, totalDuration);
  },

  stop() {
    this.isPlaying = false;
    if (this.intervalId) {
      clearTimeout(this.intervalId);
      this.intervalId = null;
    }
    this.activeNodes.forEach(node => {
      try {
        if (node.stop) node.stop();
        if (node.disconnect) node.disconnect();
      } catch (e) {}
    });
    this.activeNodes = [];
  }
};

window.SovietAnthem = SovietAnthem;
