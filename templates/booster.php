<?php
/**
 * templates/booster.php
 * ---------------------------------------------------------------
 * Content template cho trang Boost View.
 * Header/footer do router (public/index.php) xử lý.
 *
 * Router inject trước khi include:
 *   $config, $pageTitle, $activePage, $extraHead
 * ---------------------------------------------------------------
 */

use App\Core\EnvLoader;
EnvLoader::load(__DIR__ . '/../.env');
$apiKey          = EnvLoader::get('API_KEY', '');
$base            = rtrim($config['base_url'], '/');
$resolveEndpoint = $base . '/boost/resolve';
$runEndpoint     = $base . '/boost/run';
?>

<div class="boost-container">
    <div class="page-title">Boost <span>Tương Tác</span> TikTok <i class="fa-solid fa-rocket" style="font-size:0.8em;"></i></div>

    <p class="page-subtitle">Tăng View · Like · Share · Download · Adaptive speed · Real-time tracking</p>

    <!-- Input card -->
    <div class="card">
        <div class="input-group">
            <input id="tiktokUrl" class="input-url" type="text"
                   placeholder="Dán link TikTok vào đây... (video hoặc ảnh)"
                   autocomplete="off">
            <button class="btn btn-resolve" id="btnResolve">
                <i class="fa-solid fa-magnifying-glass"></i> Lấy ID
            </button>
        </div>

        <div id="vidInfo" style="margin-bottom:1.2rem; display:none;">
            <span class="vid-badge">
                <i class="fa-solid fa-circle-check"></i>
                Video ID: <b id="vidIdDisplay">—</b>
            </span>
        </div>

        <div class="settings-row">
            <div class="setting-item">
                <label>Loại tương tác</label>
                <select id="actionType" class="setting-input" style="width:130px;">
                    <option value="view">👁 View</option>
                    <option value="like">❤️ Like</option>
                    <option value="share">🔗 Share</option>
                    <option value="download">⬇️ Download</option>
                </select>
            </div>
            <div class="setting-item">
                <label>Batch Size (mỗi đợt)</label>
                <input id="batchSize" class="setting-input" type="number" value="100" min="10" max="500">
            </div>
            <div class="setting-item">
                <label>Số đợt tối đa</label>
                <input id="maxBatch" class="setting-input" type="number" value="999" min="1" max="9999">
            </div>
            <div style="margin-top:auto; display:flex; gap:10px;">
                <button class="btn btn-start" id="btnStart" disabled>
                    <i class="fa-solid fa-play"></i> Bắt đầu Boost
                </button>
                <button class="btn btn-stop" id="btnStop" style="display:none;">
                    <i class="fa-solid fa-stop"></i> Dừng
                </button>
            </div>
        </div>
    </div>

    <!-- Stats dashboard -->
    <div class="card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:gap(8px);">
            <div style="font-size:1.1rem; font-weight:800;">📊 Thống kê Real-time</div>
            <span class="status-badge status-idle" id="statusBadge">
                <span class="pulse"></span> Chờ
            </span>
        </div>

        <!-- Progress -->
        <div class="progress-wrap"><div class="progress-bar" id="progressBar"></div></div>
        <div style="font-size:0.75rem; color:var(--muted); margin-bottom:1.5rem;">
            Đợt <b id="batchDone">0</b> / <b id="batchTotal">999</b>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👁️</div>
                <div class="stat-value text-accent" id="statViews">0</div>
                <div class="stat-label">Views Đã Gửi</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-value text-success" id="statSuccess">0</div>
                <div class="stat-label">Thành Công</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">❌</div>
                <div class="stat-value text-danger" id="statFailed">0</div>
                <div class="stat-label">Thất Bại</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🚀</div>
                <div class="stat-value text-cyan" id="statVps">0</div>
                <div class="stat-label">View/Giây</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⚡</div>
                <div class="stat-value text-warning" id="statVpm">0</div>
                <div class="stat-label">View/Phút</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🏆</div>
                <div class="stat-value" id="statPeak">0</div>
                <div class="stat-label">Peak v/s</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-value" id="statRate">0%</div>
                <div class="stat-label">Tỷ Lệ OK</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏱️</div>
                <div class="stat-value" id="statTime">0s</div>
                <div class="stat-label">Thời Gian</div>
            </div>
        </div>

        <!-- Log -->
        <div style="font-size:0.75rem; color:var(--muted); font-weight:600; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px;">Log</div>
        <div class="log-box" id="logBox">
            <p>Chờ bạn bắt đầu chiến dịch...</p>
        </div>
    </div>
</div><!-- /.boost-container -->

<script>
const RESOLVE_ENDPOINT = '<?php echo $resolveEndpoint; ?>';
const RUN_ENDPOINT     = '<?php echo $runEndpoint; ?>';
const API_KEY          = '<?php echo htmlspecialchars($apiKey, ENT_QUOTES); ?>';


let videoId   = null;
let isRunning = false;
let batchDone = 0;
let maxBatch  = 999;
let globalStats = { success: 0, failed: 0, vps: 0, vpm: 0, peak_vps: 0, success_rate: 0, elapsed: 0 };

// ── DOM refs ────────────────────────────────────────────────────────────────
const $url        = document.getElementById('tiktokUrl');
const $resolve    = document.getElementById('btnResolve');
const $start      = document.getElementById('btnStart');
const $stop       = document.getElementById('btnStop');
const $batchSize  = document.getElementById('batchSize');
const $maxBatch   = document.getElementById('maxBatch');
const $actionType = document.getElementById('actionType');
const $vidInfo    = document.getElementById('vidInfo');
const $vidId      = document.getElementById('vidIdDisplay');
const $progress   = document.getElementById('progressBar');
const $batchDone  = document.getElementById('batchDone');
const $batchTot   = document.getElementById('batchTotal');
const $status     = document.getElementById('statusBadge');
const $log        = document.getElementById('logBox');

const fields = {
    views   : document.getElementById('statViews'),
    success : document.getElementById('statSuccess'),
    failed  : document.getElementById('statFailed'),
    vps     : document.getElementById('statVps'),
    vpm     : document.getElementById('statVpm'),
    peak    : document.getElementById('statPeak'),
    rate    : document.getElementById('statRate'),
    time    : document.getElementById('statTime'),
};

// ── Log helper ───────────────────────────────────────────────────────────────
function log(msg, type = '') {
    const p = document.createElement('p');
    p.className = type ? 'log-' + type : '';
    p.textContent = '[' + new Date().toLocaleTimeString() + '] ' + msg;
    $log.appendChild(p);
    $log.scrollTop = $log.scrollHeight;
}

// ── Status badge ─────────────────────────────────────────────────────────────
function setStatus(label, cls) {
    $status.textContent = '';
    const pulse = document.createElement('span');
    pulse.className = 'pulse';
    $status.appendChild(pulse);
    $status.append(' ' + label);
    $status.className = 'status-badge ' + cls;
}

// ── Update stats UI ───────────────────────────────────────────────────────────
function updateUI(stats) {
    // Accumulate globally
    globalStats.success      = stats.success;
    globalStats.failed       = stats.failed;
    globalStats.vps          = stats.vps;
    globalStats.vpm          = stats.vpm;
    globalStats.peak_vps     = stats.peak_vps;
    globalStats.success_rate = stats.success_rate;
    globalStats.elapsed      = stats.elapsed;

    fields.views.textContent   = stats.success.toLocaleString();
    fields.success.textContent = stats.success.toLocaleString();
    fields.failed.textContent  = stats.failed.toLocaleString();
    fields.vps.textContent     = stats.vps.toFixed(1);
    fields.vpm.textContent     = Math.round(stats.vpm).toLocaleString();
    fields.peak.textContent    = stats.peak_vps.toFixed(1);
    fields.rate.textContent    = stats.success_rate.toFixed(1) + '%';
    fields.time.textContent    = stats.elapsed + 's';

    // Coloring rate
    fields.rate.className = stats.success_rate >= 70 ? 'stat-value text-success'
                          : stats.success_rate >= 40 ? 'stat-value text-warning'
                          : 'stat-value text-danger';

    // Batch progress
    maxBatch = parseInt($maxBatch.value) || 999;
    $batchTot.textContent  = maxBatch;
    $batchDone.textContent = batchDone;
    const pct = Math.min((batchDone / maxBatch) * 100, 100);
    $progress.style.width = pct + '%';
}

// ── Resolve Video ID ──────────────────────────────────────────────────────────
$resolve.addEventListener('click', async () => {
    const url = $url.value.trim();
    if (!url) { log('Hãy nhập URL TikTok!', 'warn'); return; }

    $resolve.disabled = true;
    $resolve.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Đang lấy ID...';
    log('Đang phân tích URL: ' + url);

    try {
        const res  = await fetch(RESOLVE_ENDPOINT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Api-Key': API_KEY },
            body: JSON.stringify({ url })
        });
        const data = await res.json();
        if (data.success) {
            videoId = data.video_id;
            $vidId.textContent = videoId;
            $vidInfo.style.display = 'block';
            $start.disabled = false;
            log('✅ Video ID: ' + videoId, 'ok');
        } else {
            log('❌ ' + (data.error || 'Không lấy được Video ID'), 'err');
        }
    } catch (e) {
        log('❌ Network error: ' + e.message, 'err');
    } finally {
        $resolve.disabled = false;
        $resolve.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Lấy ID';
    }
});

// ── Start boost loop ──────────────────────────────────────────────────────────
$start.addEventListener('click', () => {
    if (!videoId) { log('Hãy lấy Video ID trước!', 'warn'); return; }
    maxBatch  = parseInt($maxBatch.value) || 999;
    batchDone = 0;
    isRunning = true;

    $start.style.display = 'none';
    $stop.style.display  = '';
    setStatus('Đang chạy', 'status-running');
    const actionLabel = $actionType.options[$actionType.selectedIndex].text;
    log(`🚀 Bắt đầu boost ${actionLabel}! Batch: ${$batchSize.value} · Tổng: ${maxBatch} đợt`, 'ok');

    runLoop();
});

$stop.addEventListener('click', () => {
    isRunning = false;
    $start.style.display = '';
    $stop.style.display  = 'none';
    setStatus('Đã dừng', 'status-idle');
    log('⛔ Đã dừng chiến dịch sau ' + batchDone + ' đợt.', 'warn');
});

// ── Main async loop ───────────────────────────────────────────────────────────
async function runLoop() {
    while (isRunning && batchDone < maxBatch) {
        try {
            const res = await fetch(RUN_ENDPOINT, {
                method : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Api-Key': API_KEY },
                body   : JSON.stringify({
                    video_id   : videoId,
                    action     : document.getElementById('actionType').value,
                    batch_size : parseInt($batchSize.value) || 100,
                })
            });
            const data = await res.json();

            if (data.success) {
                batchDone++;
                updateUI(data.stats);
                const rate = data.stats.success_rate;
                if (batchDone % 10 === 0) {
                    const act = data.action || 'view';
                    log(`📊 Đợt #${batchDone} [${act}] · OK: ${data.stats.success.toLocaleString()} · ${data.stats.vps}v/s · Rate: ${data.stats.success_rate}%`,
                        rate >= 70 ? 'ok' : rate >= 40 ? 'warn' : 'err');
                }
            } else {
                log('⚠️ Batch error: ' + (data.error || 'Unknown'), 'err');
                await sleep(2000); // Nghỉ khi gặp lỗi
            }
        } catch (e) {
            log('❌ Request failed: ' + e.message, 'err');
            await sleep(3000);
        }
    }

    if (batchDone >= maxBatch) {
        isRunning = false;
        $start.style.display = '';
        $stop.style.display  = 'none';
        setStatus('Hoàn tất', 'status-done');
        log(`🎉 Hoàn tất! Tổng view gửi thành công: ${globalStats.success.toLocaleString()}`, 'ok');
    }
}

function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }
</script>
