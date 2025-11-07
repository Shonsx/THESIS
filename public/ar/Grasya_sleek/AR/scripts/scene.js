import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

export async function setupScene(canvas) {
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
  // Keep pixel ratio fixed so browser zoom doesn’t change render scale
  renderer.setPixelRatio(1);
  renderer.setSize(canvas.clientWidth, canvas.clientHeight, false);

  const scene = new THREE.Scene();
  scene.background = null;
  const camera = new THREE.PerspectiveCamera(50, canvas.clientWidth / canvas.clientHeight, 0.01, 100);
  camera.position.set(0, 1.4, 3);

  const hemi = new THREE.HemisphereLight(0xffffff, 0x334466, 1.0);
  scene.add(hemi);
  const dir = new THREE.DirectionalLight(0xffffff, 0.6);
  dir.position.set(4, 6, 2);
  scene.add(dir);

  // Removed ground grid for clean camera-only try-on

  const clock = new THREE.Clock();

  function onResize() {
    const rect = canvas.getBoundingClientRect();
    const w = Math.max(1, Math.floor(rect.width));
    const h = Math.max(1, Math.floor(rect.height));
    renderer.setPixelRatio(1);
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
  }
  window.addEventListener('resize', onResize);
  onResize();

  const ctx = { renderer, scene, camera, clock };
  renderer.setAnimationLoop(() => {
    renderer.render(scene, camera);
  });
  return ctx;
}

function findBoneByName(root, names) {
  for (const n of names) {
    const bone = root.getObjectByName(n);
    if (bone) return bone;
  }
  return null;
}

export async function loadRigAndHelpers(ctx, glbPath) {
  const loader = new GLTFLoader();
  const gltf = await loader.loadAsync(glbPath);
  const root = gltf.scene;
  root.traverse((obj) => {
    if (obj.isMesh) {
      obj.castShadow = true;
      obj.receiveShadow = true;
    }
  });
  ctx.scene.add(root);

  // Try to find bones by common names in user's rig
  const bones = {
    Spine: findBoneByName(root, ['Spine', 'spine', 'mixamorigSpine']),
    Chest: findBoneByName(root, ['Chest', 'chest', 'Spine2', 'mixamorigSpine2']),
    Neck: findBoneByName(root, ['Neck', 'neck', 'mixamorigNeck']),
    Head: findBoneByName(root, ['Head', 'head', 'mixamorigHead']),
    ShoulderR: findBoneByName(root, ['ShoulderR', 'RightShoulder', 'mixamorigRightShoulder']),
    UpperArmR: findBoneByName(root, ['UpperArmR', 'RightArm', 'RightUpperArm', 'mixamorigRightArm']),
    LowerArmR: findBoneByName(root, ['LowerArmR', 'RightForeArm', 'RightLowerArm', 'mixamorigRightForeArm']),
    ShoulderL: findBoneByName(root, ['ShoulderL', 'LeftShoulder', 'mixamorigLeftShoulder']),
    UpperArmL: findBoneByName(root, ['UpperArmL', 'LeftArm', 'LeftUpperArm', 'mixamorigLeftArm']),
    LowerArmL: findBoneByName(root, ['LowerArmL', 'LeftForeArm', 'LeftLowerArm', 'mixamorigLeftForeArm']),
  };

  // Visual helpers (SkeletonHelper and axis gizmos)
  // Omit SkeletonHelper to avoid visual controls/overlays

  // Constraints ranges (radians) approximated from clinical ROM references
  const deg = (d) => THREE.MathUtils.degToRad(d);
  const constraints = {
    Chest: rangeXYZ(deg(-30), deg(30), deg(-30), deg(30), deg(-40), deg(40)),
    Neck: rangeXYZ(deg(-80), deg(80), deg(-80), deg(80), deg(-80), deg(80)),
    Head: rangeXYZ(deg(-80), deg(80), deg(-80), deg(80), deg(-80), deg(80)),
    UpperArmR: rangeXYZ(deg(-60), deg(180), deg(-40), deg(180), deg(-90), deg(90)),
    LowerArmR: rangeXYZ(deg(0), deg(150), deg(-15), deg(15), deg(-90), deg(90)),
    UpperArmL: rangeXYZ(deg(-60), deg(180), deg(-180), deg(40), deg(-90), deg(90)),
    LowerArmL: rangeXYZ(deg(0), deg(150), deg(-15), deg(15), deg(-90), deg(90)),
  };

  // Small axes helpers on key bones
  const helpers = [];
  // Skip axes helpers on bones to keep view uncluttered

  return {
    root,
    bones,
    constraints,
    helpers,
    helpersVisible: true,
    ctx,
  };
}

export function updateConstraintsVisibility(rig, visible) {
  rig.helpers.forEach((h) => (h.visible = visible));
}

function rangeXYZ(xMin, xMax, yMin, yMax, zMin, zMax) {
  return {
    min: new THREE.Vector3(xMin, yMin, zMin),
    max: new THREE.Vector3(xMax, yMax, zMax),
  };
}