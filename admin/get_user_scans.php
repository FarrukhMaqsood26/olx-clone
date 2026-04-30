<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('<p class="text-red-500">Unauthorized</p>');
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$stmt = $pdo->prepare("SELECT * FROM user_face_scans WHERE user_id = ? ORDER BY FIELD(capture_angle, 'front', 'left', 'right', 'up', 'down')");
$stmt->execute([$user_id]);
$scans = $stmt->fetchAll();

if (empty($scans)) {
    echo '<div class="col-span-full py-20 text-center text-slate-400">
            <i class="fas fa-folder-open text-4xl mb-4 opacity-20"></i>
            <p>No biometric scan data found for this user.</p>
          </div>';
    exit;
}

foreach ($scans as $scan): ?>
    <div class="bg-slate-50 border border-slate-100 rounded-2xl overflow-hidden shadow-sm group hover:shadow-md transition">
        <div class="aspect-square bg-slate-200 relative overflow-hidden">
            <img src="../uploads/face-scans/<?= htmlspecialchars($scan['image_path']) ?>" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-brand/10 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                <span class="bg-white/90 backdrop-blur px-3 py-1.5 rounded-full text-[10px] font-bold text-brand uppercase tracking-widest shadow-sm"><?= $scan['capture_angle'] ?> View</span>
            </div>
        </div>
        <div class="p-4 border-t border-slate-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest"><?= $scan['capture_angle'] ?> Scan</span>
                <span class="text-[10px] font-bold text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-full">3D Liveness Verified</span>
            </div>
            <p class="text-[11px] text-slate-500 line-clamp-1 italic"><?= date('M d, Y - H:i', strtotime($scan['created_at'])) ?></p>
            
            <?php if ($scan['mesh_landmarks_json']): ?>
                <div class="mt-3 pt-3 border-t border-slate-100">
                    <button onclick="toggleLandmarks(<?= $scan['id'] ?>)" class="text-[10px] font-bold text-brand hover:underline flex items-center gap-1">
                        <i class="fas fa-project-diagram"></i> View 468 Landmarks JSON
                    </button>
                    <div id="landmarks-<?= $scan['id'] ?>" class="hidden mt-3 p-3 bg-slate-900 rounded-xl overflow-x-auto relative group/json">
                        <button onclick="copyToClipboard('landmarks-pre-<?= $scan['id'] ?>')" class="absolute top-2 right-2 p-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white opacity-0 group-hover/json:opacity-100 transition shadow-sm" title="Copy JSON">
                            <i class="far fa-copy"></i>
                        </button>
                        <pre id="landmarks-pre-<?= $scan['id'] ?>" class="text-[9px] text-emerald-400 font-mono leading-tight max-h-40 overflow-y-auto"><?= htmlspecialchars(json_encode(json_decode($scan['mesh_landmarks_json']), JSON_PRETTY_PRINT)) ?></pre>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
