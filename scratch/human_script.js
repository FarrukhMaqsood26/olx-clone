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
            detector: { rotation: true, maxDetected: 1, minConfidence: 0.4 },
            mesh: { enabled: true },
            iris: { enabled: true },
            antispoof: { enabled: true },
            liveness: { enabled: true }
        },
        body: { enabled: false },
        hand: { enabled: false },
        object: { enabled: false },
        segmentation: { enabled: false }
    };

    const human = new Human.Human(config);

    async function initHuman() {
        $feedback.text('Initializing AI Models...');
        await human.load();
        await human.warmup();
        $('#loading-spinner').addClass('hidden');
        $feedback.text('Ready to start?');
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

    function onResults(results) {
        canvasCtx.save();
        canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
        
        if (!results.face || results.face.length === 0) {
            updateFeedback("Face not detected", "red");
            return;
        }

        const face = results.face[0];
        const landmarks = face.mesh;

        // --- Liveness & Anti-Spoof (Pre-trained Models) ---
        if (face.liveness < 0.25 || face.antispoof > 0.75) {
            updateFeedback("Face covered or spoof detected", "red");
            return;
        }

        // --- Anatomical Ratio Check ---
        const noseTip = landmarks[4];
        const leftEye = landmarks[33];
        const rightEye = landmarks[263];
        const eyeDist = Math.abs(leftEye[0] - rightEye[0]);
        const rotation = getFaceRotation(landmarks);

        $errorMsg.addClass('hidden');

        if (isScanning && !checkpoints.front) {
            captureFrame('front', landmarks);
            checkpoints.front = true;
            totalProgress += 20; 
        }

        handleLiveness(rotation, landmarks);

        // Visual Feedback
        const scaleX = canvasElement.width / videoElement.videoWidth;
        const scaleY = canvasElement.height / videoElement.videoHeight;
        canvasCtx.fillStyle = '#10b981';
        canvasCtx.beginPath();
        canvasCtx.arc(noseTip[0] * scaleX, noseTip[1] * scaleY, 6, 0, 2 * Math.PI);
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

    function handleLiveness(rot, landmarks) {
        if (isVerified || !isScanning) return;
        let detectedAction = null;
        if (rot.yaw < -0.8) detectedAction = 'right';
        else if (rot.yaw > 0.8) detectedAction = 'left';
        else if (rot.pitch < -0.4) detectedAction = 'up';
        else if (rot.pitch > 1.3) detectedAction = 'down';

        if (detectedAction && !checkpoints[detectedAction]) {
            captureFrame(detectedAction, landmarks);
            checkpoints[detectedAction] = true;
            $(`.checkpoint[data-target="${detectedAction}"]`).removeClass('bg-slate-100 text-slate-400').addClass('bg-emerald-500 text-white shadow-sm scale-105');
            totalProgress += 20;
            $progressBar.css('width', totalProgress + '%');
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
