<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Painel de Monitoramento — Monitor de Energia</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    :root {
      --bg: #080a10;
      --surface: #111420;
      --surface2: #1a1e30;
      --surface3: #222840;
      --border: rgba(255,255,255,0.07);
      --text: #e2e6f3;
      --text-muted: #6b7499;
      --green: #22c55e;
      --green-dim: rgba(34,197,94,0.15);
      --red: #f43f5e;
      --red-dim: rgba(244,63,94,0.15);
      --amber: #f59e0b;
      --amber-dim: rgba(245,158,11,0.15);
      --blue: #60a5fa;
      --blue-dim: rgba(96,165,250,0.15);
      --purple: #a78bfa;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: grid;
      grid-template-rows: auto 1fr;
      overflow-x: hidden;
    }

    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background:
        radial-gradient(ellipse at 10% 10%, rgba(96,165,250,0.05) 0%, transparent 40%),
        radial-gradient(ellipse at 90% 90%, rgba(167,139,250,0.05) 0%, transparent 40%),
        radial-gradient(ellipse at 50% 50%, rgba(34,197,94,0.02) 0%, transparent 60%);
      pointer-events: none;
    }

    /* HEADER */
    header {
      background: rgba(8,10,16,0.9);
      backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--border);
      padding: 0 32px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 64px;
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .header-brand {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .brand-logo {
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, var(--blue), var(--purple));
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      box-shadow: 0 4px 20px rgba(96,165,250,0.3);
    }

    .brand-text h1 {
      font-size: 15px;
      font-weight: 700;
      background: linear-gradient(90deg, var(--blue), var(--purple));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .brand-text span {
      font-size: 11px;
      color: var(--text-muted);
    }

    .header-right {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .time-display {
      font-size: 13px;
      font-weight: 600;
      color: var(--text-muted);
      font-variant-numeric: tabular-nums;
    }

    .btn-refresh {
      background: var(--surface2);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 8px 16px;
      font-size: 12px;
      font-weight: 500;
      color: var(--text);
      cursor: pointer;
      font-family: 'Inter', sans-serif;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s;
    }

    .btn-refresh:hover {
      background: var(--surface3);
      border-color: var(--blue);
      color: var(--blue);
    }

    /* LAYOUT */
    .dashboard {
      display: grid;
      grid-template-columns: 1fr 380px;
      grid-template-rows: auto 1fr;
      gap: 20px;
      padding: 24px 32px;
      position: relative;
      z-index: 1;
      max-width: 1400px;
      margin: 0 auto;
      width: 100%;
    }

    /* STATS ROW */
    .stats-row {
      grid-column: 1 / -1;
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
    }

    .stat-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 20px;
      display: flex;
      align-items: flex-start;
      gap: 14px;
      transition: all 0.3s;
      position: relative;
      overflow: hidden;
    }

    .stat-card::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 2px;
      opacity: 0.6;
      transition: opacity 0.3s;
    }

    .stat-card.green::after { background: var(--green); }
    .stat-card.red::after { background: var(--red); }
    .stat-card.amber::after { background: var(--amber); }
    .stat-card.blue::after { background: var(--blue); }

    .stat-icon {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      flex-shrink: 0;
    }

    .stat-icon.green { background: var(--green-dim); }
    .stat-icon.red { background: var(--red-dim); }
    .stat-icon.amber { background: var(--amber-dim); }
    .stat-icon.blue { background: var(--blue-dim); }

    .stat-info { flex: 1; }

    .stat-label {
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-muted);
      margin-bottom: 6px;
      font-weight: 500;
    }

    .stat-value {
      font-size: 24px;
      font-weight: 700;
      line-height: 1;
      margin-bottom: 4px;
    }

    .stat-value.green { color: var(--green); }
    .stat-value.red { color: var(--red); }
    .stat-value.amber { color: var(--amber); }
    .stat-value.blue { color: var(--blue); }

    .stat-sub {
      font-size: 11px;
      color: var(--text-muted);
    }

    /* MAIN PANEL */
    .main-panel {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    /* CAMERA PANEL */
    .camera-panel {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 20px;
      overflow: hidden;
      flex: 1;
    }

    .panel-header {
      padding: 16px 20px;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .panel-title {
      font-size: 13px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--text-muted);
    }

    .status-dot.online { background: var(--green); box-shadow: 0 0 6px var(--green); animation: pulseDot 2s infinite; }
    .status-dot.offline { background: var(--red); }

    @keyframes pulseDot {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    .camera-frame {
      position: relative;
      aspect-ratio: 16/9;
      background: #000;
      overflow: hidden;
    }

    #remoteVideo {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: none;
    }

    .camera-offline {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 12px;
      background: linear-gradient(135deg, #080a10, #111420);
    }

    .camera-offline-icon {
      font-size: 48px;
      opacity: 0.3;
    }

    .camera-offline h3 {
      font-size: 15px;
      font-weight: 600;
      color: var(--text-muted);
    }

    .camera-offline p {
      font-size: 12px;
      color: var(--text-muted);
      opacity: 0.6;
      text-align: center;
      max-width: 260px;
      line-height: 1.5;
    }

    .btn-snapshot {
      background: none;
      border: 1px solid var(--blue);
      border-radius: 10px;
      padding: 8px 16px;
      font-size: 12px;
      font-weight: 500;
      color: var(--blue);
      cursor: pointer;
      font-family: 'Inter', sans-serif;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s;
    }

    .btn-snapshot:hover {
      background: var(--blue-dim);
    }

    .camera-controls {
      padding: 12px 20px;
      display: flex;
      gap: 8px;
      border-top: 1px solid var(--border);
    }

    .btn-cam-action {
      flex: 1;
      background: var(--surface2);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 10px;
      font-size: 12px;
      font-weight: 500;
      color: var(--text-muted);
      cursor: pointer;
      font-family: 'Inter', sans-serif;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }

    .btn-cam-action:hover { background: var(--surface3); color: var(--text); }

    .btn-cam-action.primary {
      background: linear-gradient(135deg, rgba(96,165,250,0.2), rgba(167,139,250,0.2));
      border-color: rgba(96,165,250,0.3);
      color: var(--blue);
    }

    .btn-cam-action.primary:hover {
      background: linear-gradient(135deg, rgba(96,165,250,0.3), rgba(167,139,250,0.3));
    }

    /* Snapshot preview */
    #snapshotPreview {
      display: none;
      max-width: 100%;
      border-radius: 8px;
      margin: 12px 20px;
    }

    /* DEVICE STATUS PANEL */
    .device-panel {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 20px;
      overflow: hidden;
    }

    .device-list {
      padding: 8px 0;
    }

    .device-item {
      padding: 14px 20px;
      display: flex;
      align-items: flex-start;
      gap: 12px;
      border-bottom: 1px solid var(--border);
      transition: background 0.2s;
    }

    .device-item:last-child { border-bottom: none; }
    .device-item:hover { background: rgba(255,255,255,0.02); }

    .device-status-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      flex-shrink: 0;
    }

    .device-info { flex: 1; min-width: 0; }

    .device-name {
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 3px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .device-details {
      font-size: 11px;
      color: var(--text-muted);
      line-height: 1.4;
    }

    .device-badge {
      font-size: 11px;
      font-weight: 600;
      padding: 3px 10px;
      border-radius: 20px;
      flex-shrink: 0;
    }

    .device-badge.green { background: var(--green-dim); color: var(--green); }
    .device-badge.red { background: var(--red-dim); color: var(--red); }
    .device-badge.amber { background: var(--amber-dim); color: var(--amber); }

    /* SIDEBAR */
    .sidebar {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    /* EVENT LOG SIDEBAR */
    .log-panel {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 20px;
      overflow: hidden;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .log-body {
      flex: 1;
      overflow-y: auto;
      padding: 8px 0;
      max-height: 420px;
    }

    .log-body::-webkit-scrollbar { width: 4px; }
    .log-body::-webkit-scrollbar-track { background: transparent; }
    .log-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }

    .log-entry {
      padding: 10px 16px;
      border-bottom: 1px solid rgba(255,255,255,0.03);
      display: flex;
      gap: 10px;
      align-items: flex-start;
      animation: fadeSlide 0.3s ease;
    }

    @keyframes fadeSlide {
      from { opacity: 0; transform: translateX(10px); }
      to { opacity: 1; transform: translateX(0); }
    }

    .log-entry:last-child { border-bottom: none; }

    .le-icon {
      font-size: 14px;
      margin-top: 1px;
      flex-shrink: 0;
    }

    .le-content { flex: 1; min-width: 0; }

    .le-msg {
      font-size: 12px;
      font-weight: 500;
      line-height: 1.3;
      margin-bottom: 3px;
    }

    .le-time {
      font-size: 10px;
      color: var(--text-muted);
    }

    .log-empty-state {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
      gap: 8px;
      color: var(--text-muted);
      font-size: 12px;
    }

    /* CONNECTION CONFIG */
    .config-panel {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 20px;
    }

    .config-panel h3 {
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 4px;
      color: var(--text);
    }

    .config-panel p {
      font-size: 11px;
      color: var(--text-muted);
      margin-bottom: 14px;
      line-height: 1.5;
    }

    .qr-placeholder {
      background: var(--surface2);
      border: 1px dashed var(--border);
      border-radius: 12px;
      padding: 16px;
      text-align: center;
      margin-bottom: 12px;
    }

    .qr-placeholder .qr-url {
      font-size: 11px;
      color: var(--blue);
      word-break: break-all;
      font-weight: 500;
      background: var(--surface3);
      border-radius: 6px;
      padding: 8px;
      margin-top: 8px;
      cursor: pointer;
      user-select: all;
    }

    .qr-placeholder .qr-label {
      font-size: 11px;
      color: var(--text-muted);
    }

    .btn-copy-url {
      width: 100%;
      background: var(--surface2);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 10px;
      font-size: 12px;
      font-weight: 500;
      color: var(--text);
      cursor: pointer;
      font-family: 'Inter', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      transition: all 0.2s;
    }

    .btn-copy-url:hover { background: var(--surface3); border-color: var(--blue); color: var(--blue); }

    /* ALERT BADGE */
    .alert-banner {
      display: none;
      background: linear-gradient(135deg, rgba(244,63,94,0.15), rgba(244,63,94,0.05));
      border: 1px solid rgba(244,63,94,0.4);
      border-radius: 12px;
      padding: 14px 18px;
      align-items: center;
      gap: 12px;
      animation: alertPulse 1.5s ease-in-out infinite;
    }

    .alert-banner.show { display: flex; }

    @keyframes alertPulse {
      0%, 100% { box-shadow: 0 0 0 0 rgba(244,63,94,0.3); }
      50% { box-shadow: 0 0 20px 4px rgba(244,63,94,0.1); }
    }

    .alert-icon { font-size: 24px; }

    .alert-text h4 { font-size: 14px; font-weight: 700; color: var(--red); }
    .alert-text p { font-size: 12px; color: var(--text-muted); }

    /* Responsive */
    @media (max-width: 900px) {
      .dashboard {
        grid-template-columns: 1fr;
        padding: 16px;
      }
      .stats-row {
        grid-template-columns: repeat(2, 1fr);
      }
      .sidebar {
        order: -1;
      }
    }

    @media (max-width: 560px) {
      .stats-row { grid-template-columns: 1fr 1fr; }
      header { padding: 0 16px; }
    }
  </style>
</head>
<body>

<header>
  <div class="header-brand">
    <div class="brand-logo">⚡</div>
    <div class="brand-text">
      <h1>Monitor de Energia</h1>
      <span>Painel de Controle Remoto</span>
    </div>
  </div>
  <div class="header-right">
    <span class="time-display" id="clockDisplay">--:--:--</span>
    <button class="btn-refresh" onclick="loadStatus()">🔄 Atualizar</button>
  </div>
</header>

<div class="dashboard">

  <!-- STATS ROW -->
  <div class="stats-row">
    <div class="stat-card green">
      <div class="stat-icon green">⚡</div>
      <div class="stat-info">
        <div class="stat-label">Status Atual</div>
        <div class="stat-value green" id="statStatus">--</div>
        <div class="stat-sub" id="statStatusSub">Aguardando dados...</div>
      </div>
    </div>
    <div class="stat-card blue">
      <div class="stat-icon blue">🔋</div>
      <div class="stat-info">
        <div class="stat-label">Nível Bateria</div>
        <div class="stat-value blue" id="statBattery">--%</div>
        <div class="stat-sub" id="statBatterySub">Celular sensor</div>
      </div>
    </div>
    <div class="stat-card red">
      <div class="stat-icon red">🔴</div>
      <div class="stat-info">
        <div class="stat-label">Quedas Hoje</div>
        <div class="stat-value red" id="statOutages">0</div>
        <div class="stat-sub" id="statOutagesSub">Sem quedas registradas</div>
      </div>
    </div>
    <div class="stat-card amber">
      <div class="stat-icon amber">🕐</div>
      <div class="stat-info">
        <div class="stat-label">Última Atualização</div>
        <div class="stat-value amber" id="statLastUpdate">--</div>
        <div class="stat-sub" id="statUpdateSub">Aguardando...</div>
      </div>
    </div>
  </div>

  <!-- MAIN PANEL -->
  <div class="main-panel">

    <!-- Alert Banner -->
    <div class="alert-banner" id="alertBanner">
      <div class="alert-icon">🚨</div>
      <div class="alert-text">
        <h4>QUEDA DE ENERGIA DETECTADA!</h4>
        <p id="alertTime">Sem dados ainda</p>
      </div>
    </div>

    <!-- Camera Panel -->
    <div class="camera-panel">
      <div class="panel-header">
        <div class="panel-title">
          <span class="status-dot" id="cameraDot"></span>
          📷 Câmera Remota
        </div>
        <button class="btn-snapshot" id="btnRequestCam" onclick="requestCameraView()">
          📡 Solicitar Câmera
        </button>
      </div>
      <div class="camera-frame">
        <div class="camera-offline" id="cameraOffline">
          <div class="camera-offline-icon">📷</div>
          <h3>Câmera Offline</h3>
          <p>O celular precisa estar com a câmera ativada no app mobile. Clique em "Solicitar Câmera" para enviar uma solicitação ao dispositivo.</p>
        </div>
        <video id="remoteVideo" autoplay playsinline muted></video>
      </div>
      <div style="padding: 12px 20px; display: flex; gap: 8px; flex-direction: column;">
        <img id="snapshotPreview" src="" alt="Snapshot da câmera" />
      </div>
      <div class="camera-controls">
        <button class="btn-cam-action primary" onclick="takeSnapshot()">📸 Tirar Foto</button>
        <button class="btn-cam-action" onclick="requestAudio()">🎙️ Ouvir Local</button>
        <button class="btn-cam-action" onclick="saveSnapshot()">💾 Salvar</button>
      </div>
      <div id="audioContainer" style="display:none; padding:12px 20px; background:var(--surface2); border-top:1px solid var(--border)">
         <p style="font-size:11px; margin-bottom:8px">🎙️ Último Áudio Recebido:</p>
         <audio id="audioPlayer" controls style="width:100%"></audio>
      </div>
    </div>

    <!-- Device Status -->
    <div class="device-panel">
      <div class="panel-header">
        <div class="panel-title">📱 Dispositivos Monitorados</div>
        <span style="font-size:11px; color: var(--text-muted)" id="deviceCount">Nenhum</span>
      </div>
      <div class="device-list" id="deviceList">
        <div style="padding: 20px; text-align:center; color: var(--text-muted); font-size: 12px;">
          Nenhum dispositivo conectado ainda.
        </div>
      </div>
    </div>
  </div>

  <!-- SIDEBAR -->
  <div class="sidebar">

    <!-- Config -->
    <div class="config-panel">
      <h3>📲 Conectar Celular</h3>
      <p>Abra este link no navegador do celular para iniciar o monitoramento:</p>
      <div class="qr-placeholder">
        <div class="qr-label">Link para o celular:</div>
        <div class="qr-url" id="mobileUrl" onclick="copyUrl()">Calculando...</div>
      </div>
      <button class="btn-copy-url" onclick="copyUrl()">📋 Copiar Link</button>
    </div>

    <!-- Event Log -->
    <div class="log-panel">
      <div class="panel-header">
        <div class="panel-title">📋 Eventos em Tempo Real</div>
        <button style="background:none; border:1px solid var(--border); border-radius:6px; padding:3px 8px; font-size:11px; color:var(--text-muted); cursor:pointer; font-family:inherit;" onclick="clearDashboardLog()">Limpar</button>
      </div>
      <div class="log-body" id="dashboardLog">
        <div class="log-empty-state">
          <span style="font-size:24px; opacity:0.3">📋</span>
          <p>Aguardando eventos do celular...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// ==========================================
// STATE
// ==========================================
let devices = {};
let dashLog = [];
let webrtcPeer = null;
let snapshotCanvas = null;
let lastSnapshotUrl = null;
let statusPollInterval = null;

// ==========================================
// CLOCK
// ==========================================
function updateClock() {
  document.getElementById('clockDisplay').textContent =
    new Date().toLocaleTimeString('pt-BR');
}
setInterval(updateClock, 1000);
updateClock();

// ==========================================
// MOBILE URL
// ==========================================
window.addEventListener('DOMContentLoaded', () => {
  const base = window.location.href.replace('dashboard.php', '').replace('dashboard.html', '');
  const mobileLink = base + 'mobile.html';
  document.getElementById('mobileUrl').textContent = mobileLink;
  loadStatus();
  startPolling();
  // Iniciar busca por áudios novos assim que abrir o painel
  setInterval(checkNewAudio, 5000); 
});

function copyUrl() {
  const url = document.getElementById('mobileUrl').textContent;
  navigator.clipboard.writeText(url).then(() => showNotif('✅ Link copiado!'));
}

// ==========================================
// POLLING SERVER
// ==========================================
function startPolling() {
  if (statusPollInterval) clearInterval(statusPollInterval);
  statusPollInterval = setInterval(loadStatus, 10000); // every 10s
}

function loadStatus() {
  fetch('api.php?action=status')
    .then(r => r.json())
    .then(data => {
      updateDashboard(data);
    })
    .catch(err => {
      console.warn('Polling error:', err);
    });
}

function updateDashboard(data) {
  const { devices: devData = {}, events: evts = [], outages_today = 0 } = data;

  // Update devices
  devices = devData;
  renderDevices();

  // Update stats
  const deviceList = Object.values(devData);
  const latestDevice = deviceList.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp))[0];

  if (latestDevice) {
    const charging = latestDevice.charging;
    const level = latestDevice.level;
    const ts = new Date(latestDevice.timestamp);
    const elapsed = Math.round((Date.now() - ts) / 1000);

    document.getElementById('statStatus').textContent = charging ? '✅ Normal' : '🔴 Sem Energia';
    document.getElementById('statStatus').style.color = charging ? 'var(--green)' : 'var(--red)';
    document.getElementById('statStatusSub').textContent = charging ? 'Energia presente' : 'QUEDA DETECTADA!';
    document.getElementById('statBattery').textContent = (level !== null ? level : '--') + '%';
    document.getElementById('statLastUpdate').textContent = elapsed < 60 ? elapsed + 's' : Math.round(elapsed/60) + 'min';
    document.getElementById('statUpdateSub').textContent = ts.toLocaleTimeString('pt-BR');

    // Alert
    const alertBanner = document.getElementById('alertBanner');
    if (!charging) {
      alertBanner.classList.add('show');
      document.getElementById('alertTime').textContent = 'Detectado em ' + ts.toLocaleTimeString('pt-BR');
    } else {
      alertBanner.classList.remove('show');
    }
  }

  document.getElementById('statOutages').textContent = outages_today;
  document.getElementById('statOutagesSub').textContent = outages_today === 0
    ? 'Sem quedas hoje' : outages_today + (outages_today === 1 ? ' queda hoje' : ' quedas hoje');

  // Events
  if (evts && evts.length > 0) {
    const newEvents = evts.filter(e => !dashLog.find(d => d.id === e.id));
    newEvents.reverse().forEach(e => {
      dashLog.unshift(e);
    });
    if (dashLog.length > 100) dashLog = dashLog.slice(0, 100);
    renderDashLog();
  }
}

// ==========================================
// RENDER DEVICES
// ==========================================
function renderDevices() {
  const list = document.getElementById('deviceList');
  const count = document.getElementById('deviceCount');
  const keys = Object.keys(devices);

  count.textContent = keys.length + (keys.length === 1 ? ' dispositivo' : ' dispositivos');

  if (keys.length === 0) {
    list.innerHTML = '<div style="padding:20px; text-align:center; color:var(--text-muted); font-size:12px;">Nenhum dispositivo conectado.</div>';
    return;
  }

  list.innerHTML = keys.map(id => {
    const d = devices[id];
    const charging = d.charging;
    const level = d.level;
    const ts = new Date(d.timestamp);
    const elapsed = Math.round((Date.now() - ts) / 1000);
    const isOnline = elapsed < 60;

    const iconBg = charging ? 'var(--green-dim)' : 'var(--red-dim)';
    const icon = charging ? '⚡' : '🔌';
    const badgeClass = isOnline ? (charging ? 'green' : 'red') : 'amber';
    const badgeText = !isOnline ? 'Offline' : charging ? 'Com Energia' : 'Sem Energia';

    return `
      <div class="device-item">
        <div class="device-status-icon" style="background:${iconBg}">${icon}</div>
        <div class="device-info">
          <div class="device-name">📱 ${id.substring(0, 20)}</div>
          <div class="device-details">🔋 ${level !== null ? level + '%' : '--'}  •  Atualizado: ${elapsed < 60 ? elapsed + 's atrás' : Math.round(elapsed/60) + 'min atrás'}</div>
        </div>
        <div class="device-badge ${badgeClass}">${badgeText}</div>
      </div>
    `;
  }).join('');
}

// ==========================================
// DASHBOARD LOG
// ==========================================
function renderDashLog() {
  const el = document.getElementById('dashboardLog');
  if (dashLog.length === 0) {
    el.innerHTML = '<div class="log-empty-state"><span style="font-size:24px;opacity:0.3">📋</span><p>Aguardando eventos...</p></div>';
    return;
  }
  el.innerHTML = dashLog.slice(0, 50).map(e => {
    const icon = e.type === 'charging' ? '⚡' : e.type === 'discharging' || e.event === 'power_outage' ? '🔴' : 'ℹ️';
    const ts = new Date(e.time || e.timestamp);
    const timeStr = ts.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second:'2-digit' });
    const dateStr = ts.toLocaleDateString('pt-BR');
    const msg = e.message || (e.event === 'power_outage' ? '🔴 Queda de energia detectada!' : e.event === 'charging_start' ? '⚡ Energia voltou' : 'Evento: ' + (e.event || '?'));
    return `
      <div class="log-entry">
        <div class="le-icon">${icon}</div>
        <div class="le-content">
          <div class="le-msg">${msg}</div>
          <div class="le-time">${dateStr} às ${timeStr}${e.battery !== undefined && e.battery !== null ? ' • 🔋 ' + e.battery + '%' : ''}</div>
        </div>
      </div>
    `;
  }).join('');
}

function clearDashboardLog() {
  dashLog = [];
  renderDashLog();
}

// ==========================================
// CAMERA (Auto-refresh snapshots)
// ==========================================
let liveViewInterval = null;
let localPC = null;

function requestCameraView() {
  showNotif('📡 Iniciando monitoramento (Fotos + Vídeo)...');
  
  // 1. Liga o monitoramento por FOTOS (Backup garantido)
  startLiveView();
  
  // 2. Tenta iniciar o VÍDEO REAL em paralelo
  startWebRTC();
  
  // 3. Notifica o celular
  fetch('api.php?action=request_camera');
}

async function startWebRTC() {
  if (localPC) { try { localPC.close(); } catch(e){} }
  
  localPC = new RTCPeerConnection({
    iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
  });

  localPC.ontrack = (event) => {
    console.log('Recebendo trilha de vídeo!');
    const video = document.getElementById('remoteVideo');
    video.srcObject = event.streams[0];
    video.style.display = 'block';
    document.getElementById('cameraOffline').style.display = 'none';
    document.getElementById('snapshotPreview').style.display = 'none';
  };

  localPC.onicecandidate = (event) => {
    if (event.candidate) {
      fetch('api.php?action=push_signal&type=ice_dash', {
        method: 'POST', body: JSON.stringify(event.candidate)
      });
    }
  };

  // Criar oferta de vídeo
  const offer = await localPC.createOffer({ offerToReceiveVideo: true });
  await localPC.setLocalDescription(offer);

  // Enviar oferta para o servidor
  fetch('api.php?action=push_signal&type=offer', {
    method: 'POST', body: JSON.stringify(offer)
  });

  showNotif('⏳ Aguardando resposta do celular...');
  
  // Começar a procurar pela resposta (Answer)
  pollForWebRTCAnswer();
}

function pollForWebRTCAnswer() {
  const check = setInterval(() => {
    fetch('api.php?action=pull_signal&type=answer')
      .then(r => r.json())
      .then(async data => {
        if (data.ok && data.data.sdp) {
          clearInterval(check);
          console.log('Resposta do celular recebida!');
          await localPC.setRemoteDescription(new RTCSessionDescription(data.data));
          showNotif('✅ Vídeo conectado!', 'success');
        }
      });
    
    // Também buscar candidatos ICE do celular
    fetch('api.php?action=pull_signal&type=ice_mobile')
      .then(r => r.json())
      .then(data => {
        if (data.ok) localPC.addIceCandidate(new RTCIceCandidate(data.data));
      });

  }, 3000);
}

function startLiveView() {
  // Garante que o sistema de fotos comece a rodar imediatamente
  if (liveViewInterval) clearInterval(liveViewInterval);
  updateImageOnly(); 
  liveViewInterval = setInterval(updateImageOnly, 3000);
}

function requestAudio() {
  showNotif('🎙️ Solicitando áudio... Aguarde 10s.', 'info');
  // Forçar o container a aparecer com aviso de carregando
  const container = document.getElementById('audioContainer');
  const player = document.getElementById('audioPlayer');
  container.style.display = 'block';
  container.style.opacity = '0.5';
  
  fetch('api.php?action=push_signal&type=request_audio', {
     method: 'POST', body: JSON.stringify({ requested: true })
  });
  
  // Tenta carregar o áudio repetidamente nos próximos 15 segundos
  let attempts = 0;
  const retry = setInterval(() => {
     checkNewAudio();
     attempts++;
     if (attempts > 10) clearInterval(retry);
  }, 2000);
}

function checkNewAudio() {
  const player = document.getElementById('audioPlayer');
  const container = document.getElementById('audioContainer');
  const audioUrl = 'data/audio_record.webm';
  
  fetch(audioUrl, { method: 'HEAD', cache: 'no-cache' })
    .then(res => {
       if (res.ok) {
          const finalUrl = audioUrl + '?t=' + Date.now();
          // Se for um áudio novo ou o container estiver apagado
          if (container.style.opacity === '0.5' || player.src.indexOf('audio_record') === -1) {
             player.src = finalUrl;
             container.style.display = 'block';
             container.style.opacity = '1';
             showNotif('🎵 ÁUDIO RECEBIDO!', 'success');
          }
       }
    });
}

// Esta função apenas atualiza a imagem no painel
function updateImageOnly() {
  fetch('api.php?action=get_snapshot')
    .then(r => r.json())
    .then(data => {
      if (data.snapshot) {
        const img = document.getElementById('snapshotPreview');
        img.src = data.snapshot; // Removido o cache-buster que quebrava o Base64
        img.style.display = 'block';
        lastSnapshotUrl = data.snapshot;
        
        // Esconde o aviso de offline
        document.getElementById('cameraOffline').style.display = 'none';
        
        const status = document.getElementById('liveStatus');
        if (status) status.textContent = 'Sincronizado: ' + new Date().toLocaleTimeString();
      }
    }).catch(err => console.error('Erro ao buscar foto:', err));
}

// Esta função é chamada quando você clica no botão "Tirar Foto"
function takeSnapshot() {
  showNotif('📸 Buscando foto atual...');
  updateImageOnly();
  if (!liveViewInterval) {
    showNotif('💡 Dica: O modo automático foi ativado.');
    startLiveView();
  }
}

function saveSnapshot() {
  if (!lastSnapshotUrl) { showNotif('⚠️ Tire um snapshot primeiro', 'warn'); return; }
  const a = document.createElement('a');
  a.href = lastSnapshotUrl;
  a.download = 'monitor_' + new Date().toISOString().replace(/[:.]/g, '-') + '.jpg';
  a.click();
}

function disconnectCamera() {
  const video = document.getElementById('remoteVideo');
  video.style.display = 'none';
  document.getElementById('cameraOffline').style.display = 'flex';
  document.getElementById('snapshotPreview').style.display = 'none';
  showNotif('📷 Câmera desconectada');
}

// ==========================================
// NOTIFICATION
// ==========================================
let notifTimeout;
function showNotif(msg, type = 'info') {
  // Simple inline notification
  console.log('[Monitor]', msg);
  const el = document.createElement('div');
  el.style.cssText = `
    position: fixed; bottom: 24px; right: 24px; z-index: 9999;
    background: #1a1e30; border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px; padding: 12px 20px; font-size: 13px; font-weight: 500;
    color: #e2e6f3; box-shadow: 0 10px 40px rgba(0,0,0,0.5);
    animation: slideNotif 0.3s ease;
    font-family: 'Inter', sans-serif;
  `;
  el.textContent = msg;
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 3500);
}
</script>
</body>
</html>
