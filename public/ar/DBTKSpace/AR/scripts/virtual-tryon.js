import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

export class VirtualTryOn {
  constructor() {
    this.scene = null;
    this.camera = null;
    this.renderer = null;
    this.boneModel = null;
    this.leftSleeve = null;
    this.rightSleeve = null;
    this.torsoBone = null;
    // Named bones for armature-driven models
    this.spineBone = null; // backbone close to neck
    this.rootBone = null;  // waist / pelvis root
    this.leftShoulderBone = null;
    this.rightShoulderBone = null;
    this.leftArmBone = null;   // upper arm
    this.rightArmBone = null;
    this.leftForearmBone = null; // lower arm / forearm
    this.rightForearmBone = null;
    // Detected local aim axes for bones (which local axis points toward the child)
    this.leftUpperAimAxis = new THREE.Vector3(0, -1, 0);
    this.rightUpperAimAxis = new THREE.Vector3(0, -1, 0);
    this.leftForeAimAxis = new THREE.Vector3(0, -1, 0);
    this.rightForeAimAxis = new THREE.Vector3(0, -1, 0);
    this.pose = null;
    this.webcam = null;
    this.isTracking = false;
    this.modelRotation = 0; // Face camera (-Z) without inversion
    this.modelScale = 0.10; // Further reduce baseline scale
    this.modelPositionY = 0.94; // nudge spawn point slightly downward
    this.modelPositionZ = 0.0; // in front of camera (camera looks down -Z)
    this.smoothingFactor = 0.6;
    this.isLoading = true;
    // Sean eto yung path paltan paltan mo
    this.modelPath = 'DBTK.glb';

    // Body tracking properties
    this.bodyPosition = new THREE.Vector3(0, 0, 0);
    this.bodyScale = 1.0;
    this.targetBodyPosition = new THREE.Vector3(0, 0, 0);
    this.targetBodyScale = 1.0;
    this.bodySmoothing = 0.25; // Faster following for closer fit
    // Smoothing for body position following (0..1, higher = faster)
    this.positionFollowAlpha = 0.35;

    // Enhanced auto-tracking properties
    // Depth in world units to map normalized landmarks to. Used for follow mapping.
    this.followDepth = 0.5; // bring mapped points closer to camera
    this.autoScaleEnabled = true;
    // Disable spine/chest orientation following unless explicitly enabled
    this.spineChestFollowEnabled = false;
    // Base vertical offset: slight downward baseline during tracking
    this.baseVerticalOffset = -0.3; // small baseline lowering
    // Base horizontal offset: positive moves model to the right
    this.baseHorizontalOffset = 1;
    this.distanceFromCamera = 0;
    this.targetDistance = 0;
    this.distanceSmoothing = 0.2;

    // T-shirt specific positioning constraints
    this.tshirtMinY = -2.0; // Minimum Y position to prevent dropping too low
    this.tshirtMaxY = 1.2;  // Allow slightly higher placement
    this.tshirtStabilityBuffer = 0.1; // Buffer zone for smooth constraint application
    this.tshirtConstraintSmoothing = 0.8; // Smooth constraint application
    this.preserveClothPhysics = true; // Maintain realistic drape behavior

    // Scaling-aware alignment configuration
    this.scaleCompensationMode = 'lower_model'; // 'lower_model' | 'raise_camera'
    this.scaleToYOffsetRatio = 0.35; // proportion of height to lower per +1 scale
    this.scaleToCameraYOffsetRatio = 0.25; // proportion of height to raise camera per +1 scale
    this.scaleToZPushRatio = 0.0; // optional push back along Z per +1 scale (0 disables)
    this.modelHeightWorld = null; // computed from bounding box
    this.prevFinalScale = this.modelScale; // track last scale applied
    this.baseCameraY = 0; // default camera y
    this.cameraYOffsetBlend = 0; // smoothed camera y offset when mode is raise_camera
    this.cameraYOffsetSmoothing = 0.3; // blend speed for camera raise

    // Filter-like tracking properties
    this.filterMode = true; // Enable filter-like behavior by default
    this.instantFollow = true; // Instant following for filter-like behavior
    this.tightTracking = true; // Tighter tracking for filter-like behavior
    this.filterSmoothing = 0.25; // Lower sensitivity for rotations when not instant
    this.modelSizeMultiplier = 1.0; // Keep multiplier neutral to avoid oversized appearance

    // Separate smoothing for shoulders vs arms (shoulders less sensitive)
    this.shoulderSmoothing = 0.25; // more natural, slower chest orientation
    this.armSmoothing = 0.25;      // slower arm slerp for natural follow

    // Sensitivity and filtering for vertical motion and arms
    // Reduce y-axis responsiveness and add a dead zone to ignore small fluctuations
    this.yAxisResponsiveness = 0.6; // ~40% reduction in vertical responsiveness
    this.yUpResponsiveness = 0.4;   // damp upward movement more strongly
    this.yDownResponsiveness = 0.8; // allow downward settling more naturally
    this.yDeadZoneWorld = ((this.stabilityPositionThreshold || 0.02) * 1.4);

    // Arm movement smoothing and minor-motion dead zone
    this.armFilterAlpha = 0.60; // stronger smoothing to reduce sleeve sensitivity
    this.armDeadZoneAngleRad = 0.10; // bigger dead zone to ignore micro jitter
    this.armFilters = {
      L: { shoulder: null, elbow: null, wrist: null },
      R: { shoulder: null, elbow: null, wrist: null }
    };

    // Arm resting pose tuning
    this.armDownBias = 1.0; // stronger pull toward down when elbow below shoulder
    this.armDownThreshold = 0.35; // engage "down" more readily, suppress raising
    // Use heuristic follow to avoid sleeves raising
    this.armDirectFollow = false; // allow downward bias/clamps to apply
    this.armInvertVertical = false; // set true if your camera feed inverts Y
    this.armInvertDepth = true;   // set true if model uses opposite Z

    // Optional base X rotation if model faces a different axis
    this.modelBaseXRotation = 0; // set to Math.PI or Math.PI/2 if needed
    // Optional base Y rotation: face directly toward camera (-Z)
    // Set to 0 so the shirt faces the center by default
    this.modelBaseYRotation = -0.8;
    // Quick correction toggles removed

    // Stability thresholds to reduce jitter while keeping sensitivity
    this.stabilityPositionThreshold = 0.02; // world units
    this.stabilityRotationThreshold = 0.02; // radians
    this.stabilityScaleThreshold = 0.005; // unitless

    // Mobile/performance
    this.mobileMode = false;
    this.performanceMode = false;
    this.frameSkip = 1;
    this.frameCount = 0;
    this.debugLogging = true;
    this.debugLogEveryNFrames = 60; // log roughly once per second at 60fps
  }

  // Compute a torso-aligned screen-space rectangle from MediaPipe landmarks.
  // Uses the same camera projection and follow depth as the model mapping.
  // offsetYWorld applies an extra vertical shift in world units (e.g., -5).
  // Returns { left, top, width, height, visible } in CSS pixels.
  getTorsoScreenRectFromLandmarks(landmarks, canvas, offsetYWorld = 0) {
    if (!landmarks || !this.camera || !canvas) return { visible: false };
    const d = this.followDepth;
    const lSh = this.normalizedToWorld(landmarks[11].x, landmarks[11].y, d);
    const rSh = this.normalizedToWorld(landmarks[12].x, landmarks[12].y, d);
    const lHip = this.normalizedToWorld(landmarks[23].x, landmarks[23].y, d);
    const rHip = this.normalizedToWorld(landmarks[24].x, landmarks[24].y, d);
    // Apply extra vertical offset in world space
    const off = offsetYWorld + (this.baseVerticalOffset || 0);
    lSh.y += off; rSh.y += off; lHip.y += off; rHip.y += off;
    const pts = [lSh, rSh, lHip, rHip];
    // Use CSS pixel sizes exclusively so devicePixelRatio (browser zoom) has no effect
    const w = canvas.clientWidth;
    const h = canvas.clientHeight;
    let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
    let anyVisible = false;
    for (const p of pts) {
      const ndc = p.clone().project(this.camera);
      if (ndc.z >= -1 && ndc.z <= 1) anyVisible = true;
      const x = (ndc.x * 0.5 + 0.5) * w;
      const y = (-ndc.y * 0.5 + 0.5) * h;
      minX = Math.min(minX, x);
      minY = Math.min(minY, y);
      maxX = Math.max(maxX, x);
      maxY = Math.max(maxY, y);
    }
    if (!anyVisible || !isFinite(minX) || !isFinite(minY) || !isFinite(maxX) || !isFinite(maxY)) return { visible: false };
    const left = Math.max(0, Math.min(w, minX));
    const top = Math.max(0, Math.min(h, minY));
    const right = Math.max(0, Math.min(w, maxX));
    const bottom = Math.max(0, Math.min(h, maxY));
    const width = Math.max(0, right - left);
    const height = Math.max(0, bottom - top);
    if (width < 2 || height < 2) return { visible: false };
    return { left, top, width, height, visible: true };
  }

  // Initialize scene, camera, renderer
  init(canvasEl) {
    this.scene = new THREE.Scene();
    this.scene.background = null; // transparent for overlay look
    this.camera = new THREE.PerspectiveCamera(45, canvasEl.clientWidth / canvasEl.clientHeight, 0.1, 100);
    // Guard against unintended zoom changes
    this.camera.zoom = 1;
    this.camera.updateProjectionMatrix();
    this.camera.position.set(0, 1.4, 1.6);
    this.camera.lookAt(0, 1.4, 0);

    this.renderer = new THREE.WebGLRenderer({ canvas: canvasEl, antialias: true, alpha: true });
    this.renderer.setSize(canvasEl.clientWidth, canvasEl.clientHeight);
    // Lock pixel ratio to 1 so browser zoom does not affect rendering scale
    this.renderer.setPixelRatio(1);

    // Lights
    const hemi = new THREE.HemisphereLight(0xffffff, 0x444444, 1.2);
    this.scene.add(hemi);
    const dir = new THREE.DirectionalLight(0xffffff, 0.8);
    dir.position.set(2, 4, 2);
    this.scene.add(dir);

    // Debug helpers: axes at origin to understand orientation
    const axes = new THREE.AxesHelper(0.5);
    this.scene.add(axes);

    // Resize handler
    const onResize = () => {
      const w = canvasEl.clientWidth, h = canvasEl.clientHeight;
      this.camera.aspect = w / h;
      this.camera.updateProjectionMatrix();
      // Keep renderer size in CSS pixels and ignore devicePixelRatio
      this.renderer.setPixelRatio(1);
      this.renderer.setSize(w, h);
    };
    window.addEventListener('resize', onResize);
    onResize();
  }

  // Convert normalized screen coords (0..1) to world space at a given distance
  normalizedToWorld(nx, ny, distance) {
    const xNDC = (nx - 0.5) * 2;
    const yNDC = (0.5 - ny) * 2; // invert Y
    const ndc = new THREE.Vector3(xNDC, yNDC, 0.5);
    ndc.unproject(this.camera);
    const dir = ndc.sub(this.camera.position).normalize();
    const world = this.camera.position.clone().add(dir.multiplyScalar(distance));
    return world;
  }

  // Load GLB model and map bones
  async loadModel(path) {
    const loader = new GLTFLoader();
    const candidates = [path].filter(Boolean).length ? [path] : this.modelPathCandidates;

    let gltf = null;
    let chosenPath = null;
    for (const candidate of candidates) {
      console.log('[VTO] Trying GLB path', candidate);
      try {
        gltf = await loader.loadAsync(candidate);
        chosenPath = candidate;
        break;
      } catch (err) {
        console.warn('[VTO] Load failed, retrying with cache-busting', { candidate, err });
        const retryPath = candidate.includes('?') ? candidate + '&v=' + Date.now() : candidate + '?v=' + Date.now();
        try {
          gltf = await loader.loadAsync(retryPath);
          chosenPath = retryPath;
          console.warn('[VTO] GLB loaded on retry', retryPath);
          break;
        } catch (err2) {
          console.error('[VTO] Retry failed for GLB', { retryPath, err2 });
        }
      }
    }
    if (!gltf) {
      console.error('[VTO] All GLB path candidates failed', this.modelPathCandidates);
      this.isLoading = false;
      return; // Fail quietly to let UI continue; status text can explain
    }
    this.modelPath = chosenPath;
    this.boneModel = gltf.scene;
    // Ensure materials are visible and not culled; apply special handling for 'Human' mesh
    this.boneModel.traverse((o) => {
      if (o.isMesh && o.material) {
        const applyMatProps = (mat, isHuman) => {
          if (!mat) return;
          mat.side = THREE.DoubleSide;
          if ('transparent' in mat) mat.transparent = false;
          if ('opacity' in mat) mat.opacity = 1;
          if ('depthWrite' in mat) mat.depthWrite = true;
          if (isHuman && 'colorWrite' in mat) mat.colorWrite = false; // write depth only
        };
        const isHuman = (o.name || '').toLowerCase().includes('human');
        if (Array.isArray(o.material)) {
          o.material.forEach((m) => applyMatProps(m, isHuman));
        } else {
          applyMatProps(o.material, isHuman);
        }
        o.castShadow = true;
        o.receiveShadow = true;
      }
    });

    // Apply static base orientation only (no dynamic rotation)
    this.boneModel.rotation.y = (this.modelBaseYRotation || 0);
    // Optional auto-upright: guard behind flag
    if (this.autoUprightEnabled) {
      try {
        const preBox = new THREE.Box3().setFromObject(this.boneModel);
        const preSize = new THREE.Vector3();
        preBox.getSize(preSize);
        if (preSize.z > preSize.y * 1.2) {
          this.boneModel.rotation.x = -Math.PI / 2;
          console.warn('[VTO] Auto-upright applied: X rotation -90°');
        }
      } catch (e) {
        console.warn('[VTO] Upright check failed', e);
      }
    }
    // Apply optional base X rotation if your model faces a different axis
    if (this.modelBaseXRotation !== 0) {
      this.boneModel.rotation.x = this.modelBaseXRotation;
    }
    this.boneModel.position.y = this.modelPositionY;
    this.boneModel.position.z = this.modelPositionZ;
    this.boneModel.scale.setScalar(this.modelScale * this.modelSizeMultiplier);
    // Debug bounding box helper removed per request

    // Map bones by name (case-insensitive). Prefer true Bone nodes; add fallback for non-Bone transforms.
    this.boneModel.traverse((obj) => {
      const name = (obj.name || '').toLowerCase();
      if (!name) return;
      const isBoneLike = obj.isBone || obj.type === 'Bone';
      // Shoulders
      if (name.includes('shoulder')) {
        if ((name.includes('l') || name.includes('left'))) this.leftShoulderBone = this.leftShoulderBone || (isBoneLike ? obj : null);
        if ((name.includes('r') || name.includes('right') || name.includes('.r') || name.endsWith('_r'))) this.rightShoulderBone = this.rightShoulderBone || (isBoneLike ? obj : null);
      }
      // Spine / chest / root
      if ((name.includes('spine') || name.includes('chest')) && isBoneLike) this.spineBone = this.spineBone || obj;
      if ((name.includes('root') || name.includes('hips')) && isBoneLike) this.rootBone = this.rootBone || obj;
      // Upper arms
      const isForearm = name.includes('forearm') || name.includes('lowerarm');
      const isUpperArm = name.includes('upperarm') || (name.includes('arm') && !isForearm);
      if (isUpperArm) {
        if ((name.includes('l') || name.includes('left'))) this.leftArmBone = this.leftArmBone || (isBoneLike ? obj : null);
        if ((name.includes('r') || name.includes('right') || name.includes('.r') || name.endsWith('_r'))) this.rightArmBone = this.rightArmBone || (isBoneLike ? obj : null);
      }
      // Forearms
      if (isForearm) {
        if ((name.includes('l') || name.includes('left'))) this.leftForearmBone = this.leftForearmBone || (isBoneLike ? obj : null);
        if ((name.includes('r') || name.includes('right') || name.includes('.r') || name.endsWith('_r'))) this.rightForearmBone = this.rightForearmBone || (isBoneLike ? obj : null);
      }
    });

    // Fallback: if any right-side bones are missing, search non-Bone transforms (excluding meshes)
    const findNode = (patterns) => {
      let found = null;
      this.boneModel.traverse((o) => {
        if (found) return;
        const n = (o.name || '').toLowerCase();
        if (!n || o.isMesh) return;
        if (patterns.every((p) => n.includes(p))) found = o;
      });
      return found;
    };
    if (!this.rightShoulderBone) this.rightShoulderBone = findNode(['shoulder', 'r']) || findNode(['shoulder', 'right']);
    if (!this.rightArmBone) this.rightArmBone = findNode(['arm', 'r']) || findNode(['upperarm', 'r']) || findNode(['arm', 'right']) || findNode(['upperarm', 'right']);
    if (!this.rightForearmBone) this.rightForearmBone = findNode(['forearm', 'r']) || findNode(['lowerarm', 'r']) || findNode(['forearm', 'right']) || findNode(['lowerarm', 'right']);

    // Detect per-bone local aim axes based on bind pose (child direction)
    const detectAimAxis = (bone) => {
      if (!bone || !bone.children || bone.children.length === 0) return new THREE.Vector3(0, -1, 0);
      const boneWorld = new THREE.Vector3();
      bone.getWorldPosition(boneWorld);
      const childWorld = new THREE.Vector3();
      bone.children[0].getWorldPosition(childWorld);
      const dirWorld = childWorld.sub(boneWorld).normalize();
      const qWorld = new THREE.Quaternion();
      bone.getWorldQuaternion(qWorld);
      const qInv = qWorld.clone().invert();
      const dirLocal = dirWorld.clone().applyQuaternion(qInv);
      // Snap to dominant axis with sign
      const ax = Math.abs(dirLocal.x), ay = Math.abs(dirLocal.y), az = Math.abs(dirLocal.z);
      if (ax >= ay && ax >= az) return new THREE.Vector3(Math.sign(dirLocal.x) || 1, 0, 0);
      if (ay >= ax && ay >= az) return new THREE.Vector3(0, Math.sign(dirLocal.y) || -1, 0);
      return new THREE.Vector3(0, 0, Math.sign(dirLocal.z) || -1);
    };
    this.leftUpperAimAxis = detectAimAxis(this.leftArmBone) || this.leftUpperAimAxis;
    this.rightUpperAimAxis = detectAimAxis(this.rightArmBone) || this.rightUpperAimAxis;
    this.leftForeAimAxis = detectAimAxis(this.leftForearmBone) || this.leftForeAimAxis;
    this.rightForeAimAxis = detectAimAxis(this.rightForearmBone) || this.rightForeAimAxis;

    // Log mapped bones for verification
    console.log('[VTO] Bone map', {
      leftShoulder: this.leftShoulderBone?.name,
      rightShoulder: this.rightShoulderBone?.name,
      leftArm: this.leftArmBone?.name,
      rightArm: this.rightArmBone?.name,
      leftForearm: this.leftForearmBone?.name,
      rightForearm: this.rightForearmBone?.name,
    });
    console.log('[VTO] Aim axes', {
      leftUpper: this.leftUpperAimAxis.toArray(),
      rightUpper: this.rightUpperAimAxis.toArray(),
      leftFore: this.leftForeAimAxis.toArray(),
      rightFore: this.rightForeAimAxis.toArray(),
    });
    // Debug: count meshes for visibility assurance
    let meshCount = 0;
    this.boneModel.traverse((o) => { if (o.isMesh) meshCount++; });

    this.scene.add(this.boneModel);

    // Compute model height once
    const box = new THREE.Box3().setFromObject(this.boneModel);
    this.modelHeightWorld = box.max.y - box.min.y;

    if (this.debugLogging) {
      console.log('[VTO] Model loaded');
      console.log('[VTO] meshCount', meshCount);
      console.log('[VTO] boneModel.position', {
        x: this.boneModel.position.x,
        y: this.boneModel.position.y,
        z: this.boneModel.position.z,
      });
      if (this.rootBone) {
        console.log('[VTO] rootBone.position', {
          x: this.rootBone.position.x,
          y: this.rootBone.position.y,
          z: this.rootBone.position.z,
        });
      } else {
        console.log('[VTO] rootBone not found yet');
      }
      console.log('[VTO] modelHeightWorld', this.modelHeightWorld);
    }

    this.isLoading = false;
  }

  // Update model based on MediaPipe landmarks
  updateFromLandmarks(landmarks) {
    if (!landmarks || !this.boneModel) return;

    // Map normalized landmarks (0..1) into world coordinates at a fixed depth
    const d = this.followDepth;
    const leftShoulder = this.normalizedToWorld(landmarks[11].x, landmarks[11].y, d);
    const rightShoulder = this.normalizedToWorld(landmarks[12].x, landmarks[12].y, d);
    const leftElbow = this.normalizedToWorld(landmarks[13].x, landmarks[13].y, d);
    const rightElbow = this.normalizedToWorld(landmarks[14].x, landmarks[14].y, d);
    const leftWrist = this.normalizedToWorld(landmarks[15].x, landmarks[15].y, d);
    const rightWrist = this.normalizedToWorld(landmarks[16].x, landmarks[16].y, d);
    const leftHip = this.normalizedToWorld(landmarks[23].x, landmarks[23].y, d);
    const rightHip = this.normalizedToWorld(landmarks[24].x, landmarks[24].y, d);

    // Body center
    const midShoulder = leftShoulder.clone().add(rightShoulder).multiplyScalar(0.5);
    const midHip = leftHip.clone().add(rightHip).multiplyScalar(0.5);
    const up = midShoulder.clone().sub(midHip).normalize();
    // Shoulder vector (used for optional chest orientation only)
    const shoulderVec = rightShoulder.clone().sub(leftShoulder);

    // Body position (mid torso)
    this.targetBodyPosition.copy(midShoulder);
    this.targetBodyPosition.y += this.baseVerticalOffset;
    // Apply horizontal offset (move model right/left without changing rotation)
    this.targetBodyPosition.x += (this.baseHorizontalOffset || 0);
    // Position response using configurable alpha
    this.bodyPosition.lerp(this.targetBodyPosition, this.positionFollowAlpha);

    // Apply downward offset proportional to user scale (50% sensitivity, downward only)
    const scaleDelta = Math.max(0, (this.modelSizeMultiplier || 1) - 1.0);
    if (scaleDelta > 0) {
      this.bodyPosition.y += -(0.5 * scaleDelta);
    }

    // Clamp vertical position to avoid dropping too low or rising too high
    if (isFinite(this.tshirtMinY) && isFinite(this.tshirtMaxY)) {
      this.bodyPosition.y = THREE.MathUtils.clamp(
        this.bodyPosition.y,
        this.tshirtMinY,
        this.tshirtMaxY
      );
    }

    // Body scale from shoulder width
    const shoulderDist = leftShoulder.distanceTo(rightShoulder);
    // World-based scaling: compare shoulder width at depth to baseline
    const baselineWidth = 0.55; // larger baseline shrinks computed scale
    this.targetBodyScale = THREE.MathUtils.clamp(shoulderDist / baselineWidth, 0.85, 1.1);
    this.bodyScale = THREE.MathUtils.damp(this.bodyScale, this.targetBodyScale, this.bodySmoothing, 1/60);

    // Apply to root
    if (this.rootBone) {
      this.rootBone.position.copy(this.bodyPosition);
      // No dynamic yaw: keep static base orientation
      this.rootBone.rotation.y = (this.modelBaseYRotation || 0);
      // Include user scale multiplier when root exists
      this.rootBone.scale.setScalar((this.autoScaleEnabled ? this.bodyScale : 1.0) * this.modelSizeMultiplier);
    } else {
      // Fallback when no armature root exists: move the entire model
      this.boneModel.position.copy(this.bodyPosition);
      // No dynamic yaw: keep static base orientation
      this.boneModel.rotation.y = (this.modelBaseYRotation || 0);
      this.boneModel.scale.setScalar(this.modelScale * this.modelSizeMultiplier * (this.autoScaleEnabled ? this.bodyScale : 1.0));
    }

    // Spine / chest orientation: derive forward from shoulder axis and up
    if (this.spineBone && this.spineChestFollowEnabled) {
      const chestUp = up.clone().normalize();
      const shoulderDir = shoulderVec.clone().normalize();
      let chestForward = new THREE.Vector3().crossVectors(shoulderDir, chestUp).normalize();
      // Fallback when vectors are nearly parallel
      if (chestForward.lengthSq() < 1e-6) chestForward.set(0, 0, -1);
      const m = new THREE.Matrix4().lookAt(new THREE.Vector3(), chestForward, chestUp);
      const q = new THREE.Quaternion().setFromRotationMatrix(m);
      this.spineBone.quaternion.slerp(q, this.shoulderSmoothing);
    }

    // Arms
    const aimBoneToDir = (bone, localAimAxis, targetDirWorld, smoothing) => {
      if (!bone || !targetDirWorld) return;
      const parentQuat = new THREE.Quaternion();
      if (bone.parent) bone.parent.getWorldQuaternion(parentQuat); else parentQuat.identity();
      const parentInv = parentQuat.clone().invert();
      // Convert world target dir into parent space
      const dirParent = targetDirWorld.clone().normalize().applyQuaternion(parentInv).normalize();
      // Auto-flip aim axis sign to minimize rotation angle
      let src = localAimAxis.clone().normalize();
      if (src.dot(dirParent) < 0) src.multiplyScalar(-1);
      const qLocal = new THREE.Quaternion().setFromUnitVectors(src, dirParent);
      if (smoothing && smoothing > 0 && smoothing < 1) bone.quaternion.slerp(qLocal, smoothing);
      else bone.quaternion.copy(qLocal);
    };

    const applyArm = (isLeft, shoulder, elbow, wrist, shoulderBone, upperArmBone, forearmBone) => {
      // Direct follow mode: bypass resting-pose bias and clamps
      if (this.armDirectFollow) {
        let upper = elbow.clone().sub(shoulder).normalize();
        let fore = wrist.clone().sub(elbow).normalize();
        if (this.armInvertVertical) {
          upper.y *= -1; fore.y *= -1;
        }
        if (this.armInvertDepth) {
          upper.z *= -1; fore.z *= -1;
        }
        aimBoneToDir(upperArmBone, isLeft ? this.leftUpperAimAxis : this.rightUpperAimAxis, upper, this.armSmoothing);
        aimBoneToDir(forearmBone, isLeft ? this.leftForeAimAxis : this.rightForeAimAxis, fore, this.armSmoothing);
        return;
      }
      // Compute outward direction from torso (left is -shoulderVec, right is +shoulderVec)
      const torsoRight = shoulderVec.clone().normalize();
      const outward = isLeft ? torsoRight.clone().multiplyScalar(-1) : torsoRight;
      // Upper arm desired direction
      let dirUpperW = elbow.clone().sub(shoulder).normalize();
      const isRight = !isLeft;
      // If elbow sits below shoulder, bias arm direction toward a downward reference
      const verticalDelta = shoulder.y - elbow.y; // >0 means elbow is lower
      if (verticalDelta > 0 && this.armDownBias > 0) {
        const downRef = new THREE.Vector3(0, -1, 0).add(outward.clone().multiplyScalar(0.25)).normalize();
        const effectiveThreshold = isRight ? Math.max(0.2, this.armDownThreshold * 0.7) : this.armDownThreshold;
        const effectiveBias = isRight ? this.armDownBias * 1.2 : this.armDownBias;
        let downFactor = THREE.MathUtils.clamp((verticalDelta / effectiveThreshold) * effectiveBias, 0, 1);
        // Engage a strong downward override when elbow is below shoulder
        downFactor = Math.max(downFactor, 0.9);
        // Pull toward down reference
        dirUpperW.lerp(downRef, downFactor).normalize();
        // Hard elevation clamp so relaxed arms don’t float
        const clampUpperYMin = isRight ? -0.8 : -0.6;
        if (dirUpperW.y > clampUpperYMin) {
          dirUpperW.y = clampUpperYMin;
          // Re-project to keep outward component non-inward
          const projOut = outward.clone().multiplyScalar(Math.max(0.0, dirUpperW.dot(outward)));
          const downComp = new THREE.Vector3(0, -1, 0).multiplyScalar(Math.sqrt(Math.max(0.0, 1 - projOut.lengthSq())));
          dirUpperW.copy(projOut.add(downComp).normalize());
        }
      }
      // Strong rest pose: if BOTH elbow and wrist are below shoulder, force a down+out pose
      const wristBelow = wrist.y < (shoulder.y - 0.04);
      const elbowBelow = elbow.y < (shoulder.y - 0.04);
      if (wristBelow && elbowBelow) {
        const outwardWeight = isRight ? 0.7 : 0.5;
        const downOut = new THREE.Vector3(0, -1, 0).add(outward.clone().multiplyScalar(outwardWeight)).normalize();
        // Override: force a firm down-and-out pose to keep sleeves down
        dirUpperW.copy(downOut).normalize();
      }
      // Ensure any below-shoulder posture keeps a strong downward component
      if (wristBelow || elbowBelow) {
        const clampRestUpperYMin = isRight ? -0.65 : -0.5;
        if (dirUpperW.y > clampRestUpperYMin) {
          dirUpperW.y = clampRestUpperYMin;
          const projOut = outward.clone().multiplyScalar(Math.max(0.0, dirUpperW.dot(outward)));
          const downComp = new THREE.Vector3(0, -1, 0).multiplyScalar(Math.sqrt(Math.max(0.0, 1 - projOut.lengthSq())));
          dirUpperW.copy(projOut.add(downComp).normalize());
        }
      }
      // Prevent arm pointing inward across torso: reflect inward component
      const dotOut = dirUpperW.dot(outward);
      if (dotOut < -0.05) {
        const inwardComp = outward.clone().multiplyScalar(dotOut);
        dirUpperW.sub(inwardComp.multiplyScalar(2)).normalize();
      }
      aimBoneToDir(upperArmBone, isLeft ? this.leftUpperAimAxis : this.rightUpperAimAxis, dirUpperW, this.armSmoothing);
      // Forearm follows elbow->wrist, with minimal upward allowance in rest
      const dirForeW = wrist.clone().sub(elbow).normalize();
      const clampForeYMin = isRight ? -0.5 : -0.35;
      if (wristBelow && elbowBelow && dirForeW.y > clampForeYMin) {
        dirForeW.y = clampForeYMin;
        const projOutFore = outward.clone().multiplyScalar(Math.max(0.0, dirForeW.dot(outward)));
        const downCompFore = new THREE.Vector3(0, -1, 0).multiplyScalar(Math.sqrt(Math.max(0.0, 1 - projOutFore.lengthSq())));
        dirForeW.copy(projOutFore.add(downCompFore).normalize());
      }
      aimBoneToDir(forearmBone, isLeft ? this.leftForeAimAxis : this.rightForeAimAxis, dirForeW, this.armSmoothing);
    };

    // Always map left landmarks to left bones and right to right
    applyArm(true, leftShoulder, leftElbow, leftWrist, this.leftShoulderBone, this.leftArmBone, this.leftForearmBone);
    applyArm(false, rightShoulder, rightElbow, rightWrist, this.rightShoulderBone, this.rightArmBone, this.rightForearmBone);

    if (this.debugLogging) {
      this.frameCount++;
      if (this.frameCount % this.debugLogEveryNFrames === 0) {
        console.log('[VTO] Frame', this.frameCount);
        console.log('[VTO] boneModel.position', {
          x: this.boneModel.position.x,
          y: this.boneModel.position.y,
          z: this.boneModel.position.z,
        });
        if (this.rootBone) {
          console.log('[VTO] rootBone.position', {
            x: this.rootBone.position.x,
            y: this.rootBone.position.y,
            z: this.rootBone.position.z,
          });
        }
        console.log('[VTO] camera.position', {
          x: this.camera.position.x,
          y: this.camera.position.y,
          z: this.camera.position.z,
        });
      }
    }
  }

  

  // Compute the model's projected screen-space bounding rectangle in CSS pixels
  // relative to the renderer canvas. Returns { left, top, width, height, visible }.
  getModelScreenRect(canvas) {
    if (!this.boneModel || !this.camera || !canvas) return { visible: false };
    const box = new THREE.Box3().setFromObject(this.boneModel);
    if (!isFinite(box.min.x) || !isFinite(box.max.x)) return { visible: false };
    const corners = [
      new THREE.Vector3(box.min.x, box.min.y, box.min.z),
      new THREE.Vector3(box.min.x, box.min.y, box.max.z),
      new THREE.Vector3(box.min.x, box.max.y, box.min.z),
      new THREE.Vector3(box.min.x, box.max.y, box.max.z),
      new THREE.Vector3(box.max.x, box.min.y, box.min.z),
      new THREE.Vector3(box.max.x, box.min.y, box.max.z),
      new THREE.Vector3(box.max.x, box.max.y, box.min.z),
      new THREE.Vector3(box.max.x, box.max.y, box.max.z)
    ];
    // Use CSS pixel sizes exclusively so devicePixelRatio (browser zoom) has no effect
    const w = canvas.clientWidth;
    const h = canvas.clientHeight;
    let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
    let anyVisible = false;
    for (const c of corners) {
      const ndc = c.clone().project(this.camera);
      if (ndc.z >= -1 && ndc.z <= 1) anyVisible = true;
      const x = (ndc.x * 0.5 + 0.5) * w;
      const y = (-ndc.y * 0.5 + 0.5) * h;
      minX = Math.min(minX, x);
      minY = Math.min(minY, y);
      maxX = Math.max(maxX, x);
      maxY = Math.max(maxY, y);
    }
    if (!anyVisible || !isFinite(minX) || !isFinite(minY) || !isFinite(maxX) || !isFinite(maxY)) return { visible: false };
    const left = Math.max(0, Math.min(w, minX));
    const top = Math.max(0, Math.min(h, minY));
    const right = Math.max(0, Math.min(w, maxX));
    const bottom = Math.max(0, Math.min(h, maxY));
    const width = Math.max(0, right - left);
    const height = Math.max(0, bottom - top);
    if (width < 2 || height < 2) return { visible: false };
    return { left, top, width, height, visible: true };
  }

  // Render loop
  render() {
    if (this.renderer && this.scene && this.camera) {
      this.renderer.render(this.scene, this.camera);
    }
  }

  // Dispose
  dispose() {
    if (this.renderer) this.renderer.dispose();
    if (this.boneModel) this.scene.remove(this.boneModel);
    window.removeEventListener('resize', this.onResize);
  }
}