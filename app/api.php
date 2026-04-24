<?php
/**
 * api.php — Backend da API REST para o Monitor de Energia
 * Recebe dados do celular e fornece ao painel remoto.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ==========================================
// DATA STORAGE (JSON files)
// ==========================================
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$devicesFile  = $dataDir . '/devices.json';
$eventsFile   = $dataDir . '/events.json';
$snapshotFile = $dataDir . '/snapshot.jpg';
$cameraReqFile = $dataDir . '/camera_request.json';

function readJson($file, $default = []) {
    if (!file_exists($file)) return $default;
    $content = file_get_contents($file);
    $decoded = json_decode($content, true);
    return $decoded !== null ? $decoded : $default;
}

function writeJson($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ==========================================
// ACTIONS
// ==========================================
$action = $_GET['action'] ?? '';

switch ($action) {

    // ---- PING (test connection) ----
    case 'ping':
        echo json_encode(['ok' => true, 'time' => date('c')]);
        break;

    // ---- MOBILE updates its status ----
    case 'update':
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!$data) { echo json_encode(['ok' => false, 'error' => 'Invalid JSON']); break; }

        $sessionId = $data['sessionId'] ?? 'unknown_' . time();
        $timestamp = $data['timestamp'] ?? date('c');
        $charging = $data['charging'] ?? null;
        $level = $data['level'] ?? null;
        $event = $data['event'] ?? 'update';
        $message = $data['message'] ?? null;

        // Update device registry
        $devices = readJson($devicesFile, []);
        $devices[$sessionId] = [
            'sessionId' => $sessionId,
            'charging' => $charging,
            'level' => $level,
            'timestamp' => $timestamp,
            'last_event' => $event,
        ];
        writeJson($devicesFile, $devices);

        // Record significant events
        if (in_array($event, ['power_outage', 'charging_start', 'app_start', 'reconnect'])) {
            $events = readJson($eventsFile, []);
            $eventEntry = [
                'id' => $sessionId . '_' . time() . '_' . rand(1000, 9999),
                'sessionId' => $sessionId,
                'event' => $event,
                'type' => ($event === 'power_outage') ? 'discharging' : 'charging',
                'message' => $message ?? eventToMessage($event, $charging, $level),
                'charging' => $charging,
                'level' => $level,
                'battery' => $level,
                'time' => $timestamp,
                'timestamp' => $timestamp,
                'date' => date('Y-m-d'),
            ];
            array_unshift($events, $eventEntry);
            // Keep last 500 events
            if (count($events) > 500) $events = array_slice($events, 0, 500);
            writeJson($eventsFile, $events);
        }

        echo json_encode(['ok' => true]);
        break;

    // ---- DASHBOARD reads status ----
    case 'status':
        $devices = readJson($devicesFile, []);
        $events  = readJson($eventsFile,  []);

        // Count outages today
        $today = date('Y-m-d');
        $outages_today = count(array_filter($events, function($e) use ($today) {
            return ($e['event'] ?? '') === 'power_outage' && ($e['date'] ?? '') === $today;
        }));

        // Return last 100 events
        $recentEvents = array_slice($events, 0, 100);

        echo json_encode([
            'ok' => true,
            'devices' => $devices,
            'events' => $recentEvents,
            'outages_today' => $outages_today,
            'server_time' => date('c'),
        ]);
        break;

    // ---- DASHBOARD requests camera activation on mobile ----
    case 'request_camera':
        writeJson($cameraReqFile, [
            'requested' => true,
            'timestamp' => date('c'),
        ]);
        echo json_encode(['ok' => true, 'message' => 'Camera request sent']);
        break;

    case 'check_camera_request':
        $cam = readJson($dataDir . '/signal_request_camera.json', ['requested' => false]);
        $aud = readJson($dataDir . '/signal_request_audio.json', ['requested' => false]);
        
        $res = ['ok' => true, 'requested' => false, 'audio_requested' => false];
        
        if ($cam['requested'] ?? false) {
            $res['requested'] = true;
            writeJson($dataDir . '/signal_request_camera.json', ['requested' => false]);
        }
        if ($aud['requested'] ?? false) {
            $res['audio_requested'] = true;
            writeJson($dataDir . '/signal_request_audio.json', ['requested' => false]);
        }
        echo json_encode($res);
        break;

    case 'upload_audio':
        if (isset($_FILES['audio'])) {
            $audioFile = $dataDir . '/audio_record.webm';
            if (move_uploaded_file($_FILES['audio']['tmp_name'], $audioFile)) {
                echo json_encode(['ok' => true, 'url' => 'data/audio_record.webm']);
            } else {
                echo json_encode(['ok' => false, 'error' => 'Failed to save file']);
            }
        } else {
             echo json_encode(['ok' => false, 'error' => 'No audio file received']);
        }
        break;

    case 'log':
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if ($data) {
            $events = readJson($eventsFile, []);
            $data['id'] = uniqid();
            $data['timestamp'] = date('c');
            $data['date'] = date('Y-m-d');
            array_unshift($events, $data);
            writeJson($eventsFile, array_slice($events, 0, 500));
            echo json_encode(['ok' => true]);
        }
        break;

    // ---- MOBILE uploads snapshot ----
    case 'upload_snapshot':
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!$data || !isset($data['image'])) {
            echo json_encode(['ok' => false, 'error' => 'No image data']);
            break;
        }

        // data:image/jpeg;base64,...
        $imageData = $data['image'];
        if (preg_match('/^data:image\/\w+;base64,/', $imageData)) {
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
        }
        $imageData = base64_decode($imageData);

        if ($imageData === false) {
            echo json_encode(['ok' => false, 'error' => 'Invalid base64']);
            break;
        }

        file_put_contents($snapshotFile, $imageData);
        writeJson($dataDir . '/snapshot_meta.json', [
            'timestamp' => date('c'),
            'sessionId' => $data['sessionId'] ?? 'unknown',
        ]);

        echo json_encode(['ok' => true]);
        break;

    // ---- DASHBOARD gets snapshot ----
    case 'get_snapshot':
        if (!file_exists($snapshotFile)) {
            echo json_encode(['ok' => false, 'snapshot' => null, 'message' => 'No snapshot yet']);
            break;
        }
        $imageData = file_get_contents($snapshotFile);
        $base64 = 'data:image/jpeg;base64,' . base64_encode($imageData);
        $meta = readJson($dataDir . '/snapshot_meta.json', []);
        echo json_encode([
            'ok' => true,
            'snapshot' => $base64,
            'timestamp' => $meta['timestamp'] ?? null,
        ]);
        break;

    // ---- WEBRTC SIGNALING ----
    case 'push_signal':
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        $type = $_GET['type'] ?? 'unknown';
        if ($data) {
            writeJson($dataDir . "/signal_{$type}.json", [
                'data' => $data,
                'timestamp' => time()
            ]);
            echo json_encode(['ok' => true]);
        }
        break;

    case 'pull_signal':
        $type = $_GET['type'] ?? 'unknown';
        $file = $dataDir . "/signal_{$type}.json";
        if (file_exists($file)) {
            $signal = readJson($file);
            // Se o sinal tiver mais de 30 segundos, ignorar (velho)
            if (time() - $signal['timestamp'] < 30) {
                echo json_encode(['ok' => true, 'data' => $signal['data']]);
                // Opcional: deletar após ler para não repetir
                // unlink($file);
                break;
            }
        }
        echo json_encode(['ok' => false]);
        break;

    // ---- GET ALL EVENTS ----
    case 'events':
        $events = readJson($eventsFile, []);
        $limit = intval($_GET['limit'] ?? 100);
        echo json_encode([
            'ok' => true,
            'events' => array_slice($events, 0, $limit),
            'total' => count($events),
        ]);
        break;

    // ---- CLEAR EVENTS ----
    case 'clear_events':
        writeJson($eventsFile, []);
        echo json_encode(['ok' => true]);
        break;

    default:
        echo json_encode(['ok' => false, 'error' => 'Unknown action: ' . $action]);
        break;
}

// ==========================================
// HELPERS
// ==========================================
function eventToMessage($event, $charging, $level) {
    $lvl = $level !== null ? " (bateria: {$level}%)" : '';
    switch ($event) {
        case 'power_outage':    return "🔴 QUEDA DE ENERGIA detectada!{$lvl}";
        case 'charging_start':  return "⚡ Energia voltou — carregamento retomado{$lvl}";
        case 'app_start':       return "📱 App iniciado" . ($charging ? " — energia OK{$lvl}" : " — SEM ENERGIA{$lvl}");
        case 'reconnect':       return "🔗 Celular reconectado ao servidor{$lvl}";
        case 'heartbeat':       return "💓 Heartbeat{$lvl}";
        default:                return "Evento: {$event}{$lvl}";
    }
}
?>
