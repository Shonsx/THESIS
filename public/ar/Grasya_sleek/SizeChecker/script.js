const videoElement = document.getElementById('video');
const canvasElement = document.getElementById('canvas');
const canvasCtx = canvasElement.getContext('2d');
const sizeResult = document.getElementById('sizeResult');
const calibrationInstructions = document.getElementById('calibrationInstructions');
const startButton = document.getElementById('startButton');
const sideControls = document.getElementById('sideControls');
const repeatButton = document.getElementById('repeatButton');
const exitButton = document.getElementById('exitButton');
const topLogo = document.getElementById('topLogo');
const resultModal = document.getElementById('resultModal');
const resultHeader = document.getElementById('resultHeader');
const resultBody = document.getElementById('resultBody');
const popupRepeat = document.getElementById('popupRepeat');
const popupTryOn = document.getElementById('popupTryOn');
const popupClose = document.getElementById('popupClose');

// Responsive: sync canvas pixels to container size (with DPR)
function syncCanvasToContainer() {
  const parent = canvasElement.parentElement;
  if (!parent) return;
  const rect = parent.getBoundingClientRect();
  const dpr = window.devicePixelRatio || 1;
  canvasElement.width = Math.round(rect.width * dpr);
  canvasElement.height = Math.round(rect.height * dpr);
  canvasElement.style.width = `${Math.round(rect.width)}px`;
  canvasElement.style.height = `${Math.round(rect.height)}px`;
}

// Use actual video frame size for measurement math, decoupled from canvas pixels
function getFrameSize() {
  const w = (videoElement && videoElement.videoWidth) ? videoElement.videoWidth : canvasElement.width;
  const h = (videoElement && videoElement.videoHeight) ? videoElement.videoHeight : canvasElement.height;
  return { w, h };
}

const sizeChart = [
  { size: 'XS Extra Small', wMin: 13.0, wMax: 13.0, lMin: 24.0, lMax: 24.0 },
  { size: 'Small', wMin: 14.0, wMax: 14.0, lMin: 25.0, lMax: 25.0 },
  { size: 'Medium', wMin: 16.0, wMax: 16.0, lMin: 26.0, lMax: 26.0 }, // Target: 16-inch shoulder as M, 26-inch torso
  { size: 'Large', wMin: 17.0, wMax: 17.0, lMin: 27.5, lMax: 27.5 }, // Target: breathable for M (17-inch width, 27.5-inch length)
  { size: 'XL Extra Large', wMin: 18.0, wMax: 18.0, lMin: 28.5, lMax: 28.5 },
  { size: '2XL', wMin: 19.0, wMax: 19.0, lMin: 29.5, lMax: 29.5 },
  { size: '3XL', wMin: 20.0, wMax: 20.0, lMin: 30.5, lMax: 30.5 },
  { size: '4XL', wMin: 22.0, wMax: 22.0, lMin: 32.0, lMax: 32.0 },
];

let pxToInch = 0; // This will be updated by calibration
let calibrationState = 'initial_prompt'; // 'initial_prompt', 'countdown', 'calibrating', 'calibrated'
const ACTUAL_SHOULDER_WIDTH_INCHES = 16; // User's actual shoulder width for calibration

// Define a target shoulderWidthPx range for calibration at the preferred distance.
// The range 180-200px seems to yield about 16 inches shoulder width for the user.
const TARGET_CALIBRATION_PX_WIDTH_MIN = 180; 
const TARGET_CALIBRATION_PX_WIDTH_MAX = 200; 

// Add a global flag to control when to start pose detection
let sizeTryStarted = false;
let stableResultTimer = null;
let lastComputedResult = null; // legacy: no longer used for stability
let lastSizeLabel = null; // Track only the size name for stability
let lastLooseLabel = null; // Track the loose (one size up) label
const SIZE_STABLE_MS = 1500; // Show popup when size stays same for this long
let resultLocked = false; // When true, freeze result updates after popup

function findSize(width, length) {
  const tolerance = 1; // 1 inch tolerance
  for (const entry of sizeChart) {
    const wMin = entry.wMin - tolerance;
    const wMax = entry.wMax + tolerance;
    const lMin = entry.lMin - tolerance;
    const lMax = entry.lMax + tolerance;

    console.log(`Checking size ${entry.size}: W [${wMin.toFixed(1)}-${wMax.toFixed(1)}] L [${lMin.toFixed(1)}-${lMax.toFixed(1)}]`);
    console.log(`  Actual: W ${width.toFixed(1)}, L ${length.toFixed(1)}`);

    if (
      width >= wMin && width <= wMax &&
      length >= lMin && length <= lMax
    ) {
      return entry.size;
    }
  }
  return 'Unknown';
}

function distance(p1, p2) {
  const { w, h } = getFrameSize();
  const dx = (p1.x - p2.x) * w;
  const dy = (p1.y - p2.y) * h;
  return Math.sqrt(dx * dx + dy * dy);
}

function drawLandmarks(landmarks) {
  canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
  // Draw the static guide rectangle
  const guideRectX = canvasElement.width * 0.15;
  const guideRectY = canvasElement.height * 0.1;
  const guideRectWidth = canvasElement.width * 0.7;
  const guideRectHeight = canvasElement.height * 0.8;
  canvasCtx.strokeStyle = '#FFC107';
  canvasCtx.lineWidth = 5;
  canvasCtx.setLineDash([]);
  canvasCtx.strokeRect(guideRectX, guideRectY, guideRectWidth, guideRectHeight);

  // Additional white overlay design elements
  // Corner brackets
  const cornerLen = 40;
  canvasCtx.strokeStyle = 'white';
  canvasCtx.lineWidth = 3;
  // Top-left
  canvasCtx.beginPath();
  canvasCtx.moveTo(guideRectX, guideRectY + cornerLen);
  canvasCtx.lineTo(guideRectX, guideRectY);
  canvasCtx.lineTo(guideRectX + cornerLen, guideRectY);
  canvasCtx.stroke();
  // Top-right
  canvasCtx.beginPath();
  canvasCtx.moveTo(guideRectX + guideRectWidth - cornerLen, guideRectY);
  canvasCtx.lineTo(guideRectX + guideRectWidth, guideRectY);
  canvasCtx.lineTo(guideRectX + guideRectWidth, guideRectY + cornerLen);
  canvasCtx.stroke();
  // Bottom-left
  canvasCtx.beginPath();
  canvasCtx.moveTo(guideRectX, guideRectY + guideRectHeight - cornerLen);
  canvasCtx.lineTo(guideRectX, guideRectY + guideRectHeight);
  canvasCtx.lineTo(guideRectX + cornerLen, guideRectY + guideRectHeight);
  canvasCtx.stroke();
  // Bottom-right
  canvasCtx.beginPath();
  canvasCtx.moveTo(guideRectX + guideRectWidth - cornerLen, guideRectY + guideRectHeight);
  canvasCtx.lineTo(guideRectX + guideRectWidth, guideRectY + guideRectHeight);
  canvasCtx.lineTo(guideRectX + guideRectWidth, guideRectY + guideRectHeight - cornerLen);
  canvasCtx.stroke();

  // If pose landmarks are present, outline the torso
  if (landmarks && landmarks.length > 0) {
    const leftShoulder = landmarks[11];
    const rightShoulder = landmarks[12];
    const rightHip = landmarks[24];
    const leftHip = landmarks[23];
    if (leftShoulder && rightShoulder && rightHip && leftHip) {
      canvasCtx.save();
      canvasCtx.strokeStyle = 'white';
      canvasCtx.lineWidth = 4;
      canvasCtx.beginPath();
      canvasCtx.moveTo(leftShoulder.x * canvasElement.width, leftShoulder.y * canvasElement.height);
      canvasCtx.lineTo(rightShoulder.x * canvasElement.width, rightShoulder.y * canvasElement.height);
      canvasCtx.lineTo(rightHip.x * canvasElement.width, rightHip.y * canvasElement.height);
      canvasCtx.lineTo(leftHip.x * canvasElement.width, leftHip.y * canvasElement.height);
      canvasCtx.closePath();
      canvasCtx.stroke();
      canvasCtx.restore();
    }
  }
}

// Function to start the calibration countdown
function startCalibrationCountdown() {
  calibrationInstructions.style.display = 'block';
  sizeResult.style.display = 'none'; // Hide size result during calibration
  calibrationState = 'countdown';
  if (topLogo) topLogo.classList.add('loading');
  
  let count = 3; // Changed from 5 to 3
  calibrationInstructions.textContent = 'Hold still, size is measuring...'; // New initial message

  // Small delay before starting numerical countdown to show the initial message
  setTimeout(() => {
    const countdownInterval = setInterval(() => {
      if (count > 0) {
        calibrationInstructions.textContent = `${count}`;
        count--;
      } else if (count === 0) {
        calibrationInstructions.textContent = 'SNAP!';
        calibrationState = 'calibrating'; // Temporarily set state to capture measurement
        clearInterval(countdownInterval);
        // A small delay to ensure 'SNAP!' is visible before switching
        setTimeout(() => {
          calibrationInstructions.style.display = 'none';
          sizeResult.style.display = 'block'; // Show size result after calibration
          calibrationState = 'calibrated';
          if (topLogo) topLogo.classList.remove('loading');
          console.log('Calibration complete. System is now ready for size detection.');
        }, 500);
      }
    }, 1000);
  }, 1000); // Wait 1 second before starting numerical countdown
}

function onResults(results) {
  // If result is locked, keep drawing overlay but do not change size/result
  if (resultLocked) {
    drawLandmarks(results.poseLandmarks || []);
    return;
  }
  drawLandmarks(results.poseLandmarks || []);
  if (!sizeTryStarted) {
    // Pre-start: do not show detecting; keep prompt minimal
    return;
  }
  if (!results.poseLandmarks) {
    if (calibrationState === 'initial_prompt') {
      calibrationInstructions.textContent = 'Please stand in frame to begin calibration.';
    } else if (calibrationState !== 'calibrated') {
      calibrationInstructions.textContent = 'Detecting pose... Please stand in frame for calibration.';
    } else {
      sizeResult.textContent = 'Detecting... Please ensure your full torso is visible and within the yellow guide.';
    }
    if (topLogo && (calibrationState === 'initial_prompt' || calibrationState !== 'calibrated')) {
      topLogo.classList.add('loading');
    }
    return;
  }

  const leftShoulder = results.poseLandmarks[11];
  const rightShoulder = results.poseLandmarks[12];
  const leftHip = results.poseLandmarks[23];
  const rightHip = results.poseLandmarks[24];

  // Define normalized coordinates for the guide rectangle
  const guideRectNormalizedX = 0.15;
  const guideRectNormalizedY = 0.1;
  const guideRectNormalizedWidth = 0.7;
  const guideRectNormalizedHeight = 0.8;

  if (leftShoulder && rightShoulder && leftHip && rightHip) {
    if (calibrationState === 'initial_prompt') {
      const currentShoulderWidthPx = distance(leftShoulder, rightShoulder);

      if (currentShoulderWidthPx >= TARGET_CALIBRATION_PX_WIDTH_MIN && currentShoulderWidthPx <= TARGET_CALIBRATION_PX_WIDTH_MAX) {
        calibrationInstructions.textContent = 'You are at a good distance! Hold still.';
        startCalibrationCountdown(); // Start countdown when distance is good
      } else if (currentShoulderWidthPx < TARGET_CALIBRATION_PX_WIDTH_MIN) {
        calibrationInstructions.textContent = 'Too far.'; // Simplified message
      } else if (currentShoulderWidthPx > TARGET_CALIBRATION_PX_WIDTH_MAX) {
        calibrationInstructions.textContent = 'Too close.'; // Simplified message
      }
      return; // Return during initial prompt phase
    }

    if (calibrationState === 'calibrating') {
      const torsoWidthPx = distance(leftShoulder, rightShoulder);
      pxToInch = ACTUAL_SHOULDER_WIDTH_INCHES / torsoWidthPx; // Calculate pxToInch
      console.log('Calibration Measurement Captured:', {
        shoulderWidthPx: torsoWidthPx,
        actualShoulderWidthInches: ACTUAL_SHOULDER_WIDTH_INCHES,
        calculatedPxToInch: pxToInch
      });
      // No return here, let the state change in setTimeout of startCalibrationCountdown
    }

    if (calibrationState !== 'calibrated') {
      // Only show pose detection during calibration if not yet calibrated
      return;
    }

    // Check if shoulders are within the horizontal bounds of the guide
    if (leftShoulder.x < guideRectNormalizedX || rightShoulder.x > (guideRectNormalizedX + guideRectNormalizedWidth)) {
      sizeResult.textContent = 'Please center yourself horizontally within the white guide.';
      return;
    }
    // Check if shoulders and hips are within the vertical bounds of the guide
    if (leftShoulder.y < guideRectNormalizedY || rightShoulder.y < guideRectNormalizedY ||
        leftHip.y > (guideRectNormalizedY + guideRectNormalizedHeight) || rightHip.y > (guideRectNormalizedY + guideRectNormalizedHeight)) {
      sizeResult.textContent = 'Please adjust vertically to fit within the white guide.';
      return;
    }

    const torsoWidthPx = distance(leftShoulder, rightShoulder);
    const torsoLengthPx = (distance(leftShoulder, leftHip) + distance(rightShoulder, rightHip)) / 2;

    const torsoWidthIn = torsoWidthPx * pxToInch;
    const torsoLengthIn = torsoLengthPx * pxToInch;

    const fitSize = findSize(torsoWidthIn, torsoLengthIn); // Get the primary fit size
    let breathableSize = 'N/A';

    // Determine breathable size - find the next size up from fitSize
    const fitSizeIndex = sizeChart.findIndex(entry => entry.size === fitSize);
    if (fitSizeIndex !== -1 && fitSizeIndex < sizeChart.length - 1) {
      breathableSize = sizeChart[fitSizeIndex + 1].size;
    }

    if (fitSize === 'Unknown') {
      sizeResult.textContent = 'Please fix your posture and match the camera angle.';
      lastSizeLabel = null;
      lastLooseLabel = null;
      if (stableResultTimer) { clearTimeout(stableResultTimer); stableResultTimer = null; }
    } else {
      // Show only the size name in the inline status (remove numbers)
      sizeResult.textContent = `Suggested Body Fit Size: ${fitSize}`;
      // Stability based solely on the size, ignoring width/length fluctuations
      if (lastSizeLabel !== fitSize) {
        lastSizeLabel = fitSize;
        lastLooseLabel = breathableSize;
        if (stableResultTimer) clearTimeout(stableResultTimer);
        stableResultTimer = setTimeout(() => {
          const popupText = `Your size is "${lastSizeLabel}" (Fit).`;
          showResultPopup(popupText);
        }, SIZE_STABLE_MS);
      }
    }
    
    console.log('Measurements:', {
      shoulderWidthPx: torsoWidthPx,
      shoulderWidthIn: torsoWidthIn,
      torsoLengthPx: torsoLengthPx,
      torsoLengthIn: torsoLengthIn,
      pxToInch: pxToInch
    });
  } else {
    if (calibrationState === 'initial_prompt') {
      calibrationInstructions.textContent = 'Please stand in frame to begin calibration.';
    } else if (calibrationState !== 'calibrated') {
      calibrationInstructions.textContent = 'Please ensure your full torso is visible and within the white guide.';
    } else {
      sizeResult.textContent = 'Detecting... Please ensure your full torso is visible and within the white guide.';
    }
  }
}

const pose = new window.Pose({
  locateFile: (file) => {
    return `https://cdn.jsdelivr.net/npm/@mediapipe/pose/${file}`;
  }
});

pose.setOptions({
  modelComplexity: 1,
  smoothLandmarks: true,
  enableSegmentation: false,
  smoothSegmentation: false,
  minDetectionConfidence: 0.5,
  minTrackingConfidence: 0.5
});

pose.onResults(onResults);

const camera = new window.Camera(videoElement, {
  onFrame: async () => {
    await pose.send({ image: videoElement });
  },
  width: 1280,
  height: 960
});

// Only start the camera after the Start button is pressed
// Remove or comment out the immediate camera.start() call

// Add a function to start the process
function startSizeTry() {
  sizeTryStarted = true;
  // Show detection status only after start is pressed
  sizeResult.textContent = 'Detecting...';
  calibrationInstructions.style.display = 'block';
  calibrationInstructions.textContent = 'Please stand in frame to begin calibration.';
  videoElement.style.display = '';
  if (topLogo) topLogo.classList.add('loading');
  // Show side controls
  if (sideControls) sideControls.style.display = 'flex';
  // Ensure canvas pixels match container before starting
  syncCanvasToContainer();
  camera.start()
    .then(() => {
      console.log('Camera started successfully');
      // Re-sync after camera initializes (layout can shift)
      syncCanvasToContainer();
      // Adjust container aspect ratio based on actual video stream
      applyContainerAspectRatio();
    })
    .catch((error) => {
      console.error('Error starting camera:', error);
      sizeResult.textContent = 'Error: Could not access camera. Please make sure you have granted camera permissions and your camera is working.';
      calibrationInstructions.style.display = 'none';
    });
}

// Helpers to reset/quit
function resetCalibration() {
  calibrationState = 'initial_prompt';
  pxToInch = 0;
}

function clearCanvas() {
  canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
}

function stopCamera() {
  try {
    if (camera && typeof camera.stop === 'function') {
      camera.stop();
    }
  } catch (e) {
    // fallback
  }
  try {
    const stream = videoElement.srcObject;
    if (stream && stream.getTracks) {
      stream.getTracks().forEach(t => t.stop());
    }
  } catch (e) {}
}

function repeatMeasurement() {
  // New behavior: go back to popup to repeat the whole process
  goToPopupStart();
}

function quitToStart() {
  stopCamera();
  sizeTryStarted = false;
  resetCalibration();
  clearCanvas();
  resultLocked = false;
  // UI reset
  if (startButton) startButton.style.display = '';
  if (sideControls) sideControls.style.display = 'none';
  calibrationInstructions.style.display = 'none';
  sizeResult.style.display = 'block';
  sizeResult.textContent = 'Press Start to begin';
  if (topLogo) topLogo.classList.remove('loading');
  hideResultPopup();
  // Clear any pending result popup timer/state
  if (stableResultTimer) { clearTimeout(stableResultTimer); stableResultTimer = null; }
  lastSizeLabel = null;
  lastLooseLabel = null;
}

// Wire buttons
if (repeatButton) repeatButton.addEventListener('click', repeatMeasurement);
if (exitButton) exitButton.addEventListener('click', quitToStart);

function showResultPopup(text) {
  if (!resultModal) return;
  resultHeader.textContent = 'Your Suggested Size Result';
  resultBody.textContent = text || 'Result ready.';
  resultModal.style.display = 'flex';
  // Lock result and stop future updates
  resultLocked = true;
  if (stableResultTimer) { clearTimeout(stableResultTimer); stableResultTimer = null; }
}

function hideResultPopup() {
  if (!resultModal) return;
  resultModal.style.display = 'none';
}

if (popupRepeat) popupRepeat.addEventListener('click', () => {
  // Repeat via popup: reset calibration and reopen the wizard
  goToPopupStart();
});
if (popupClose) popupClose.addEventListener('click', () => {
  // Return to Start when Close is pressed on the result popup
  quitToStart();
});
if (popupTryOn) popupTryOn.addEventListener('click', () => {
  const parts = window.location.pathname.split('/').filter(Boolean);
  const productName = parts.length >= 2 ? decodeURIComponent(parts[1]) : '';
  const encodedName = encodeURIComponent(productName);
  window.location.href = `/ar/${encodedName}/AR/`;
});

// Initial sync and responsive handling
window.addEventListener('DOMContentLoaded', () => {
  syncCanvasToContainer();
  // Initial aspect ratio until we know video stream
  applyContainerAspectRatio();
});
window.addEventListener('resize', () => {
  syncCanvasToContainer();
});

// Dynamically set container aspect ratio from video metadata (e.g., 16:9 cameras)
function applyContainerAspectRatio() {
  const container = document.querySelector('.container');
  if (!container) return;
  // Force square regardless of camera feed ratio
  container.style.aspectRatio = '1 / 1';
}

// Update aspect ratio when video metadata is available
if (videoElement) {
  videoElement.addEventListener('loadedmetadata', () => {
    applyContainerAspectRatio();
    syncCanvasToContainer();
  });
}

// Helper: return to start state and reopen popup for a fresh run
function goToPopupStart() {
  // Reset to start UI and stop camera
  quitToStart();
  // Open the instruction modal at step 1
  if (typeof openInstruction === 'function') openInstruction();
  if (typeof resetWizard === 'function') resetWizard();
  // Clear any pending stability timers
  if (stableResultTimer) { clearTimeout(stableResultTimer); stableResultTimer = null; }
  lastSizeLabel = null;
  lastLooseLabel = null;
}
