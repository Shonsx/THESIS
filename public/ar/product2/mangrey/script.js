// Get video element for MediaPipe input
const videoElement = document.getElementsByClassName("input_video")[0];

// Initialize MediaPipe Pose
const pose = new Pose({
  locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/pose/${file}`,
});


pose.setOptions({
  modelComplexity: 1,
  smoothLandmarks: true,
  enableSegmentation: false,
  minDetectionConfidence: 0.5,
  minTrackingConfidence: 0.5,
});

pose.onResults(onResults);

// Setup camera feed for MediaPipe
const camera = new Camera(videoElement, {
  onFrame: async () => {
    await pose.send({ image: videoElement });
  },
  width: 640,
  height: 480,
});
camera.start();

function onResults(results) {
  if (!results.poseLandmarks) return;

  const landmarks = results.poseLandmarks;

  // Extract left and right shoulder positions (normalized 0..1)
  const leftShoulder = landmarks[11];
  const rightShoulder = landmarks[12];

  // Send data string to Unity script if unityInstance is ready
  if (typeof window.unityInstance !== "undefined") {
    window.unityInstance.SendMessage(
      "BodyTracker",
      "UpdateShoulders",
      `${leftShoulder.x},${leftShoulder.y},${rightShoulder.x},${rightShoulder.y}`
    );
  }
}
