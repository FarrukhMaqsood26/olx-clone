<?php 
require_once 'includes/config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if already verified
$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_face_scans WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
if ($stmt->fetchColumn() > 0) {
    header("Location: index.php?success=verified");
    exit;
}

include 'includes/header.php'; 
?>

<div class="flex items-center justify-center py-20 px-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-lg border border-slate-200 p-8 sm:p-10">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-slate-900 mb-2">3D Face Identity</h2>
            <p class="text-slate-500">Please complete your biometric verification to continue.</p>
        </div>
        
        <form action="api/auth.php" method="POST" class="space-y-5" enctype="multipart/form-data" id="faceVerifyForm">
            <input type="hidden" name="complete_face_verification" value="1">
            <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">

            <!-- Face Verification Section -->
            <div id="face-verification-container">
                <div class="flex items-center justify-between mb-4">
                    <label class="block text-sm font-bold text-slate-700">Scan Process <span class="text-red-500">*</span></label>
                    <span id="verification-status" class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-100 text-slate-400">Not Started</span>
                </div>
                
                <div class="relative rounded-2xl bg-slate-900 overflow-hidden aspect-video shadow-inner group">
                    <video id="webcam" autoplay playsinline class="w-full h-full object-cover grayscale brightness-75 transition-all duration-700"></video>
                    <canvas id="face-canvas" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>
                    
                    <div id="face-feedback" class="absolute inset-0 flex flex-col items-center justify-center bg-black/40 text-white p-6 text-center transition-opacity duration-300">
                        <div class="w-16 h-16 border-4 border-white/20 border-t-white rounded-full animate-spin mb-4" id="loading-spinner"></div>
                        <p id="feedback-text" class="text-sm font-medium tracking-tight">Initializing AI Models...</p>
                        
                        <button type="button" id="start-scan-btn" class="mt-4 bg-brand hover:bg-brand-light text-white font-bold py-2 px-6 rounded-full shadow-lg transition transform active:scale-95 flex items-center gap-2">
                            <i class="fas fa-play"></i> Start Scan
                        </button>
                    </div>

                    <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-white/10 overflow-hidden">
                        <div id="scan-progress-bar" class="h-full bg-brand w-0 transition-all duration-300"></div>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between gap-4">
                    <div class="grid grid-cols-4 gap-2 flex-grow" id="liveness-checkpoints">
                        <div class="checkpoint bg-slate-100 rounded-lg p-2 text-center text-[9px] font-bold text-slate-400 uppercase transition" data-target="left">Left</div>
                        <div class="checkpoint bg-slate-100 rounded-lg p-2 text-center text-[9px] font-bold text-slate-400 uppercase transition" data-target="right">Right</div>
                        <div class="checkpoint bg-slate-100 rounded-lg p-2 text-center text-[9px] font-bold text-slate-400 uppercase transition" data-target="up">Up</div>
                        <div class="checkpoint bg-slate-100 rounded-lg p-2 text-center text-[9px] font-bold text-slate-400 uppercase transition" data-target="down">Down</div>
                    </div>
                    <button type="button" id="retry-scan-btn" class="shrink-0 w-10 h-10 rounded-full bg-slate-100 text-slate-400 hover:bg-red-50 hover:text-red-500 transition flex items-center justify-center shadow-sm" title="Retry Scan">
                        <i class="fas fa-redo-alt"></i>
                    </button>
                </div>

                <p id="face-error-msg" class="text-[11px] text-red-500 mt-3 font-medium hidden flex items-center gap-1">
                    <i class="fas fa-exclamation-triangle"></i> <span></span>
                </p>
                
                <input type="hidden" name="face_verified" id="face-verified-input" value="0">
                
                <div id="scan-data-storage">
                    <input type="hidden" name="scan_front_image" id="scan-front-image">
                    <input type="hidden" name="scan_left_image" id="scan-left-image">
                    <input type="hidden" name="scan_right_image" id="scan-right-image">
                    <input type="hidden" name="scan_up_image" id="scan-up-image">
                    <input type="hidden" name="scan_down_image" id="scan-down-image">
                    <input type="hidden" name="scan_landmarks" id="scan-landmarks-json">
                </div>
            </div>

            <button type="submit" id="face-submit" disabled class="w-full bg-brand hover:bg-brand-light text-white font-bold py-3.5 px-4 rounded-lg shadow-sm transition opacity-50 cursor-not-allowed">
                Complete Verification
            </button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/@vladmandic/human/dist/human.js" crossorigin="anonymous"></script>

<script>
$(document).ready(function() {
    const videoElement = document.getElementById('webcam');
    const canvasElement = document.getElementById('face-canvas');
    const canvasCtx = canvasElement.getContext('2d');
    const $submitBtn = $('#face-submit');
    const $status = $('#verification-status');
    const $feedback = $('#feedback-text');
    const $feedbackOverlay = $('#face-feedback');
    const $progressBar = $('#scan-progress-bar');
    const $errorMsg = $('#face-error-msg');
    
    const $startBtn = $('#start-scan-btn');
    const $retryBtn = $('#retry-scan-btn');
    
    let isVerified = false;
    let isScanning = false;
    let checkpoints = { front: false, left: false, right: false, up: false, down: false };
    let totalProgress = 0;
    let allLandmarks = {};

    const config = {
        modelBasePath: 'https://cdn.jsdelivr.net/npm/@vladmandic/human/models',
        face: {
            enabled: true,
            detector: { rotation: true, maxDetected: 1, minConfidence: 0.4 },
            mesh: { enabled: true }
        },
        hand: { enabled: true, maxDetected: 1, minConfidence: 0.5 },
        body: { enabled: false },
        object: { enabled: false },
        segmentation: { enabled: false }
    };

    const human = new Human.Human(config);

    async function initHuman() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { width: 640, height: 480, facingMode: 'user' } 
            });
            videoElement.srcObject = stream;
            await human.load();
            await human.warmup();
            $('#loading-spinner').addClass('hidden');
            $feedback.text('Ready to start?');
        } catch (e) {
            $feedback.text('Camera access failed.');
        }
    }

    initHuman();

    async function detectLoop() {
        if (!isScanning || isVerified) return;
        const result = await human.detect(videoElement);
        onResults(result);
        requestAnimationFrame(detectLoop);
    }

    $startBtn.on('click', function() {
        isScanning = true;
        $(this).fadeOut();
        $status.text("Scanning...").removeClass('bg-slate-100').addClass('bg-blue-500 text-white');
        $feedback.text('Position your face...');
        $(videoElement).removeClass('grayscale brightness-75');
        detectLoop();
    });

    $retryBtn.on('click', resetScan);

    function resetScan() {
        isVerified = false;
        isScanning = false;
        totalProgress = 0;
        checkpoints = { front: false, left: false, right: false, up: false, down: false };
        $('.checkpoint').removeClass('bg-emerald-500 text-white shadow-sm scale-105').addClass('bg-slate-100 text-slate-400');
        $progressBar.css('width', '0%');
        $status.text("Not Started").removeClass('bg-emerald-500 bg-blue-500 text-white').addClass('bg-slate-100 text-slate-400');
        $feedbackOverlay.fadeIn();
        $startBtn.fadeIn();
        $feedback.text('Ready to start?');
        $submitBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
        $('#face-verified-input').val('0');
        $(videoElement).addClass('grayscale brightness-75');
    }

    let stabilityFrames = 0;
    const STABILITY_THRESHOLD = 15;
    let obstructionDeBounce = 0;

    function onResults(results) {
        let currentFrameObstructed = false;
        let obstructionMsg = "";

        if (results.hand && results.hand.length > 0) {
            currentFrameObstructed = true;
            obstructionMsg = "Hand detected! Scan paused.";
        }

        if (results.face && results.face.length > 1) {
            currentFrameObstructed = true;
            obstructionMsg = "Multiple faces detected!";
        } else if (results.face && results.face.length > 0) {
            const face = results.face[0];
            if (face.faceScore < 0.3) { 
                currentFrameObstructed = true;
                obstructionMsg = "Face obscured!";
            }
        }

        if (currentFrameObstructed) {
            obstructionDeBounce++;
        } else {
            obstructionDeBounce = Math.max(0, obstructionDeBounce - 2);
        }

        if (obstructionDeBounce > 10) {
            stabilityFrames = 0;
            $status.text("Paused").removeClass('bg-blue-500').addClass('bg-red-500 text-white');
            updateFeedback(obstructionMsg, "red");
            return; 
        }

        if (isScanning && !isVerified) {
            $status.text("Scanning...").removeClass('bg-red-500 bg-slate-100').addClass('bg-blue-500 text-white');
        }

        if (!results.face || results.face.length === 0) {
            stabilityFrames = 0;
            updateFeedback("Face not detected", "red");
            return;
        }

        const face = results.face[0];
        const landmarks = face.mesh;
        const rotation = getFaceRotation(landmarks);
        $errorMsg.addClass('hidden');

        if (isScanning && !checkpoints.front) {
            captureFrame('front', landmarks);
            checkpoints.front = true;
            totalProgress += 20; 
        }

        handleLiveness(rotation, landmarks);
    }

    function captureFrame(angle, landmarks) {
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = videoElement.videoWidth;
        tempCanvas.height = videoElement.videoHeight;
        const ctx = tempCanvas.getContext('2d');
        ctx.drawImage(videoElement, 0, 0);
        $(`#scan-${angle}-image`).val(tempCanvas.toDataURL('image/jpeg', 0.8));
        allLandmarks[angle] = landmarks;
        $('#scan-landmarks-json').val(JSON.stringify(allLandmarks));
    }

    function getFaceRotation(landmarks) {
        const nose = landmarks[4];
        const leftEye = landmarks[33];
        const rightEye = landmarks[263];
        const eyeDist = Math.abs(leftEye[0] - rightEye[0]);
        const yaw = (nose[0] - (leftEye[0] + rightEye[0]) / 2) / eyeDist * 10;
        const pitch = (nose[1] - (leftEye[1] + rightEye[1]) / 2) / eyeDist * 10;
        return { yaw, pitch };
    }

    let lastDetectedAction = null;
    function handleLiveness(rot, landmarks) {
        if (isVerified || !isScanning) return;

        let currentAction = null;
        if (rot.yaw < -0.8) currentAction = 'right';
        else if (rot.yaw > 0.8) currentAction = 'left';
        else if (rot.pitch < -0.4) currentAction = 'up';
        else if (rot.pitch > 1.3) currentAction = 'down';

        if (currentAction && currentAction === lastDetectedAction) {
            stabilityFrames++;
        } else {
            stabilityFrames = 0;
            lastDetectedAction = currentAction;
        }

        if (currentAction && !checkpoints[currentAction] && stabilityFrames >= STABILITY_THRESHOLD) {
            captureFrame(currentAction, landmarks);
            checkpoints[currentAction] = true;
            $(`.checkpoint[data-target="${currentAction}"]`).removeClass('bg-slate-100 text-slate-400').addClass('bg-emerald-500 text-white shadow-sm scale-105');
            totalProgress += 20;
            $progressBar.css('width', totalProgress + '%');
            stabilityFrames = 0;
            if (totalProgress >= 100) completeVerification();
        }

        if (!checkpoints.front) $feedback.text("Look directly at the CAMERA");
        else if (!checkpoints.left) $feedback.text("Turn slowly to your LEFT");
        else if (!checkpoints.right) $feedback.text("Turn slowly to your RIGHT");
        else if (!checkpoints.up) $feedback.text("Slowly look UP");
        else if (!checkpoints.down) $feedback.text("Slowly look DOWN");
        else $feedback.text("Verification Complete!");
    }

    function completeVerification() {
        isVerified = true;
        $status.text("Verified").removeClass('bg-slate-100 text-slate-400 bg-red-500 bg-blue-500').addClass('bg-emerald-500 text-white');
        $feedbackOverlay.fadeOut();
        $('#face-verified-input').val('1');
        $submitBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
    }

    function updateFeedback(msg, color) {
        if (isVerified) return;
        $errorMsg.find('span').text(msg);
        $errorMsg.removeClass('hidden');
    }
});
</script>
