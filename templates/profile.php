<?php
/**
 * templates/profile.php
 * Profile Downloader — nhập @username, duyệt video, tải ZIP
 */
$base      = rtrim($config['base_url'], '/');
$resolveEp = $base . '/profile/resolve';
$videosEp  = $base . '/profile/videos';
$jobEp     = $base . '/profile/download-job';
$runEp     = $base . '/profile/job-run';
$zipEp     = $base . '/profile/zip';
?>

<div class="profile-container" id="profileApp">

    <!-- ── Input ── -->
    <div class="page-title">Profile <span>Downloader</span> <i class="fa-brands fa-tiktok" style="font-size:.8em;"></i></div>
    <p class="page-subtitle">Tải hàng loạt video từ bất kỳ profile TikTok công khai</p>

    <div class="card">
        <div class="input-group">
            <input id="profileUrl" class="input-url" type="text"
                   placeholder="https://www.tiktok.com/@username hoặc @username"
                   autocomplete="off">
            <button class="btn btn-resolve" id="btnResolve">
                <i class="fa-solid fa-magnifying-glass"></i> Tìm
            </button>
        </div>
        <div id="profileInfo" style="display:none;" class="profile-info-bar">
            <img id="pAvatar" src="" alt="" class="p-avatar">
            <div>
                <div id="pNickname" class="p-name"></div>
                <div id="pStats"    class="p-stats"></div>
            </div>
        </div>
    </div>

    <!-- ── Video Grid ── -->
    <div id="videoSection" style="display:none;">
        <div class="card-header-row">
            <b id="videoCount">0 video</b>
            <div style="display:flex;gap:10px;align-items:center;">
                <label style="font-size:.8rem;color:#888;">
                    <input type="checkbox" id="checkAll"> Chọn tất cả
                </label>
                <button class="btn btn-start" id="btnDownload" disabled>
                    <i class="fa-solid fa-file-zipper"></i> Tải ZIP
                </button>
            </div>
        </div>

        <div class="video-grid" id="videoGrid"></div>

        <div style="text-align:center;margin-top:1rem;">
            <button class="btn btn-load" id="btnLoadMore" style="display:none;">
                <i class="fa-solid fa-chevron-down"></i> Tải thêm
            </button>
        </div>
    </div>

    <!-- ── Job Progress ── -->
    <div id="jobSection" style="display:none;" class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <b>📦 Đang tải về...</b>
            <span id="jobStatusBadge" class="status-badge status-running">
                <span class="pulse"></span> Đang chạy
            </span>
        </div>
        <div class="progress-bar-wrap"><div class="progress-bar" id="jobProgress"></div></div>
        <div id="jobText" style="font-size:.85rem;color:#888;margin-bottom:1rem;">0 / 0 video</div>
        <a id="btnZip" href="#" class="btn btn-start" style="display:none;text-decoration:none;">
            <i class="fa-solid fa-download"></i> Tải ZIP
        </a>
    </div>

</div>

<style>
.profile-container { max-width: 900px; margin: 0 auto; padding: 2rem 1.5rem; }
.page-title { font-size:1.8rem; font-weight:900; letter-spacing:-1px; margin-bottom:.4rem; }
.page-title span { color:#fe2c55; }
.page-subtitle { color:#888; font-size:.9rem; margin-bottom:2rem; }
.card { background:#1a1a1a; border:1px solid #2a2a2a; border-radius:16px; padding:1.5rem; margin-bottom:1.5rem; }
.input-group { display:flex; gap:10px; }
.input-url { flex:1; background:#111; border:1px solid #333; color:#fff; padding:.65rem 1rem; border-radius:10px; font-size:.95rem; font-family:inherit; outline:none; }
.input-url:focus { border-color:#555; }
.btn { padding:.6rem 1.2rem; border-radius:10px; font-weight:700; font-family:inherit; border:none; cursor:pointer; font-size:.9rem; display:inline-flex; align-items:center; gap:8px; }
.btn-resolve { background:#fff; color:#111; white-space:nowrap; }
.btn-resolve:hover { background:#eee; }
.btn-start { background:#fff; color:#111; }
.btn-start:hover { background:#eee; }
.btn-start:disabled { opacity:.4; cursor:not-allowed; }
.btn-load { background:#1a1a1a; color:#fff; border:1px solid #333; }
.btn-load:hover { background:#222; }
.profile-info-bar { display:flex; align-items:center; gap:12px; margin-top:1rem; padding-top:1rem; border-top:1px solid #222; }
.p-avatar { width:48px; height:48px; border-radius:50%; object-fit:cover; border:2px solid #333; }
.p-name { font-weight:800; font-size:1.05rem; }
.p-stats { font-size:.8rem; color:#888; margin-top:2px; }
.card-header-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; }
.video-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:12px; }
.video-card { position:relative; border-radius:12px; overflow:hidden; background:#111; border:2px solid #222; cursor:pointer; aspect-ratio:9/16; transition:border-color .15s; }
.video-card.selected { border-color:#fe2c55; }
.video-card img { width:100%; height:100%; object-fit:cover; display:block; }
.video-card .v-overlay { position:absolute; inset:0; background:linear-gradient(to top,rgba(0,0,0,.7) 40%,transparent); }
.video-card .v-check { position:absolute; top:8px; right:8px; width:20px; height:20px; border-radius:50%; background:#333; border:2px solid #555; display:flex; align-items:center; justify-content:center; font-size:.7rem; color:transparent; }
.video-card.selected .v-check { background:#fe2c55; border-color:#fe2c55; color:#fff; }
.video-card .v-stats { position:absolute; bottom:6px; left:8px; font-size:.7rem; color:#ddd; }
.progress-bar-wrap { height:6px; background:#222; border-radius:6px; overflow:hidden; margin-bottom:.5rem; }
.progress-bar { height:100%; width:0; background:linear-gradient(90deg,#fe2c55,#ff9f43); border-radius:6px; transition:width .4s; }
.status-badge { display:inline-flex; align-items:center; gap:6px; padding:.25rem .8rem; border-radius:50px; font-size:.75rem; font-weight:700; }
.status-running { background:rgba(74,222,128,.12); color:#4ade80; }
.status-done { background:rgba(59,130,246,.12); color:#60a5fa; }
.pulse { width:8px; height:8px; background:currentColor; border-radius:50%; animation:pulseDot 1.4s ease infinite; }
@keyframes pulseDot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.7)} }
@media(max-width:500px) { .video-grid { grid-template-columns:repeat(3,1fr); } }
</style>

<script>
const RESOLVE_EP = '<?php echo $resolveEp; ?>';
const VIDEOS_EP  = '<?php echo $videosEp; ?>';
const JOB_EP     = '<?php echo $jobEp; ?>';
const RUN_EP     = '<?php echo $runEp; ?>';
const ZIP_EP     = '<?php echo $zipEp; ?>';

let profile    = null;
let cursor     = 0;
let hasMore    = false;
let videos     = [];   // all loaded videos
let selectedIds = new Set();
let jobId      = null;
let jobTimer   = null;

const $url       = document.getElementById('profileUrl');
const $resolve   = document.getElementById('btnResolve');
const $pInfo     = document.getElementById('profileInfo');
const $pAvatar   = document.getElementById('pAvatar');
const $pNickname = document.getElementById('pNickname');
const $pStats    = document.getElementById('pStats');
const $vSection  = document.getElementById('videoSection');
const $vGrid     = document.getElementById('videoGrid');
const $vCount    = document.getElementById('videoCount');
const $checkAll  = document.getElementById('checkAll');
const $btnDl     = document.getElementById('btnDownload');
const $btnMore   = document.getElementById('btnLoadMore');
const $jobSec    = document.getElementById('jobSection');
const $jobProg   = document.getElementById('jobProgress');
const $jobText   = document.getElementById('jobText');
const $jobBadge  = document.getElementById('jobStatusBadge');
const $btnZip    = document.getElementById('btnZip');

// ── Resolve profile ─────────────────────────────────────────
$resolve.addEventListener('click', async () => {
    const urlVal = $url.value.trim();
    if (!urlVal) return;

    $resolve.disabled = true;
    $resolve.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    try {
        const res  = await fetch(RESOLVE_EP, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ url: urlVal })
        });
        const data = await res.json();
        if (!data.success) { alert(data.error); return; }

        profile = data.data;
        $pAvatar.src       = profile.avatar;
        $pNickname.textContent = profile.nickname + ' @' + profile.username;
        $pStats.textContent    = `${fmtNum(profile.follower_count)} followers · ${fmtNum(profile.video_count)} videos`;
        $pInfo.style.display   = 'flex';

        // Load first page
        videos = []; cursor = 0;
        $vGrid.innerHTML = '';
        $vSection.style.display = '';
        await loadVideos();
    } finally {
        $resolve.disabled = false;
        $resolve.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Tìm';
    }
});

// ── Load videos page ────────────────────────────────────────
async function loadVideos() {
    $btnMore.disabled = true;
    const res  = await fetch(VIDEOS_EP, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ sec_uid: profile.sec_uid, cursor, count: 20 })
    });
    const data = await res.json();
    if (!data.success) return;

    const page = data.data;
    cursor  = page.cursor;
    hasMore = page.has_more;
    page.videos.forEach(v => { videos.push(v); renderCard(v); });

    $vCount.textContent = videos.length + (hasMore ? '+' : '') + ' video';
    $btnMore.style.display = hasMore ? '' : 'none';
    $btnMore.disabled = false;
}

// ── Render one video card ───────────────────────────────────
function renderCard(v) {
    const card = document.createElement('div');
    card.className = 'video-card';
    card.dataset.id = v.id;
    card.innerHTML = `
        <img src="${v.cover}" loading="lazy" alt="">
        <div class="v-overlay"></div>
        <div class="v-check"><i class="fa-solid fa-check"></i></div>
        <div class="v-stats">👁 ${fmtNum(v.play_count || 0)}</div>
    `;
    card.addEventListener('click', () => toggleSelect(v.id, card));
    $vGrid.appendChild(card);
}

function toggleSelect(id, card) {
    if (selectedIds.has(id)) { selectedIds.delete(id); card.classList.remove('selected'); }
    else                     { selectedIds.add(id);    card.classList.add('selected'); }
    $btnDl.disabled = selectedIds.size === 0;
    $checkAll.checked = selectedIds.size === videos.length;
}

$checkAll.addEventListener('change', () => {
    document.querySelectorAll('.video-card').forEach(card => {
        const id = card.dataset.id;
        if ($checkAll.checked) { selectedIds.add(id); card.classList.add('selected'); }
        else                   { selectedIds.delete(id); card.classList.remove('selected'); }
    });
    $btnDl.disabled = selectedIds.size === 0;
});

$btnMore.addEventListener('click', loadVideos);

// ── Create download job ─────────────────────────────────────
document.getElementById('btnDownload').addEventListener('click', async () => {
    if (!selectedIds.size) return;
    $btnDl.disabled = true;

    const res  = await fetch(JOB_EP, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            uid: profile.uid,
            username: profile.username,
            video_ids: [...selectedIds]
        })
    });
    const data = await res.json();
    if (!data.success) { alert(data.error); $btnDl.disabled = false; return; }

    jobId = data.job_id;
    $jobSec.style.display = '';
    runJobLoop();
});

// ── Job polling loop ────────────────────────────────────────
async function runJobLoop() {
    const res  = await fetch(RUN_EP, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ job_id: jobId })
    });
    const data = await res.json();
    if (!data.success) return;

    const job = data.job;
    const pct = job.total > 0 ? Math.round((job.done + job.failed) / job.total * 100) : 0;
    $jobProg.style.width = pct + '%';
    $jobText.textContent = `${job.done} / ${job.total} video · ${job.failed} lỗi`;

    if (job.status === 'done') {
        $jobBadge.className = 'status-badge status-done';
        $jobBadge.innerHTML = '<i class="fa-solid fa-check"></i> Hoàn thành';
        $btnZip.href = ZIP_EP + '?id=' + jobId;
        $btnZip.style.display = '';
    } else {
        setTimeout(runJobLoop, 1500);
    }
}

function fmtNum(n) {
    if (n >= 1e6) return (n/1e6).toFixed(1) + 'M';
    if (n >= 1e3) return (n/1e3).toFixed(1) + 'K';
    return String(n);
}
</script>
