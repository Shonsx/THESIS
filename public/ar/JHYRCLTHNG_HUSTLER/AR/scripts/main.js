import * as THREE from 'three';
import { PoseController } from './mediapipe-controller.js';
import { VirtualTryOn } from './virtual-tryon.js';

let vto = null;
let controller = null;

async function init() {
  // Prevent browser zoom so it doesn't affect layout or rendering
  window.addEventListener('wheel', (e) => {
    if (e.ctrlKey) e.preventDefault(); // Prevent zoom with Ctrl + scroll
  }, { passive: false });

  window.addEventListener('keydown', (e) => {
    if ((e.ctrlKey && (e.key === '+' || e.key === '-' || e.key === '0'))) {
      e.preventDefault(); // Prevent Ctrl + +, -, 0 zoom keys
    }
  });
  const canvas = document.getElementById('canvas');
  const video = document.getElementById('inputVideo');
  const statusEl = document.getElementById('status');
  const outsideDialog = document.getElementById('outsideDialog');
  const insideDialog = document.getElementById('insideDialog');
  const startBtn = document.getElementById('startBtn');
  const modelBoxEl = document.getElementById('modelBox');
  const mpCanvas = document.getElementById('mpCanvas');
  const mpCtx = mpCanvas ? mpCanvas.getContext('2d') : null;
  // Hide legacy DOM overlay to avoid confusion with MediaPipe-drawn box
  if (modelBoxEl) modelBoxEl.style.display = 'none';

  if (!canvas || !video || !statusEl) {
    console.error('UI elements missing:', { canvas, video, statusEl });
    return;
  }

  vto = new VirtualTryOn();
  vto.init(canvas);
  try {
    await vto.loadModel(vto.modelPath);
    if (statusEl) statusEl.textContent = 'Model loaded';
  } catch (e) {
    console.error('Model failed to load', e);
    if (statusEl) statusEl.textContent = 'Model load failed';
  }

  // Controls: Scale slider Clamped
  const scaleSlider = document.getElementById('scaleSlider');
  const scaleValue = document.getElementById('scaleValue');
  const verticalSlider = document.getElementById('verticalSlider');
  const verticalValue = document.getElementById('verticalValue');
  // Removed: swap arms, mirror feed controls
  if (scaleSlider && scaleValue) {
    const setScaleUI = (val) => {
      const clamped = Math.max(0.1, Math.min(3.0, val));
      scaleSlider.value = clamped.toFixed(2);
      scaleValue.textContent = clamped.toFixed(2) + '×';
    };
    // Initialize from current multiplier
    setScaleUI(vto.modelSizeMultiplier || 1.0);
    let lastScaleForOffset = vto.modelSizeMultiplier || 1.0;
    scaleSlider.addEventListener('input', (e) => {
      const v = parseFloat(e.target.value);
      vto.modelSizeMultiplier = isFinite(v) ? v : 1.0;
      setScaleUI(vto.modelSizeMultiplier);
    });
    // When user commits an increase, also nudge model down by 0.5
    scaleSlider.addEventListener('change', (e) => {
      const v = parseFloat(e.target.value);
      if (isFinite(v) && v > lastScaleForOffset + 1e-4) {
        vto.baseVerticalOffset = (vto.baseVerticalOffset || 0) - 0.2;
      }
      lastScaleForOffset = isFinite(v) ? v : lastScaleForOffset;
    });
  }

  // Controls: Vertical offset slider
  if (verticalSlider && verticalValue) {
    const setVerticalUI = (val) => {
      const clamped = Math.max(-1.5, Math.min(1.0, val));
      verticalSlider.value = clamped.toFixed(2);
      verticalValue.textContent = clamped.toFixed(2);
    };
    // Initialize from current baseVerticalOffset
    setVerticalUI(vto.baseVerticalOffset || 0);
    verticalSlider.addEventListener('input', (e) => {
      const v = parseFloat(e.target.value);
      vto.baseVerticalOffset = isFinite(v) ? v : (vto.baseVerticalOffset || 0);
      setVerticalUI(vto.baseVerticalOffset);
    });
  }


  // Auto Scale toggle removed from UI; autoscale remains enabled by default.


  // Removed: Swap Arms UI wiring


  // Removed: Mirror Feed UI wiring and display mirroring

  // Box tuning controls removed (using fixed constants below)

  // Verification UI (optional and may not exist)
  function renderReport(report) {
    const el = document.getElementById('verification');
    if (!el) return;
    el.innerHTML = '';
    report.items.forEach((item) => {
      const div = document.createElement('div');
      div.className = 'joint ' + (item.status === 'ok' ? 'ok' : item.status === 'warn' ? 'warn' : 'bad');
      div.textContent = `${item.joint}: ${item.message}`;
      el.appendChild(div);
    });
  }

  // MediaPipe controller
  controller = new PoseController(video, (landmarks) => {
    // Draw torso box on MediaPipe canvas (not via Three.js)
    if (mpCanvas && mpCtx) {
      const w = mpCanvas.clientWidth; const h = mpCanvas.clientHeight;
      if (mpCanvas.width !== w || mpCanvas.height !== h) {
        mpCanvas.width = w; mpCanvas.height = h;
      }
      mpCtx.clearRect(0, 0, mpCanvas.width, mpCanvas.height);

      if (!landmarks) {
        statusEl.textContent = 'No landmarks';
        // Ensure overlay is visible when prompting alignment
        if (mpCanvas && mpCanvas.style) mpCanvas.style.visibility = 'visible';
        if (outsideDialog) { outsideDialog.style.display = 'block'; outsideDialog.textContent = 'Fit Torso in box'; }
        if (insideDialog) insideDialog.style.display = 'none';
        // Hide model when not detected
        if (vto && vto.boneModel) vto.boneModel.visible = false;
        // Fixed-size portrait guide box (not affected by scale slider)
        const boxOffsetY = 32; // shift box further downward
        const margin = 20;
        const aspectPortrait = 1.6; // height = aspectPortrait * width (portrait)
        // Start from width, enforce portrait height, clamp to canvas
        let gw = Math.floor(mpCanvas.width * 0.24);
        gw = Math.min(Math.max(220, gw), mpCanvas.width - margin * 2);
        let gh = Math.floor(gw * aspectPortrait);
        if (gh > mpCanvas.height - margin * 2) {
          gh = mpCanvas.height - margin * 2;
          gw = Math.floor(gh / aspectPortrait);
        }
        const gx = Math.floor((mpCanvas.width - gw) / 2);
        const gy = Math.floor(mpCanvas.height * 0.34) + boxOffsetY;
        mpCtx.save();
        mpCtx.strokeStyle = 'rgba(0,255,255,0.95)';
        mpCtx.setLineDash([8, 8]);
        mpCtx.lineWidth = 5; // thicker
        mpCtx.strokeRect(gx, gy, gw, gh);
        mpCtx.restore();
        // Center the dialog within the guide box
        if (outsideDialog) {
          outsideDialog.style.left = (gx + gw / 2) + 'px';
          outsideDialog.style.top = (gy + gh / 2) + 'px';
        }
        return;
      }

      statusEl.textContent = 'Tracking';
      vto.updateFromLandmarks(landmarks);

      // Fixed-size portrait box at a fixed position (not affected by scale or model)
      const boxOffsetY = 32; // shift box further downward
      const margin = 20;
      const aspectPortrait = 1.6; // height = aspectPortrait * width (portrait)
      // Start from width, enforce portrait height, clamp to canvas
      let bw = Math.floor(mpCanvas.width * 0.24);
      bw = Math.min(Math.max(220, bw), mpCanvas.width - margin * 2);
      let bh = Math.floor(bw * aspectPortrait);
      if (bh > mpCanvas.height - margin * 2) {
        bh = mpCanvas.height - margin * 2;
        bw = Math.floor(bh / aspectPortrait);
      }
      // Fixed center horizontally and lower vertically
      const bx = Math.floor((mpCanvas.width - bw) / 2);
      const by = Math.floor(mpCanvas.height * 0.32) + boxOffsetY;

      // Draw only the torso outline (shoulders and hips) and hide when fully inside the box
      const projectToScreen = (v) => {
        const ndc = v.clone().project(vto.camera);
        return {
          x: (ndc.x * 0.5 + 0.5) * mpCanvas.width,
          y: (-ndc.y * 0.5 + 0.5) * mpCanvas.height,
        };
      };
      const d = vto.followDepth;
      const lShW = vto.normalizedToWorld(landmarks[11].x, landmarks[11].y, d);
      const rShW = vto.normalizedToWorld(landmarks[12].x, landmarks[12].y, d);
      const rHipW = vto.normalizedToWorld(landmarks[24].x, landmarks[24].y, d);
      const lHipW = vto.normalizedToWorld(landmarks[23].x, landmarks[23].y, d);
      const pts = [lShW, rShW, rHipW, lHipW].map(projectToScreen);
      // Check if torso is fully inside the fixed box
      const insideFixed = pts.every((p) => p.x >= bx && p.x <= bx + bw && p.y >= by && p.y <= by + bh);

      // Toggle model visibility based on alignment (model hidden when outside the box)
      if (vto && vto.boneModel) vto.boneModel.visible = !!insideFixed;

      // Overlay remains visible; manage dialogs
      if (mpCanvas && mpCanvas.style) mpCanvas.style.visibility = 'visible';
      if (insideFixed) {
        statusEl.textContent = 'Aligned';
        if (outsideDialog) outsideDialog.style.display = 'none';
        if (insideDialog) {
          insideDialog.style.display = 'block';
          insideDialog.textContent = 'Ensure your full torso is visible and centered in the designated area.';
        }
      } else {
        if (insideDialog) insideDialog.style.display = 'none';
        if (outsideDialog) {
          outsideDialog.style.display = 'block';
          outsideDialog.textContent = ' Please go inside the box';
          // Position at center of the fixed box
          outsideDialog.style.left = (bx + bw / 2) + 'px';
          outsideDialog.style.top = (by + bh / 2) + 'px';
        }
      }

      // Torso outline with red-blue "shine" only when outside the box
      if (!insideFixed) {
        // Red glow pass
        mpCtx.save();
        mpCtx.setLineDash([]);
        mpCtx.lineWidth = 6;
        mpCtx.strokeStyle = 'rgba(255,0,0,0.85)';
        mpCtx.shadowColor = 'rgba(255,0,0,0.7)';
        mpCtx.shadowBlur = 18;
        mpCtx.beginPath();
        mpCtx.moveTo(pts[0].x, pts[0].y);
        mpCtx.lineTo(pts[1].x, pts[1].y);
        mpCtx.lineTo(pts[2].x, pts[2].y);
        mpCtx.lineTo(pts[3].x, pts[3].y);
        mpCtx.closePath();
        mpCtx.stroke();
        mpCtx.restore();

        // Blue crisp outline pass
        mpCtx.save();
        mpCtx.setLineDash([]);
        mpCtx.lineWidth = 3;
        mpCtx.strokeStyle = 'rgba(0,136,255,1)';
        mpCtx.beginPath();
        mpCtx.moveTo(pts[0].x, pts[0].y);
        mpCtx.lineTo(pts[1].x, pts[1].y);
        mpCtx.lineTo(pts[2].x, pts[2].y);
        mpCtx.lineTo(pts[3].x, pts[3].y);
        mpCtx.closePath();
        mpCtx.stroke();
        mpCtx.restore();
      }

      // Always draw the cyan box; thicker, and lower opacity when inside
      mpCtx.save();
      mpCtx.setLineDash([6, 6]);
      mpCtx.lineWidth = 5; // thicker
      mpCtx.strokeStyle = insideFixed ? 'rgba(0,255,255,0.40)' : 'rgba(0,255,255,0.95)';
      mpCtx.strokeRect(bx, by, bw, bh);
      mpCtx.restore();

      // Draw the fixed cyan box only when the torso is NOT fully inside it
      if (!insideFixed) {
        mpCtx.save();
        mpCtx.strokeStyle = 'rgba(0,255,255,0.95)';
        mpCtx.setLineDash([6, 6]);
        mpCtx.lineWidth = 3;
        mpCtx.strokeRect(bx, by, bw, bh);
        mpCtx.restore();
      }
    }
    // Example verification (simple)
    const report = {
      items: [
        { joint: 'Chest', message: 'Following torso', status: 'ok' },
        { joint: 'LeftArm', message: 'Tracking', status: 'ok' },
        { joint: 'RightArm', message: 'Tracking', status: 'ok' }
      ]
    };
    renderReport(report);
  });

  // Start camera with guard; also keep button for manual retry
  let cameraStarted = false;
  async function startCameraOnce() {
    if (cameraStarted) return;
    cameraStarted = true;
    if (startBtn) startBtn.disabled = true;
    statusEl.textContent = 'Starting camera…';
    try {
      await controller.start();
      statusEl.textContent = 'Camera started';
    } catch (e) {
      statusEl.textContent = 'Camera error';
      console.error('Camera start failed', e);
      cameraStarted = false;
      if (startBtn) startBtn.disabled = false;
    }
  }
  if (startBtn) {
    startBtn.addEventListener('click', startCameraOnce);
  }
  // Auto-start on load
  await startCameraOnce();

  // Render loop
  function animate() {
    requestAnimationFrame(animate);
    vto.render();
  }
  animate();
}

// Ensure DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}