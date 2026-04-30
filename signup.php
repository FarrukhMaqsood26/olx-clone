<?php 
require_once 'includes/config.php';
if (isset($_SESSION['user_id'])) {
    $role = isset($_SESSION['user_role']) ? strtolower(trim($_SESSION['user_role'])) : 'user';
    if ($role === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit;
}
include 'includes/header.php'; 
include 'includes/preloader.php';
?>

<div class="flex items-center justify-center py-20 px-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-lg border border-slate-200 p-8 sm:p-10">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-slate-900 mb-2">Create an Account</h2>
            <p class="text-slate-500">Join the largest local marketplace today.</p>
        </div>
        
        <form action="api/auth.php" method="POST" class="space-y-5" enctype="multipart/form-data" id="signupForm">
            <input type="hidden" name="signup" value="1">

            <div>
                <label for="signup-name" class="block text-sm font-medium text-slate-700 mb-2">Full Name</label>
                <input type="text" name="name" id="signup-name" required placeholder="Enter your full name" 
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition text-slate-800">
            </div>

            <div>
                <label for="signup-username" class="block text-sm font-medium text-slate-700 mb-2">Username</label>
                <input type="text" name="username" id="signup-username" required placeholder="Pick a unique username" 
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition text-slate-800">
            </div>
            
            <div>
                <label for="signup-email" class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                <input type="email" name="email" id="signup-email" required placeholder="Enter your email" 
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition text-slate-800">
            </div>
            
            <div>
                <label for="signup-phone" class="block text-sm font-medium text-slate-700 mb-2">Phone Number</label>
                <input type="tel" name="phone" id="signup-phone" required placeholder="e.g. 03xx-xxxxxxx" 
                    pattern="^((\+92)|(0092)|(92)|(0))3\d{2}[- ]?\d{7}$"
                    title="Please enter a valid Pakistan number (e.g. 03451234567 or 0345-1234567)"
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition text-slate-800">
            </div>
            
            <div>
                <label for="signup-password" class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                <input type="password" name="password" id="signup-password" required minlength="6" placeholder="Create a password" 
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition text-slate-800">
            </div>
            
            <!-- Face Verification Section -->
            <div id="face-verification-container" class="mt-8 border-t border-slate-100 pt-6">
                <div class="flex items-center justify-between mb-4">
                    <label class="block text-sm font-bold text-slate-700">3D Face Verification <span class="text-red-500">*</span></label>
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

            <div class="pt-4">
                <label for="signup-avatar" class="block text-sm font-medium text-slate-700 mb-2">Profile Picture (Optional)</label>
                <input type="file" name="avatar" id="signup-avatar" accept="image/*" 
                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand/10 file:text-brand hover:file:bg-brand/20 cursor-pointer text-slate-600">
            </div>

            <button type="submit" id="signup-submit" class="w-full bg-brand hover:bg-brand-light text-white font-bold py-3.5 px-4 rounded-lg shadow-sm transition">
                Sign Up
            </button>
            
            <div class="relative flex items-center py-2">
                <div class="flex-grow border-t border-slate-200"></div>
                <span class="flex-shrink-0 mx-4 text-slate-400 text-sm font-medium uppercase tracking-wider">or sign up with</span>
                <div class="flex-grow border-t border-slate-200"></div>
            </div>
            
            <a href="api/auth.php?action=google_login" id="google-signup" class="w-full flex items-center justify-center gap-3 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold py-3 px-4 rounded-lg shadow-sm transition">
                <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/><path d="M1 1h22v22H1z" fill="none"/></svg>
                Google
            </a>
        </form>
        
        <p class="mt-8 text-center text-sm text-slate-500">
            Already have an account? <a href="login.php" class="font-bold text-brand hover:underline">Log in</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Human AI Library -->
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/human/dist/human.js" crossorigin="anonymous"></script>

<script>
$(document).ready(function() {
    const videoElement = document.getElementById('webcam');
    const canvasElement = document.getElementById('face-canvas');
    const canvasCtx = canvasElement.getContext('2d');
    const $submitBtn = $('#signup-submit');
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

    // --- PROFESSIONAL APPROACH: Human AI Library ---
    const config = {
        modelBasePath: 'https://cdn.jsdelivr.net/npm/@vladmandic/human/models',
        face: {
            enabled: true,
            detector: { rotation: true, maxDetected: 5, minConfidence: 0.4 },
            mesh: { enabled: true },
            iris: { enabled: true },
            antispoof: { enabled: false }, // Disabling built-in antispoof as it's too sensitive for low light
            liveness: { enabled: false }   // Disabling built-in liveness as it's too sensitive for low light
        },
        hand: { enabled: true, maxDetected: 1, minConfidence: 0.5 }, // NEW: Dedicated Hand Detector
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
            console.error("AI Init/Camera Error:", e);
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

    $retryBtn.on('click', function() {
        resetScan();
    });

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
    let obstructionDeBounce = 0; // De-bounce counter

    function onResults(results) {
        canvasCtx.save();
        canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
        
        // --- DE-BOUNCED OBSTRUCTION DETECTION ---
        let currentFrameObstructed = false;
        let obstructionMsg = "";

        if (results.hand && results.hand.length > 0) {
            currentFrameObstructed = true;
            obstructionMsg = "Hand detected! Scan paused.";
        }

        if (results.face && results.face.length > 1) {
            currentFrameObstructed = true;
            obstructionMsg = "Second face detected! Please ensure only one person is in the frame.";
        } else if (results.face && results.face.length > 0) {
            const face = results.face[0];
            const mesh = face.mesh;
            const upperLip = mesh[13];
            const lowerLip = mesh[14];
            const mouthOpenness = Math.abs(upperLip[1] - lowerLip[1]);
            
            // Relaxed mouth check to prevent blinking in dark/beard
            if (face.faceScore < 0.3 || (face.faceScore > 0.6 && mouthOpenness < 0.5)) { 
                currentFrameObstructed = true;
                obstructionMsg = "Face obscured! Clear your face.";
            }
        }

        // De-bounce logic: Must be obstructed for 10 frames before we actually pause
        if (currentFrameObstructed) {
            obstructionDeBounce++;
        } else {
            obstructionDeBounce = Math.max(0, obstructionDeBounce - 2); // Faster recovery
        }

        if (obstructionDeBounce > 10) {
            stabilityFrames = 0;
            $status.text("Paused").removeClass('bg-blue-500').addClass('bg-red-500 text-white');
            updateFeedback(obstructionMsg, "red");
            canvasCtx.restore();
            return; 
        }

        // Resume status if cleared
        if (isScanning && !isVerified) {
            $status.text("Scanning...").removeClass('bg-red-500 bg-slate-100').addClass('bg-blue-500 text-white');
        }

        if (!results.face || results.face.length === 0) {
            stabilityFrames = 0;
            updateFeedback("Face not detected", "red");
            canvasCtx.restore();
            return;
        }

        const face = results.face[0];
        const landmarks = face.mesh;
        const rotation = getFaceRotation(landmarks);
        
        $errorMsg.addClass('hidden');

        // --- STABILITY & SCANNING ---
        // Require the face to be "clean" and "stable" before capturing
        if (isScanning && !checkpoints.front) {
            captureFrame('front', landmarks);
            checkpoints.front = true;
            totalProgress += 20; 
        }

        handleLiveness(rotation, landmarks);

        // Feedback
        const scaleX = canvasElement.width / videoElement.videoWidth;
        const scaleY = canvasElement.height / videoElement.videoHeight;
        canvasCtx.fillStyle = '#10b981';
        canvasCtx.beginPath();
        canvasCtx.arc(landmarks[4][0] * scaleX, landmarks[4][1] * scaleY, 6, 0, 2 * Math.PI);
        canvasCtx.fill();
        canvasCtx.restore();
    }

    function captureFrame(angle, landmarks) {
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = videoElement.videoWidth;
        tempCanvas.height = videoElement.videoHeight;
        const ctx = tempCanvas.getContext('2d');
        ctx.drawImage(videoElement, 0, 0);
        const base64 = tempCanvas.toDataURL('image/jpeg', 0.8);
        $(`#scan-${angle}-image`).val(base64);
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

        // --- PREVENT INSTANT SPICKS ---
        // The user must hold the pose for 15 frames (~0.5s)
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
            stabilityFrames = 0; // Reset after capture
            if (totalProgress >= 100) completeVerification();
        }

        $feedbackOverlay.removeClass('hidden');
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
        $errorMsg.addClass('hidden');
        $(videoElement).removeClass('grayscale brightness-75').css('filter', 'none');
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
