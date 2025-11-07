// MediaPipe Pose classic via CDN (global script, not ESM)
// Docs: https://google.github.io/mediapipe/solutions/pose

function loadScript(src) {
  return new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = src;
    s.async = true;
    s.onload = () => resolve();
    s.onerror = (e) => reject(e);
    document.head.appendChild(s);
  });
}

export class PoseController {
  constructor(videoEl, onResultsCb) {
    this.videoEl = videoEl;
    this.onResultsCb = onResultsCb;
    this.pose = null;
    this.camera = null;
  }

  async start() {
    await this._loadPose();
    await this._startCamera();
  }

  async _loadPose() {
    // Load global scripts to access window.Pose and window.Camera
    await loadScript('https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js');
    await loadScript('https://cdn.jsdelivr.net/npm/@mediapipe/pose@0.5/pose.js');

    this.pose = new window.Pose({
      locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/pose@0.5/${file}`,
    });
    this.pose.setOptions({
      modelComplexity: 0,           // lighter model for higher FPS
      smoothLandmarks: false,       // reduce MP temporal smoothing to cut latency
      enableSegmentation: false,
      minDetectionConfidence: 0.6,
      minTrackingConfidence: 0.6,
    });
    this.pose.onResults((res) => {
      const landmarks = res.poseLandmarks || null;
      this.onResultsCb(landmarks);
    });
  }

  async _startCamera() {
    this.camera = new window.Camera(this.videoEl, {
      onFrame: async () => {
        await this.pose.send({ image: this.videoEl });
      },
      width: 480,   // lower resolution for higher FPS
      height: 360,
    });
    await this.camera.start();
  }
}