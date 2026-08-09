<!-- Chat do Facebook 2008 - Atendimento Online (Página Fale Conosco) -->
<style>
  .fb2008-chat-launcher {
    position: fixed;
    bottom: 75px;
    right: 20px;
    z-index: 9999;
    background: linear-gradient(135deg, #075be8, #0649c9);
    color: #ffffff;
    border: 1px solid #075be8;
    border-radius: 999px;
    padding: 8px 16px;
    font-family: Tahoma, 'Helvetica Neue', Helvetica, Arial, sans-serif;
    font-size: 13px;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 7px 18px rgba(7, 91, 232, 0.35);
    display: flex;
    align-items: center;
    gap: 8px;
    transition: transform 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
  }
  .fb2008-chat-launcher:hover {
    background: linear-gradient(135deg, #0649c9, #043da9);
    transform: translateY(-2px);
    box-shadow: 0 9px 22px rgba(7, 91, 232, 0.45);
  }
  .fb2008-online-dot {
    width: 9px;
    height: 9px;
    background-color: #42b72a;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 0 1px #ffffff;
  }
  .fb2008-chat-box {
    position: fixed;
    bottom: 0;
    right: 20px;
    width: 290px;
    height: 385px;
    z-index: 100000;
    background: #ffffff;
    border: 1px solid #075be8;
    border-bottom: none;
    border-top-left-radius: 6px;
    border-top-right-radius: 6px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    font-family: Tahoma, Verdana, Arial, sans-serif;
    font-size: 11px;
    transition: background 0.3s ease, border-color 0.3s ease;
  }
  .fb2008-chat-box.collapsed {
    display: none !important;
  }
  .fb2008-chat-header {
    background: linear-gradient(135deg, #075be8, #0649c9);
    color: #ffffff;
    padding: 6px 9px;
    font-size: 12px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top-left-radius: 5px;
    border-top-right-radius: 5px;
    user-select: none;
    cursor: pointer;
  }
  .fb2008-chat-header-title {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #ffffff;
    text-decoration: none;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }
  .fb2008-chat-controls {
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .fb2008-chat-btn {
    background: none;
    border: none;
    color: #ffffff;
    opacity: 0.85;
    cursor: pointer;
    padding: 1px 4px;
    font-size: 12px;
    line-height: 1;
    transition: opacity 0.15s;
  }
  .fb2008-chat-btn:hover {
    opacity: 1;
  }
  .fb2008-chat-btn.muted {
    color: #ffc107;
  }
  .fb2008-chat-body {
    flex: 1;
    background: #edeff4;
    padding: 8px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 6px;
    transition: background 0.3s ease;
  }
  .fb2008-msg {
    max-width: 85%;
    padding: 6px 9px;
    border-radius: 6px;
    line-height: 1.35;
    word-wrap: break-word;
  }
  .fb2008-msg-agent {
    align-self: flex-start;
    background: #ffffff;
    color: #1c1e21;
    border: 1px solid #b3b3b3;
  }
  .fb2008-msg-user {
    align-self: flex-end;
    background: #d8e2f2;
    color: #1c1e21;
    border: 1px solid #9cb1d6;
  }
  .fb2008-msg-time {
    font-size: 9px;
    color: #777;
    margin-top: 3px;
    text-align: right;
  }
  .fb2008-msg-img {
    max-width: 100%;
    max-height: 120px;
    border-radius: 4px;
    margin-top: 4px;
    display: block;
    cursor: pointer;
    border: 1px solid #ccc;
  }
  .fb2008-chat-footer {
    background: #ffffff;
    border-top: 1px solid #c0c0c0;
    padding: 6px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    transition: background 0.3s ease, border-color 0.3s ease;
  }
  .fb2008-preview-bar {
    display: flex;
    gap: 5px;
    padding-bottom: 2px;
  }
  .fb2008-preview-thumb {
    position: relative;
    width: 38px;
    height: 38px;
    border-radius: 3px;
    border: 1px solid #075be8;
    overflow: hidden;
  }
  .fb2008-preview-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .fb2008-preview-thumb .remove-img {
    position: absolute;
    top: 0;
    right: 0;
    background: rgba(0,0,0,0.75);
    color: #fff;
    border: none;
    font-size: 9px;
    width: 14px;
    height: 14px;
    cursor: pointer;
    display: grid;
    place-items: center;
  }
  .fb2008-input-row {
    display: flex;
    align-items: center;
    gap: 5px;
  }
  .fb2008-textarea {
    flex: 1;
    border: 1px solid #bdc7d8;
    border-radius: 3px;
    padding: 5px;
    font-family: inherit;
    font-size: 11px;
    resize: none;
    height: 28px;
    outline: none;
    transition: background 0.3s ease, color 0.3s ease, border-color 0.3s ease;
  }
  .fb2008-textarea:focus {
    border-color: #075be8;
  }
  .fb2008-icon-btn {
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    font-size: 14px;
    padding: 2px 4px;
    transition: color 0.2s ease;
  }
  .fb2008-icon-btn:hover {
    color: #075be8;
  }
  .fb2008-send-btn {
    background: linear-gradient(135deg, #075be8, #0649c9);
    color: #fff;
    border: 1px solid #075be8;
    border-radius: 3px;
    font-size: 10px;
    font-weight: bold;
    padding: 5px 9px;
    cursor: pointer;
  }
  .fb2008-send-btn:hover {
    background: linear-gradient(135deg, #0649c9, #043da9);
  }

  /* Adaptabilidade ao Modo Escuro (Dark Mode) */
  html[data-theme="dark"] .fb2008-chat-box,
  [data-bs-theme="dark"] .fb2008-chat-box,
  [data-theme="dark"] .fb2008-chat-box {
    background: #1e293b !important;
    border-color: #334155 !important;
    color: #f8fafc !important;
  }
  html[data-theme="dark"] .fb2008-chat-body,
  [data-bs-theme="dark"] .fb2008-chat-body,
  [data-theme="dark"] .fb2008-chat-body {
    background: #0f172a !important;
  }
  html[data-theme="dark"] .fb2008-msg-agent,
  [data-bs-theme="dark"] .fb2008-msg-agent,
  [data-theme="dark"] .fb2008-msg-agent {
    background: #1e293b !important;
    color: #f8fafc !important;
    border-color: #334155 !important;
  }
  html[data-theme="dark"] .fb2008-msg-user,
  [data-bs-theme="dark"] .fb2008-msg-user,
  [data-theme="dark"] .fb2008-msg-user {
    background: #1e40af !important;
    color: #ffffff !important;
    border-color: #2563eb !important;
  }
  html[data-theme="dark"] .fb2008-chat-footer,
  [data-bs-theme="dark"] .fb2008-chat-footer,
  [data-theme="dark"] .fb2008-chat-footer {
    background: #1e293b !important;
    border-color: #334155 !important;
  }
  html[data-theme="dark"] .fb2008-textarea,
  [data-bs-theme="dark"] .fb2008-textarea,
  [data-theme="dark"] .fb2008-textarea {
    background: #0f172a !important;
    color: #f8fafc !important;
    border-color: #334155 !important;
  }
  html[data-theme="dark"] .fb2008-icon-btn,
  [data-bs-theme="dark"] .fb2008-icon-btn,
  [data-theme="dark"] .fb2008-icon-btn {
    color: #94a3b8 !important;
  }
  html[data-theme="dark"] .fb2008-msg-time,
  [data-bs-theme="dark"] .fb2008-msg-time,
  [data-theme="dark"] .fb2008-msg-time {
    color: #94a3b8 !important;
  }
</style>

<!-- Botão Flutuante (Launcher) no Canto Inferior Direito - Posição Ajustada acima do Alternador de Tema -->
<button type="button" class="fb2008-chat-launcher" id="fb2008-launcher" onclick="toggleFbSupportChat()">
  <span class="fb2008-online-dot"></span>
  <i class="fa-solid fa-headset"></i>
  <span>Atendimento Online</span>
</button>

<!-- Janela estilo Chat do Facebook 2008 -->
<div class="fb2008-chat-box collapsed" id="fb2008-chat-box">
  <!-- Cabeçalho na cor do Botão Anunciar -->
  <div class="fb2008-chat-header" onclick="toggleFbSupportChat()">
    <div class="fb2008-chat-header-title">
      <span class="fb2008-online-dot"></span>
      <span>Atendimento Conectado</span>
    </div>
    <div class="fb2008-chat-controls" onclick="event.stopPropagation()">
      <!-- Botão Config / Som -->
      <button type="button" class="fb2008-chat-btn" id="fb2008-sound-btn" onclick="toggleFbSound()" title="Ativar/Desativar som do atendimento">
        <i class="fa-solid fa-gear"></i>
      </button>
      <!-- Botão Fechar -->
      <button type="button" class="fb2008-chat-btn" onclick="toggleFbSupportChat()" title="Fechar">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
  </div>

  <!-- Corpo do Chat -->
  <div class="fb2008-chat-body" id="fb2008-chat-body">
    <div class="fb2008-msg fb2008-msg-agent">
      👋 Olá! Bem-vindo ao <strong>Atendimento Online</strong> do Conectado em Sergipe. Como podemos te ajudar hoje?
      <div class="fb2008-msg-time">Suporte · Online agora</div>
    </div>
  </div>

  <!-- Rodapé / Envio de Mensagem e Anexo -->
  <div class="fb2008-chat-footer">
    <!-- Prévia de Imagens Anexadas (Máx 2) -->
    <div class="fb2008-preview-bar" id="fb2008-preview-bar" style="display:none;"></div>

    <div class="fb2008-input-row">
      <!-- Input File Escondido -->
      <input type="file" id="fb2008-file-input" accept="image/*" multiple style="display:none;" onchange="handleFbImagesSelected(this)">
      
      <!-- Botão Anexar Print/Imagem -->
      <button type="button" class="fb2008-icon-btn" onclick="document.getElementById('fb2008-file-input').click()" title="Anexar print ou imagem (Máx. 2 imagens)">
        <i class="fa-solid fa-paperclip"></i>
      </button>

      <!-- Input de Texto -->
      <textarea id="fb2008-text-input" class="fb2008-textarea" placeholder="Digite uma mensagem..." rows="1" onkeydown="handleFbKeyPress(event)"></textarea>

      <!-- Botão Enviar -->
      <button type="button" class="fb2008-send-btn" onclick="sendFbMessage()">Enviar</button>
    </div>
  </div>
</div>

<script>
  let fbSoundEnabled = true;
  let attachedImages = [];

  function toggleFbSupportChat() {
    const box = document.getElementById('fb2008-chat-box');
    const isHidden = box.classList.contains('collapsed');
    if (isHidden) {
      box.classList.remove('collapsed');
      scrollFbChatBottom();
      document.getElementById('fb2008-text-input').focus();
    } else {
      box.classList.add('collapsed');
    }
  }

  function toggleFbSound() {
    fbSoundEnabled = !fbSoundEnabled;
    const btn = document.getElementById('fb2008-sound-btn');
    if (fbSoundEnabled) {
      btn.classList.remove('muted');
      btn.innerHTML = '<i class="fa-solid fa-gear"></i>';
      playBeepSound();
    } else {
      btn.classList.add('muted');
      btn.innerHTML = '<i class="fa-solid fa-volume-xmark"></i>';
    }
  }

  function playBeepSound() {
    if (!fbSoundEnabled) return;
    try {
      const ctx = new (window.AudioContext || window.webkitAudioContext)();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(587.33, ctx.currentTime);
      gain.gain.setValueAtTime(0.08, ctx.currentTime);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start();
      osc.stop(ctx.currentTime + 0.12);
    } catch(e) {}
  }

  function handleFbImagesSelected(input) {
    const files = Array.from(input.files);
    if (attachedImages.length + files.length > 2) {
      alert('Você pode anexar no máximo 2 imagens por mensagem.');
      input.value = '';
      return;
    }

    files.forEach(file => {
      if (attachedImages.length < 2) {
        const reader = new FileReader();
        reader.onload = function(e) {
          attachedImages.push(e.target.result);
          renderFbImagePreviews();
        };
        reader.readAsDataURL(file);
      }
    });

    input.value = '';
  }

  function renderFbImagePreviews() {
    const bar = document.getElementById('fb2008-preview-bar');
    if (attachedImages.length === 0) {
      bar.style.display = 'none';
      bar.innerHTML = '';
      return;
    }

    bar.style.display = 'flex';
    bar.innerHTML = attachedImages.map((src, idx) => `
      <div class="fb2008-preview-thumb">
        <img src="${src}" alt="Anexo">
        <button type="button" class="remove-img" onclick="removeFbImage(${idx})">&times;</button>
      </div>
    `).join('');
  }

  function removeFbImage(index) {
    attachedImages.splice(index, 1);
    renderFbImagePreviews();
  }

  function handleFbKeyPress(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      sendFbMessage();
    }
  }

  function sendFbMessage() {
    const textInput = document.getElementById('fb2008-text-input');
    const messageText = textInput.value.trim();

    if (!messageText && attachedImages.length === 0) {
      return;
    }

    const body = document.getElementById('fb2008-chat-body');
    const now = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    let imgsHtml = '';
    attachedImages.forEach(src => {
      imgsHtml += `<img src="${src}" class="fb2008-msg-img" onclick="window.open('${src}', '_blank')">`;
    });

    const userMsgHtml = `
      <div class="fb2008-msg fb2008-msg-user">
        ${messageText ? escapeHtml(messageText) : ''}
        ${imgsHtml}
        <div class="fb2008-msg-time">${now}</div>
      </div>
    `;

    body.insertAdjacentHTML('beforeend', userMsgHtml);
    textInput.value = '';
    attachedImages = [];
    renderFbImagePreviews();
    scrollFbChatBottom();
    playBeepSound();

    setTimeout(() => {
      const agentMsgHtml = `
        <div class="fb2008-msg fb2008-msg-agent">
          Recebemos sua mensagem! Nossa equipe de atendimento em Sergipe já está analisando para te responder em breve.
          <div class="fb2008-msg-time">${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>
        </div>
      `;
      body.insertAdjacentHTML('beforeend', agentMsgHtml);
      scrollFbChatBottom();
      playBeepSound();
    }, 1200);
  }

  function scrollFbChatBottom() {
    const body = document.getElementById('fb2008-chat-body');
    body.scrollTop = body.scrollHeight;
  }

  function escapeHtml(text) {
    return text.replace(/[&<>"']/g, function(m) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
  }
</script>
