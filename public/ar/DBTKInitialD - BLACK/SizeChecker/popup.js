// Modal: Instruction Wizard (integrated)
function openInstruction() {
  const modal = document.getElementById('instructionModal');
  if (modal) modal.setAttribute('aria-hidden', 'false');
}
function closeInstruction() {
  const modal = document.getElementById('instructionModal');
  if (!modal) return;
  modal.setAttribute('aria-hidden', 'true');
  // reset wizard when closing
  resetWizard();
  // Make Start button visible to prepare size checking
  const startButton = document.getElementById('startButton');
  if (startButton) startButton.style.display = '';
  // Reset inline UI prompts to start state
  const calibrationInstructions = document.getElementById('calibrationInstructions');
  if (calibrationInstructions) calibrationInstructions.style.display = 'none';
  const sizeResult = document.getElementById('sizeResult');
  if (sizeResult) {
    sizeResult.style.display = 'block';
    sizeResult.textContent = 'Press Start to begin';
  }
}

// Wizard state (inches)
let wizardState = {
  chestIn: null,
  heightIn: null,
  step: 1,
};

function resetWizard() {
  wizardState = { chestIn: null, heightIn: null, step: 1 };
  const steps = document.querySelectorAll('#instructionModal .step');
  steps.forEach((s, i) => {
    if (i === 0) s.hidden = false; else s.hidden = true;
    s.classList.remove('leaving');
  });
  const chestEl = document.getElementById('chestWidth');
  const heightEl = document.getElementById('heightIn');
  if (chestEl) chestEl.value = '';
  if (heightEl) heightEl.value = '';
  const resultText = document.getElementById('resultText');
  if (resultText) resultText.textContent = '';
  const disclaimerBlock = document.getElementById('disclaimerBlock');
  if (disclaimerBlock) disclaimerBlock.hidden = true;
}

function goToStep(step) {
  const container = document.getElementById('instructionModal');
  if (!container) return;
  const current = container.querySelector(`.step[data-step="${wizardState.step}"]`);
  const next = container.querySelector(`.step[data-step="${step}"]`);
  if (current && next) {
    current.classList.add('leaving');
    setTimeout(() => {
      current.hidden = true;
      current.classList.remove('leaving');
      next.hidden = false;
      wizardState.step = step;
    }, 240);
  }
}

// Size chart from image (inches)
const SIZE_CHART = [
  { size: 'XS', chest: 18, height: 25 },
  { size: 'S',  chest: 19, height: 26 },
  { size: 'M',  chest: 20, height: 27 },
  { size: 'L',  chest: 21, height: 28 },
  { size: 'XL', chest: 22, height: 29 },
  { size: '2XL', chest: 23, height: 30 },
];

function nearestByKey(value, key) {
  let best = SIZE_CHART[0];
  let bestDist = Infinity;
  for (const row of SIZE_CHART) {
    const d = Math.abs(row[key] - value);
    if (d < bestDist) { bestDist = d; best = row; }
  }
  return best;
}

function bestMatch(chest, height) {
  // Sum of absolute differences; if one missing, fallback to single-key nearest
  if (typeof chest === 'number' && typeof height === 'number') {
    let best = SIZE_CHART[0];
    let bestScore = Infinity;
    for (const row of SIZE_CHART) {
      const score = Math.abs(row.chest - chest) + Math.abs(row.height - height);
      if (score < bestScore) { bestScore = score; best = row; }
    }
    return best;
  }
  if (typeof chest === 'number') return nearestByKey(chest, 'chest');
  if (typeof height === 'number') return nearestByKey(height, 'height');
  return null;
}

function renderChart(recommended) {
  const container = document.getElementById('chartContainer');
  if (!container) return;
  const rows = SIZE_CHART.map(row => {
    const cls = recommended && row.size === recommended.size ? ' class="recommended"' : '';
    return `<tr${cls}><td>${row.size}</td><td>${row.chest}</td><td>${row.height}</td></tr>`;
  }).join('');
  container.innerHTML = `
    <div class="chart-title">Size Chart — Men’s T-Shirts (inches)</div>
    <table class="chart-table">
      <thead><tr><th>Size</th><th>Chest</th><th>Height</th></tr></thead>
      <tbody>${rows}</tbody>
    </table>
    <div class="chart-note">Chest: measured ~2" below armhole across garment. Height: collar top to bottom opening.</div>
  `;
}

function computeOutcome() {
  const resultText = document.getElementById('resultText');
  if (!resultText) return;

  const hasChest = typeof wizardState.chestIn === 'number';
  const hasHeight = typeof wizardState.heightIn === 'number';
  const disclaimerBlock = document.getElementById('disclaimerBlock');
  const chartContainer = document.getElementById('chartContainer');

  const CHEST_MIN = 18, CHEST_MAX = 23;
  const HEIGHT_MIN = 25, HEIGHT_MAX = 30;
  const inRange = (v, min, max) => typeof v === 'number' && v >= min && v <= max;

  // If one or both inputs are missing, show No Result with disclaimer and no chart
  if (!(hasChest && hasHeight)) {
    resultText.textContent = "No result: provide chest width and height for a suggestion. Disclaimer: there’s a chance that this might not be your size; we are just suggesting. Follow at your own risk.";
    if (disclaimerBlock) disclaimerBlock.hidden = false;
    if (chartContainer) chartContainer.innerHTML = '';
    return;
  }

  // Both provided => show size suggestion from chart
  const outOfRange = (!inRange(wizardState.chestIn, CHEST_MIN, CHEST_MAX)) || (!inRange(wizardState.heightIn, HEIGHT_MIN, HEIGHT_MAX));
  if (outOfRange) {
    resultText.textContent = "Error: your measurements are outside the chart range. That’s something I can’t answer.";
    if (disclaimerBlock) disclaimerBlock.hidden = false;
    if (chartContainer) chartContainer.innerHTML = '';
    return;
  }

  const match = bestMatch(wizardState.chestIn, wizardState.heightIn);
  resultText.textContent = `Based on the measurements provided, it is recommended to select a shirt size "${match.size}"`;
  if (disclaimerBlock) disclaimerBlock.hidden = true;
  renderChart(match);
}

// Wire events and auto-open on load
window.addEventListener('DOMContentLoaded', () => {
  // Step 1: Next — chest
  const continueChest = document.getElementById('continueChest');
  const chestEl = document.getElementById('chestWidth');
  const skipMeasurements = document.getElementById('skipMeasurements');
  const tryOnBtnStep1 = document.getElementById('tryOnBtnStep1');

  if (continueChest) {
    continueChest.addEventListener('click', () => {
      const v = chestEl && chestEl.value !== '' ? parseFloat(chestEl.value) : null;
      wizardState.chestIn = typeof v === 'number' && !Number.isNaN(v) ? v : null; // null means skipped
      goToStep(2);
    });
  }
  if (chestEl) {
    chestEl.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        const v = chestEl.value !== '' ? parseFloat(chestEl.value) : null;
        wizardState.chestIn = typeof v === 'number' && !Number.isNaN(v) ? v : null;
        goToStep(2);
      }
    });
  }

  if (skipMeasurements) {
    skipMeasurements.addEventListener('click', () => {
      wizardState.chestIn = null;
      wizardState.heightIn = null;
      computeOutcome();
      goToStep(3);
    });
  }

  // Removed wizardRepeat button wiring (button no longer present)

  // Step 2: Next — height
  const continueHeight = document.getElementById('continueHeight');
  const heightEl = document.getElementById('heightIn');

  if (continueHeight) {
    continueHeight.addEventListener('click', () => {
      const v = heightEl && heightEl.value !== '' ? parseFloat(heightEl.value) : null;
      wizardState.heightIn = typeof v === 'number' && !Number.isNaN(v) ? v : null; // null means skipped
      computeOutcome();
      goToStep(3);
    });
  }
  if (heightEl) {
    heightEl.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        const v = heightEl.value !== '' ? parseFloat(heightEl.value) : null;
        wizardState.heightIn = typeof v === 'number' && !Number.isNaN(v) ? v : null;
        computeOutcome();
        goToStep(3);
      }
    });
  }

  // Step 3 buttons
  const tryOnBtn = document.getElementById('tryOnBtn');
  const homeBtn = document.getElementById('homeBtn');
  const tryAnotherBtn = document.getElementById('tryAnotherBtn');
  const finalNext = document.getElementById('finalNext');

  // Wire Try-On and Home navigation based on current product folder
  const parts = window.location.pathname.split('/').filter(Boolean);
  const productName = parts.length >= 2 ? decodeURIComponent(parts[1]) : '';
  const encodedName = encodeURIComponent(productName);
  if (tryOnBtn) tryOnBtn.addEventListener('click', () => {
    window.location.href = `/ar/${encodedName}/AR/`;
  });
  if (tryOnBtnStep1) tryOnBtnStep1.addEventListener('click', () => {
    window.location.href = `/ar/${encodedName}/AR/`;
  });
  if (homeBtn) homeBtn.addEventListener('click', () => {
    window.location.href = `/`;
  });
  if (tryAnotherBtn) tryAnotherBtn.addEventListener('click', () => resetWizard());
  if (finalNext) finalNext.addEventListener('click', () => {
    // Close the popup and start the size checking like at the start
    closeInstruction();
    if (typeof startSizeTry === 'function') {
      startSizeTry();
    }
    const startButton = document.getElementById('startButton');
    if (startButton) startButton.style.display = 'none';
  });

  // Auto-open the instruction modal at the very beginning
  openInstruction();
});