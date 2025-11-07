import * as THREE from 'three';

// Basic verification: compare joint Euler rotations with clinical ranges.
// In a production system, this should be computed in anatomical planes.

export function verifyPose(rig, landmarks) {
  const items = [];
  const { bones, constraints } = rig;

  function check(boneName, label) {
    const bone = bones[boneName];
    const c = constraints[boneName];
    if (!bone || !c) return;
    const e = bone.rotation;
    const withinX = e.x >= c.min.x && e.x <= c.max.x;
    const withinY = e.y >= c.min.y && e.y <= c.max.y;
    const withinZ = e.z >= c.min.z && e.z <= c.max.z;
    const ok = withinX && withinY && withinZ;
    const status = ok ? 'ok' : (withinX || withinY || withinZ) ? 'warn' : 'bad';
    const msg = `x:${toDeg(e.x)}° y:${toDeg(e.y)}° z:${toDeg(e.z)}°`;
    items.push({ joint: label, status, message: msg });
  }

  check('Chest', 'Chest');
  check('UpperArmR', 'Right Upper Arm');
  check('UpperArmL', 'Left Upper Arm');

  // Simple COM estimate based on shoulders and hips; warn if torso lean exceeds thresholds
  if (landmarks) {
    const lShoulder = landmarks[11];
    const rShoulder = landmarks[12];
    const lHip = landmarks[23];
    const rHip = landmarks[24];
    const shouldersMid = new THREE.Vector3(
      (lShoulder.x + rShoulder.x) / 2,
      (lShoulder.y + rShoulder.y) / 2,
      (lShoulder.z + rShoulder.z) / 2,
    );
    const hipsMid = new THREE.Vector3(
      (lHip.x + rHip.x) / 2,
      (lHip.y + rHip.y) / 2,
      (lHip.z + rHip.z) / 2,
    );
    const torsoVec = new THREE.Vector3().subVectors(shouldersMid, hipsMid).normalize();
    const vertical = new THREE.Vector3(0, -1, 0);
    const angle = Math.acos(THREE.MathUtils.clamp(torsoVec.dot(vertical), -1, 1));
    const angleDeg = toDeg(angle);
    const status = angleDeg < 25 ? 'ok' : angleDeg < 35 ? 'warn' : 'bad';
    items.push({ joint: 'Torso Lean', status, message: `${angleDeg.toFixed(1)}°` });
  }

  return { items };
}

function toDeg(rad) {
  return (rad * 180) / Math.PI;
}