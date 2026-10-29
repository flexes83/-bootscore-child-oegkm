(() => {
  'use strict';
  const root = document.getElementById('fx-app');
  if (!root) return;
  const config = window.oegkmFraxplus || { ready: false };
  const $ = id => document.getElementById(id);
  const form = $('fx-form');
  const risks = ['previousFracture', 'parentFracturedHip', 'smoking', 'glucocorticoids', 'rheumatoidArthritis', 'secondaryOsteoporosis', 'alcohol'];
  let baseResult = null;
  let baseSnapshot = null;
  let example = false;
  let pending = false;
  let generation = 0;
  let controller;
  const format = value => typeof value === 'number' && Number.isFinite(value) ? `${new Intl.NumberFormat('de-AT', { maximumFractionDigits: 2 }).format(value)} %` : 'Nicht verfügbar';
  const select = (name, label, items) => ({ name, label, items });
  const number = (name, label, min, max) => ({ name, label, min, max });
  const definitions = [
    { type: 'PREV', title: 'Zeitpunkt und Ort einer früheren Fraktur', description: 'Berücksichtigt, wann und an welcher Stelle eine Fraktur aufgetreten ist. Voraussetzung: frühere Fraktur in den Basisdaten.', fields: [select('fracture', 'Frakturlokalisation', [['vert', 'Wirbelkörper'], ['hip', 'Hüfte'], ['hum', 'Oberarm'], ['forearm', 'Unterarm'], ['other', 'Andere']]), select('time', 'Zeit seit der Fraktur', [['0-1M', 'Unter 1 Monat'], ['1-6M', '1–6 Monate'], ['6-12M', '6–12 Monate'], ['12-24M', '12–24 Monate']])] },
    { type: 'COR', title: 'Dosis oraler Glukokortikoide', description: 'Verfeinert die Angabe zur Glukokortikoid-Einnahme anhand der täglichen Prednisolon-Äquivalentdosis. Voraussetzung: Glukokortikoide in den Basisdaten.', fields: [select('dose', 'Tägliche Dosis', [['LOW', 'Unter 2,5 mg'], ['MID', 'Mittlere Dosis (2,5 bis unter 7,5 mg)'], ['HIG', 'Ab 7,5 mg']])] },
    { type: 'TBS', title: 'Trabecular Bone Score (TBS)', description: 'Zusätzliche Information zur Knochenstruktur aus der Lendenwirbelsäulen-DXA. Erfordert BMD bzw. T-Score und GE Lunar oder Hologic. Der dokumentierte BMI-Bereich ist 15–37 kg/m². Altersabhängige Grenzen werden durch FRAXplus berücksichtigt.', fields: [select('scanningEquipment', 'DXA-Gerät', [['1', 'GE Lunar'], ['2', 'Hologic']]), number('value', 'TBS-Wert (0,5–2,0)', 0.5, 2)] },
    { type: 'DIA', title: 'Typ-2-Diabetes', description: 'Mit BMD wird die Diabetesdauer berücksichtigt. Ohne BMD wird das Risiko mit Diabetes als Ursache einer sekundären Osteoporose neu berechnet; die Dauer wird dann nicht verwendet.', fields: [select('duration', 'Dauer der Erkrankung', [['<5y', 'Unter 5 Jahre'], ['5-10y', '5–10 Jahre'], ['>10y', 'Über 10 Jahre']])] },
    { type: 'FALL', title: 'Stürze im vergangenen Jahr', description: 'Berücksichtigt die Zahl der Stürze in den letzten zwölf Monaten. Auch die Angabe „kein Sturz“ kann das Ausgangsrisiko verändern.', fields: [select('falls', 'Anzahl der Stürze', [['0', 'Kein Sturz'], ['1', '1'], ['2', '2'], ['3+', '3 oder mehr']])] },
    { type: 'FNLS', title: 'Unterschied zwischen Schenkelhals- und LWS-T-Score', description: 'Berücksichtigt den Unterschied zur Knochendichte der Lendenwirbelsäule. Voraussetzung sind BMD bzw. T-Score am Schenkelhals und ein klinisch verwertbarer LWS-T-Score. Durch Artefakte betroffene Wirbel müssen fachgerecht ausgeschlossen sein.', fields: [number('tscore', 'T-Score der Lendenwirbelsäule', -6, 6)] },
    { type: 'HAL', title: 'Hüftachsenlänge (HAL)', description: 'Anpassung anhand der Hüftgeometrie. Nur bei vorhandener BMD verwendbar. Das zugrunde liegende Modell basiert auf GE-Lunar-Messungen; Übertragbarkeit auf andere Geräte und Populationen ärztlich beurteilen.', fields: [number('hal', 'Hüftachsenlänge in mm', 1, 200)] },
    { type: 'PHPT', title: 'Primärer Hyperparathyreoidismus', description: 'Anpassung nach Alter und Geschlecht bei primärem Hyperparathyreoidismus ohne vorangegangene Parathyreoidektomie. Nur ohne BMD vorgesehen.', fields: [select('phptWithoutPtx', 'Voraussetzung bestätigen', [['true', 'PHPT liegt vor, keine Parathyreoidektomie']])] },
    { type: 'NPF', title: 'Anzahl früherer Frakturen', description: 'Verfeinert „frühere Fraktur: ja“ anhand der konkreten Anzahl. Diese Anpassung wird ohne BMD angewendet.', fields: [select('numberPrevFract', 'Anzahl früherer Frakturen', [['1', '1'], ['2', '2'], ['3', '3'], ['4+', '4 oder mehr']])] },
    { type: 'FA', title: 'Knochendichte am Unterarm', description: 'Berücksichtigt genau einen Unterarm-Messwert: Z-Score oder eine der beiden IBEX-Messungen. Z-Score: −4 bis +4; IBEX-BMD: 0,1–2 g/cm².', fields: [select('measurement', 'Messwert', [['zFA', 'Unterarm-Z-Score'], ['UdBmd', 'IBEX ultradistale BMD (g/cm²)'], ['TdBmd', 'IBEX Drittel-distale BMD (g/cm²)']]), number('value', 'Unterarm-Messwert', -4, 4)] },
  ];

  function readBase() {
    const values = new FormData(form);
    const b = { age: Number(values.get('age')), sex: values.get('sex'), weight: Number(values.get('weight')), height: Number(values.get('height')), bmd: values.get('bmd') || '0' };
    risks.forEach(name => { b[name] = values.has(name) ? values.get(name) === 'yes' : null; });
    if (b.bmd !== '0') b.bmdValue = Number(values.get('bmdValue'));
    return b;
  }
  function reasonFor(type, b) {
    if (['PREV', 'NPF'].includes(type) && b.previousFracture !== true) return 'Frühere Fraktur erforderlich';
    if (type === 'COR' && b.glucocorticoids !== true) return 'Glukokortikoide erforderlich';
    if (['NPF', 'PHPT'].includes(type) && b.bmd !== '0') return 'Nur ohne BMD';
    if (['TBS', 'FNLS', 'HAL'].includes(type) && b.bmd === '0') return 'BMD / T-Score erforderlich';
    if (type === 'TBS' && !['1', '2', '4'].includes(b.bmd)) return 'GE Lunar / Hologic erforderlich';
    if (type === 'TBS' && b.height > 0 && (b.weight / ((b.height / 100) ** 2) < 15 || b.weight / ((b.height / 100) ** 2) > 37)) return 'BMI 15–37 erforderlich';
    return '';
  }
  function status(message) { $('fx-status').textContent = message; $('fx-status').hidden = !message; }
  function step(name) { root.querySelectorAll('[data-step]').forEach(el => el.classList.toggle('is-current', el.dataset.step === name)); }
  function scrollTo(id) { $(id).scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' }); }
  function fieldControl(def, type) {
    const label = document.createElement('label');
    label.textContent = def.label;
    const input = document.createElement(def.items ? 'select' : 'input');
    input.name = def.name;
    input.id = `fx-${type}-${def.name}`;
    input.required = true;
    if (def.items) {
      input.append(new Option('Bitte auswählen', ''));
      def.items.forEach(([value, text]) => input.append(new Option(text, value)));
    } else { input.type = 'number'; input.step = 'any'; input.min = def.min; input.max = def.max; input.inputMode = 'decimal'; }
    label.append(input);
    return label;
  }
  function renderAdjustments() {
    const list = $('fx-adjustment-list');
    definitions.forEach((def, i) => {
      const detail = document.createElement('details'); detail.className = 'fx-adjustment'; detail.dataset.type = def.type;
      const summary = document.createElement('summary');
      const index = document.createElement('span'); index.textContent = String(i + 1).padStart(2, '0');
      const title = document.createElement('span'); title.textContent = def.title;
      const availability = document.createElement('span'); availability.className = 'fx-availability';
      summary.append(index, title, availability);
      const body = document.createElement('form'); body.className = 'fx-adjust-body';
      const intro = document.createElement('p'); intro.textContent = def.description;
      const fields = document.createElement('div'); fields.className = 'fx-adjust-fields';
      def.fields.forEach(field => fields.append(fieldControl(field, def.type)));
      const note = document.createElement('p'); note.className = 'fx-adjust-warning';
      const button = document.createElement('button'); button.type = 'submit'; button.className = 'fx-button'; button.textContent = 'Wahrscheinlichkeit anpassen →';
      body.append(intro, fields, note, button); detail.append(summary, body); list.append(detail);
      detail.addEventListener('toggle', () => { if (detail.open) list.querySelectorAll('details').forEach(other => { if (other !== detail) other.open = false; }); });
      body.addEventListener('submit', event => { event.preventDefault(); adjust(def, body); });
      fields.addEventListener('input', () => { generation++; controller?.abort(); pending = false; $('fx-final').hidden = true; status(''); availability(); });
      if (def.type === 'FA') body.elements.measurement.addEventListener('change', () => { const z = body.elements.measurement.value === 'zFA'; body.elements.value.min = z ? -4 : 0.1; body.elements.value.max = z ? 4 : 2; body.elements.value.value = ''; });
    });
  }
  function availability() {
    const b = readBase();
    root.querySelectorAll('.fx-adjustment').forEach(detail => {
      const reason = reasonFor(detail.dataset.type, b);
      detail.classList.toggle('is-unavailable', !!reason);
      detail.querySelector('.fx-availability').textContent = reason || 'Zusatzangabe';
      const formEl = detail.querySelector('form');
      let note = reason || (!baseResult ? 'Bitte zunächst FRAX® berechnen oder das Beispiel laden.' : '');
      if (example) note = detail.dataset.type === 'NPF' ? 'Beispielvorschau: Das feste Ergebnis gilt ausschließlich für den geladenen Fall mit 2 früheren Frakturen.' : 'Diese Anpassung ist in der Oberfläche vorbereitet. Eine Berechnung ist nach der API-Freischaltung möglich.';
      formEl.querySelector('.fx-adjust-warning').textContent = note;
      const live = config.ready && !example;
      formEl.querySelector('button').disabled = pending || !!reason || !baseResult || (!live && !(example && detail.dataset.type === 'NPF'));
      if (detail.dataset.type === 'DIA') {
        formEl.elements.duration.disabled = b.bmd === '0';
        formEl.elements.duration.required = b.bmd !== '0';
      }
    });
  }
  function updateBmd() {
    const measured = $('fx-bmd').value !== '0';
    $('fx-bmd-value-wrap').hidden = !measured;
    const input = $('fx-bmd-value'); input.disabled = !measured; input.required = measured;
    input.min = $('fx-bmd').value === '4' ? -6 : 0.001;
    input.max = $('fx-bmd').value === '4' ? 6 : 3;
    $('fx-bmd-unit').textContent = $('fx-bmd').value === '4' ? 'T-Score' : 'g/cm²';
  }
  function invalidate() {
    generation++; controller?.abort(); pending = false; baseResult = null; baseSnapshot = null; example = false;
    $('fx-result-empty').hidden = false; $('fx-base-result').hidden = true; $('fx-final').hidden = true; $('fx-example-label').hidden = true;
    $('fx-calculate').disabled = !config.ready; status(''); step('base'); updateBmd(); availability();
  }
  function showBase(result) {
    baseResult = result; baseSnapshot = readBase();
    $('fx-result-empty').hidden = true; $('fx-base-result').hidden = false;
    $('fx-example-label').hidden = !example;
    $('fx-mof').textContent = format(result.base.mof); $('fx-hip').textContent = format(result.base.hip);
    const b = baseSnapshot;
    $('fx-summary-meta').textContent = `${b.age} Jahre · ${b.sex === 'F' ? 'Weiblich' : 'Männlich'} · ${b.weight} kg · ${b.height} cm · ${b.bmd === '0' ? 'ohne BMD' : 'mit BMD / T-Score'}`;
    availability(); step('adjust');
  }
  async function request(body) {
    controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 45000);
    try {
      const response = await fetch(config.endpoint, { method: 'POST', credentials: 'same-origin', cache: 'no-store', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': config.nonce }, body: JSON.stringify(body), signal: controller.signal });
      const result = await response.json();
      if (!response.ok) throw new Error(result.message || 'Die Berechnung ist momentan nicht verfügbar.');
      return result;
    } finally { clearTimeout(timeout); }
  }
  async function adjust(def, adjustmentForm) {
    if (pending || !baseResult) return;
    if (JSON.stringify(baseSnapshot) !== JSON.stringify(readBase())) { invalidate(); return; }
    const values = new FormData(adjustmentForm);
    const adjustment = { type: def.type };
    def.fields.forEach(field => {
      if (values.has(field.name)) adjustment[field.name] = field.items ? values.get(field.name) : Number(values.get(field.name));
    });
    if (def.type === 'PHPT') adjustment.phptWithoutPtx = adjustment.phptWithoutPtx === 'true';
    if (example) {
      if (def.type !== 'NPF' || adjustment.numberPrevFract !== '2') { status('Für die Vorschau ist ausschließlich das feste Beispiel mit 2 früheren Frakturen hinterlegt. Andere Werte werden erst nach API-Freischaltung berechnet.'); return; }
      showFinal({ ...baseResult, adjustment: { type: 'NPF', mof: 18, hip: 1.8 } }, def, '2 frühere Frakturen');
      return;
    }
    if (!config.ready) return;
    const token = generation;
    pending = true; availability(); status('FRAXplus® berechnet die Anpassung …');
    try {
      const result = await request({ base: baseSnapshot, adjustment });
      if (token !== generation) return;
      if (!result.adjustment || result.partial) {
        $('fx-final').hidden = true;
        status('FRAXplus hat die Anpassung nicht uneingeschränkt bestätigt. Bitte die Voraussetzungen prüfen. Es wird kein angepasstes Ergebnis angezeigt.'); return;
      }
      const selected = def.fields.map(field => {
        const value = adjustment[field.name];
        if (value === undefined) return '';
        return `${field.label}: ${field.items ? (field.items.find(item => item[0] === String(value))?.[1] || String(value)) : value}`;
      }).filter(Boolean).join(' · ');
      showFinal(result, def, selected);
    } catch (error) { if (token === generation) status(error.name === 'AbortError' ? 'Die Berechnung wurde nicht bestätigt. Bitte erneut versuchen.' : error.message); }
    finally { if (token === generation) { pending = false; availability(); } }
  }
  function showFinal(result, def, selected) {
    $('fx-final').hidden = false; $('fx-final-example').hidden = !example;
    $('fx-final-description').textContent = def.title;
    $('fx-result-context').textContent = `${$('fx-summary-meta').textContent} · ${selected}`;
    $('fx-original-mof').textContent = format(result.base.mof); $('fx-original-hip').textContent = format(result.base.hip);
    $('fx-adjusted-mof').textContent = format(result.adjustment.mof); $('fx-adjusted-hip').textContent = format(result.adjustment.hip);
    status(''); step('result'); scrollTo('fx-final');
  }
  form.addEventListener('input', invalidate);
  form.addEventListener('change', invalidate);
  form.addEventListener('submit', async event => {
    event.preventDefault(); if (!config.ready || pending) return;
    const token = generation; pending = true; $('fx-calculate').disabled = true; availability(); status('FRAX® berechnet das Ausgangsrisiko …');
    try { const result = await request({ base: readBase() }); if (token === generation) { example = false; showBase(result); status(''); } }
    catch (error) { if (token === generation) status(error.name === 'AbortError' ? 'Die Berechnung wurde nicht bestätigt. Bitte erneut versuchen.' : error.message); }
    finally { if (token === generation) { pending = false; $('fx-calculate').disabled = !config.ready; availability(); } }
  });
  $('fx-reset').addEventListener('click', () => { form.reset(); invalidate(); root.querySelectorAll('details').forEach(el => { el.open = false; }); });
  $('fx-goto-adjust').addEventListener('click', () => scrollTo('fx-adjustments'));
  $('fx-alternative').addEventListener('click', () => { $('fx-final').hidden = true; step('adjust'); scrollTo('fx-adjustments'); });
  $('fx-print').addEventListener('click', () => window.print());
  $('fx-load-example').addEventListener('click', () => {
    form.reset(); invalidate();
    const b = { age: '50', sex: 'F', weight: '70', height: '160', previousFracture: 'yes', parentFracturedHip: 'yes', bmd: '0' };
    risks.forEach(name => { if (!b[name]) b[name] = 'no'; });
    Object.entries(b).forEach(([name, value]) => { const el = form.elements.namedItem(name); if (el) el.value = value; });
    example = true; updateBmd(); showBase({ base: { mof: 16, hip: 1.6 } });
    const section = root.querySelector('[data-type="NPF"]'); section.querySelector('select').value = '2'; section.open = true;
    status('Festes Beispiel geladen: 50 Jahre, weiblich, 70 kg, 160 cm, frühere Fraktur und elterliche Hüftfraktur. Keine Live-Berechnung.');
  });
  renderAdjustments(); updateBmd(); availability();
  if (config.ready) {
    $('fx-connection').querySelector('strong').textContent = 'FRAXplus® · API-Zugang eingerichtet';
    $('fx-connection').querySelector('p').textContent = 'Die Berechnung erfolgt über FRAXplus. Nur für die Berechnung erforderliche Angaben werden an den Anbieter übertragen. Die Beispielvorschau bleibt ohne Übertragung verfügbar.';
    $('fx-api-note').textContent = 'Die Berechnung wird serverseitig an FRAXplus übermittelt. Keine Speicherung in einer Patientenakte.';
    $('fx-calculate').disabled = false;
  }
})();
