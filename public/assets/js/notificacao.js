(function () {
  const CHAVE = 'som_chamados';
  const INTERVALO = 10000;
  const COOLDOWN_BIPE = 30000;

  let ctx = null;
  let querSom = false;
  let ultimoVisto = null;
  let abertosVistos = null;
  let consultando = false;
  let encerrado = false;
  let ultimoBipeTime = 0;
  let tituloOriginal = document.title;

  try { querSom = localStorage.getItem(CHAVE) === '1'; } catch (e) {}

  const botao = document.createElement('button');
  botao.id = 'btn-som-notificacao';
  botao.style.cssText = `
    position: fixed; right: 20px; bottom: 20px; z-index: 9999;
    padding: 12px 20px; border: none; border-radius: 8px;
    background: #1d4a8f; color: white; cursor: pointer; font-size: 14px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3); font-weight: bold;
    transition: all 0.3s; display: flex; align-items: center; gap: 8px;
  `;
  document.body.appendChild(botao);

  function atualizarBotao() {
    if (encerrado) {
      botao.innerHTML = '⚠️ Sessão expirada';
      botao.style.background = '#e63e3e';
    } else if (!querSom) {
      botao.innerHTML = '🔇 Ativar som';
      botao.style.background = '#555';
    } else if (ctx && ctx.state === 'running') {
      botao.innerHTML = '🔊 Som ativado';
      botao.style.background = '#1d4a8f';
    } else {
      botao.innerHTML = '🔊 Toque p/ ativar';
      botao.style.background = '#f5a800';
    }
  }

  function garantirContexto() {
    if (!ctx) {
      ctx = new (window.AudioContext || window.webkitAudioContext)();
      ctx.onstatechange = atualizarBotao;
    }
    return ctx;
  }

  function retomar() {
    if (!querSom) return;
    garantirContexto();
    if (ctx.state === 'suspended') ctx.resume().then(atualizarBotao).catch(() => {});
  }
  
  ['pointerdown', 'keydown', 'touchstart'].forEach(ev =>
    document.addEventListener(ev, retomar, { passive: true })
  );

  function bipe() {
    if (!ctx || !querSom) return;
    if (ctx.state === 'suspended') ctx.resume().catch(() => {});
    if (ctx.state !== 'running') return;

    const agora = Date.now();
    if (agora - ultimoBipeTime < COOLDOWN_BIPE) return; 
    ultimoBipeTime = agora;

    const t = ctx.currentTime;
    const notas = [523.25, 659.25, 783.99, 1046.50];
    
    notas.forEach((freq, i) => {
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine'; 
      osc.frequency.value = freq;
      const inicio = t + (i * 0.08); 
      gain.gain.setValueAtTime(0, inicio);
      gain.gain.linearRampToValueAtTime(0.4, inicio + 0.02);
      gain.gain.exponentialRampToValueAtTime(0.001, inicio + 0.5);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(inicio);
      osc.stop(inicio + 0.6);
    });
  }

  function mostrarToast(qtdNovos, tituloChamado) {
    const antigo = document.getElementById('toast-notificacao');
    if (antigo) antigo.remove();

    const toast = document.createElement('div');
    toast.id = 'toast-notificacao';
    toast.style.cssText = `
      position: fixed; top: 20px; right: 20px; z-index: 10000;
      background: var(--superficie, #fff); color: var(--texto, #1f2933); border-left: 6px solid #1d4a8f;
      padding: 16px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
      font-family: 'Segoe UI', sans-serif; max-width: 300px;
      transform: translateX(120%); transition: transform 0.4s ease;
    `;
    
    toast.innerHTML = `
      <h4 style="margin: 0 0 5px; color: var(--azul-texto, #1d4a8f);">Novo Chamado!</h4>
      <p style="margin: 0; font-size: 13px; color: var(--texto-suave, #444);">
        ${qtdNovos > 1 ? `${qtdNovos} chamados chegaram.` : `#${tituloChamado}`}
      </p>
    `;
    document.body.appendChild(toast);

    setTimeout(() => toast.style.transform = 'translateX(0)', 100);
    setTimeout(() => {
      toast.style.transform = 'translateX(120%)';
      setTimeout(() => toast.remove(), 400);
    }, 5000);

    let piscando = true;
    let contador = 0;
    const intervaloTitulo = setInterval(() => {
      if (!piscando) return clearInterval(intervaloTitulo);
      document.title = contador % 2 === 0 ? `🔔 (${qtdNovos}) Novo Chamado!` : tituloOriginal;
      contador++;
    }, 1000);

    window.addEventListener('focus', () => {
      piscando = false;
      document.title = tituloOriginal;
    }, { once: true });
  }

  botao.addEventListener('click', async function () {
    try {
      if (!querSom) {
        querSom = true;
        try { localStorage.setItem(CHAVE, '1'); } catch (e) {}
        garantirContexto();
        await ctx.resume();
        atualizarBotao();
        ultimoBipeTime = 0;
        bipe(); 
      } else {
        querSom = false;
        try { localStorage.setItem(CHAVE, '0'); } catch (e) {}
        atualizarBotao();
      }
    } catch (e) {
      console.error('Erro ao ativar áudio:', e);
    }
  });

  async function verificar() {
    if (consultando || encerrado) return;
    consultando = true;

    try {
      // USA A VARIÁVEL GLOBAL
      const resposta = await fetch(window.APP_BASE_URL + '/api/chamados/status', {
        cache: 'no-store',
        credentials: 'same-origin'
      });

      if (resposta.redirected || !resposta.ok) {
        encerrado = true;
        atualizarBotao();
        return;
      }

      const d = await resposta.json();
      if (!d || !Array.isArray(d.chamados) || d.ultimo_id === undefined) return;

      const ultimoId = Number(d.ultimo_id);
      const abertosAgora = new Set(
        d.chamados.filter(c => c.status === 'aberto').map(c => Number(c.id))
      );

      if (ultimoVisto !== null) {
        const chegouNovo = ultimoId > ultimoVisto;
        const voltouAberto = [...abertosAgora].some(id => !abertosVistos.has(id));
        
        if (chegouNovo || voltouAberto) {
          const qtdNovos = Math.max(1, ultimoId - ultimoVisto);
          bipe();
          mostrarToast(qtdNovos, ultimoId);
        }
      }

      ultimoVisto = Math.max(ultimoVisto ?? 0, ultimoId);
      abertosVistos = abertosAgora;
    } catch (e) {
      console.error('Erro ao verificar chamados:', e);
    } finally {
      consultando = false;
    }
  }

  async function ciclo() {
    if (encerrado) return;
    await verificar();
    if (!encerrado) setTimeout(ciclo, INTERVALO);
  }

  if (querSom) {
    garantirContexto();
    ctx.resume().catch(() => {});
  }
  atualizarBotao();
  ciclo();
})();
