// ==========================================
// COMMAND POLLING
// ==========================================

function startPolling() {
  if (pollInterval) clearInterval(pollInterval);
  // Heartbeat a cada 15s
  pollInterval = setInterval(() => {
    if (battery) {
      sendStatusToServer({ event: 'heartbeat', level: battery.level, charging: battery.charging });
    }
  }, 15000);
  
  // Checagem mais rápida para comandos do painel (Foto e Áudio)
  setInterval(checkCameraRequest, 5000);
  
  // Iniciar sensor de ruído
  initNoiseSensor();
}

function checkCameraRequest() {
  if (!serverUrl || !isConnected) return;
  fetch(serverUrl + '/api.php?action=check_camera_request')
    .then(r => r.json())
    .then(data => {
      if (data.requested) {
        console.log('Ação: Capturando snapshot...');
        captureAndUploadSnapshot();
      }
      if (data.audio_requested) {
        console.log('Ação: Gravando áudio...');
        recordAudio(10000, "Solicitado pelo painel");
      }
    }).catch(e => console.error('Erro no polling:', e));
}

// ==========================================
// SNAPSHOT & CAMERA
// ==========================================
async function captureAndUploadSnapshot() {
  if (!cameraStream) {
    console.log('Câmera desligada, tentando ligar para foto...');
    const toggle = document.getElementById('cameraToggle');
    toggle.checked = true;
    await toggleCamera();
    setTimeout(captureAndUploadSnapshot, 2000); // Tenta de novo após ligar
    return;
  }
  
  const video = document.getElementById('cameraPreview');
  const canvas = document.createElement('canvas');
  canvas.width = video.videoWidth || 1280;
  canvas.height = video.videoHeight || 720;
  const ctx = canvas.getContext('2d');
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
  const base64 = canvas.toDataURL('image/jpeg', 0.85);

  fetch(serverUrl + '/api.php?action=upload_snapshot', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ image: base64, sessionId })
  }).then(() => {
    showToast('📸 Foto enviada!', 'success');
  });
}

// ==========================================
// AUDIO
// ==========================================
let audioContext, analyser, microphone, javascriptNode;
let isRecording = false;

async function initNoiseSensor() {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    audioContext = new AudioContext();
    analyser = audioContext.createAnalyser();
    microphone = audioContext.createMediaStreamSource(stream);
    javascriptNode = audioContext.createScriptProcessor(2048, 1, 1);
    analyser.smoothingTimeConstant = 0.8;
    analyser.fftSize = 1024;
    microphone.connect(analyser);
    analyser.connect(javascriptNode);
    javascriptNode.connect(audioContext.destination);

    javascriptNode.onaudioprocess = () => {
      const array = new Uint8Array(analyser.frequencyBinCount);
      analyser.getByteFrequencyData(array);
      let values = 0;
      for (let i = 0; i < array.length; i++) values += array[i];
      const average = values / array.length;
      if (average > 65 && !isRecording) {
        recordAudio(6000, "Barulho detectado");
      }
    };
  } catch (e) { console.warn("Erro no microfone:", e); }
}

async function recordAudio(duration, reason) {
  if (isRecording) return;
  isRecording = true;
  showToast("🎙️ Gravando...", "info");
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    const mediaRecorder = new MediaRecorder(stream);
    const audioChunks = [];
    mediaRecorder.ondataavailable = (event) => audioChunks.push(event.data);
    mediaRecorder.onstop = async () => {
      const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
      const formData = new FormData();
      formData.append('audio', audioBlob);
      fetch(serverUrl + '/api.php?action=upload_audio', { method: 'POST', body: formData })
        .then(() => {
          showToast("🎙️ Áudio enviado!", "success");
          isRecording = false;
        });
    };
    mediaRecorder.start();
    setTimeout(() => mediaRecorder.stop(), duration);
  } catch(e) { isRecording = false; }
}

// ==========================================
// TOAST & UI
// ==========================================
function showToast(msg, type = 'info') {
  const toast = document.getElementById('toast');
  toast.textContent = msg;
  toast.className = 'toast show ' + type;
  setTimeout(() => { toast.className = 'toast'; }, 3500);
}
