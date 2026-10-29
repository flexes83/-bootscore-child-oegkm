<?php
/** Rendered only by the unlisted member route in inc/fraxplus.php. */
if (!defined('ABSPATH')) { exit; }
get_header();
$risks = [
    'previousFracture' => ['Frühere Fraktur', 'Fraktur im Erwachsenenalter nach geringer Belastung.'],
    'parentFracturedHip' => ['Hüftfraktur bei einem Elternteil', 'Hüftfraktur bei Mutter oder Vater.'],
    'smoking' => ['Aktuelles Rauchen', 'Derzeitiger Tabakkonsum.'],
    'glucocorticoids' => ['Orale Glukokortikoide', 'Aktuell oder früher länger als drei Monate, entsprechend der FRAX-Definition.'],
    'rheumatoidArthritis' => ['Rheumatoide Arthritis', 'Ärztlich gesicherte Diagnose.'],
    'secondaryOsteoporosis' => ['Sekundäre Osteoporose', 'Eine mit Osteoporose assoziierte Grunderkrankung.'],
    'alcohol' => ['Alkohol: mindestens 3 Einheiten pro Tag', 'Gemäß FRAX-Definition.'],
];
?>
<main id="primary" class="site-main oegkm-fraxplus">
  <div class="container fx-shell" id="fx-app">
    <header class="fx-heading">
      <div><p class="fx-eyebrow">ÖGKM Mitgliederbereich <span class="fx-badge">Vorschau</span></p>
        <h1>FRAX<span class="fx-plus">plus</span><sup>®</sup></h1>
        <p class="fx-lead">Frakturrisiko einschätzen. Individuell verfeinern.</p>
        <p class="fx-intro">Berechnen Sie die 10-Jahres-Frakturwahrscheinlichkeit und berücksichtigen Sie anschließend einen zusätzlichen klinischen Faktor.</p>
      </div>
      <div class="fx-context"><span class="fx-context-dot" aria-hidden="true"></span> Ländermodell Österreich<br><span>Für die ärztliche Anwendung</span></div>
    </header>

    <aside class="fx-notice" id="fx-connection"><span class="fx-notice-icon" aria-hidden="true">i</span><div><strong>Vorschau ohne Live-Berechnung</strong><p>Die API-Freischaltung steht noch aus. Sie können die Eingabe und Anpassungen bereits ansehen. Über „Beispiel laden“ sehen Sie einen fest vorgegebenen Beispielfall; Ihre Eingaben werden dabei nicht berechnet oder übertragen.</p></div><button type="button" class="fx-button fx-button--outline" id="fx-load-example">Beispiel laden <span aria-hidden="true">↗</span></button></aside>

    <ol class="fx-steps" aria-label="Ablauf"><li class="is-current" data-step="base"><span>01</span> Basisdaten</li><li data-step="adjust"><span>02</span> FRAXplus-Anpassung</li><li data-step="result"><span>03</span> Ergebnis</li></ol>
    <div id="fx-status" class="fx-status" role="status" aria-live="polite" hidden></div>

    <form id="fx-form" class="fx-layout">
      <section class="fx-panel fx-questionnaire" aria-labelledby="fx-questionnaire-title">
        <div class="fx-section-heading"><div><p class="fx-eyebrow">Schritt 01</p><h2 id="fx-questionnaire-title">Basisdaten &amp; Risikofaktoren</h2></div><span class="fx-tag">FRAX®</span></div>
        <div class="fx-demographics">
          <label>Alter <span class="fx-unit">Jahre</span><input id="fx-age" name="age" type="number" min="40" max="90" step="1" placeholder="40–90" required inputmode="numeric"></label>
          <fieldset class="fx-sex"><legend>Geschlecht</legend><div class="fx-choice"><label><input type="radio" name="sex" value="F" required><span>Weiblich</span></label><label><input type="radio" name="sex" value="M"><span>Männlich</span></label></div></fieldset>
          <label>Gewicht <span class="fx-unit">kg</span><input id="fx-weight" name="weight" type="number" min="25" max="125" step="0.1" placeholder="z. B. 70" required inputmode="decimal"></label>
          <label>Körpergröße <span class="fx-unit">cm</span><input id="fx-height" name="height" type="number" min="100" max="220" step="0.1" placeholder="z. B. 160" required inputmode="decimal"></label>
        </div>
        <div class="fx-risk-list">
          <?php $number = 5; foreach ($risks as $key => $risk) : ?>
          <fieldset class="fx-risk"><legend><span class="fx-number"><?php echo (int) $number++; ?></span><?php echo esc_html($risk[0]); ?></legend><div class="fx-risk-controls"><details class="fx-help"><summary aria-label="Information zu <?php echo esc_attr($risk[0]); ?>">?</summary><p><?php echo esc_html($risk[1]); ?></p></details><div class="fx-choice fx-choice--small"><label><input type="radio" name="<?php echo esc_attr($key); ?>" value="yes" required><span>Ja</span></label><label><input type="radio" name="<?php echo esc_attr($key); ?>" value="no"><span>Nein</span></label></div></div></fieldset>
          <?php endforeach; ?>
        </div>
        <div class="fx-bmd"><div class="fx-section-heading"><div><h3>Knochendichte am Schenkelhals</h3><p>Optional · BMD bzw. T-Score</p></div><span class="fx-number">12</span></div>
          <label for="fx-bmd">Messmethode</label><div class="fx-bmd-fields"><select id="fx-bmd" name="bmd"><option value="0">Keine BMD-Messung</option><option value="4">T-Score</option><option value="1">GE Lunar</option><option value="2">Hologic</option><option value="3">Norland</option><option value="5">DMS / Medilink</option><option value="5.1">IMEX / Medical</option><option value="6">Mindways QCT</option><option value="7">Osteosys</option></select><label id="fx-bmd-value-wrap" hidden><span class="screen-reader-text" id="fx-bmd-unit-label">Messwert</span><input id="fx-bmd-value" name="bmdValue" type="number" step="any" placeholder="Messwert" inputmode="decimal" disabled><span id="fx-bmd-unit" class="fx-input-unit">T-Score</span></label></div>
        </div>
        <div class="fx-form-actions"><button type="submit" class="fx-button" id="fx-calculate" disabled>FRAX® berechnen <span aria-hidden="true">→</span></button><button type="button" id="fx-reset" class="fx-text-button">Zurücksetzen</button></div>
        <p class="fx-footnote" id="fx-api-note">Die Berechnung wird nach der API-Freischaltung verfügbar.</p>
      </section>

      <aside class="fx-sidebar" aria-labelledby="fx-summary-title"><div class="fx-summary">
        <p class="fx-eyebrow">10-Jahres-Frakturwahrscheinlichkeit</p><h2 id="fx-summary-title">Ihr FRAX®-Ergebnis</h2>
        <span class="fx-example-label" id="fx-example-label" hidden>Festes Beispiel · keine Live-Berechnung</span>
        <div id="fx-result-empty" class="fx-empty"><span aria-hidden="true">↗</span><p>Ihr Ausgangspunkt für eine<br>individuelle Risikoeinschätzung.</p><small>Nach der Berechnung erscheinen hier die beiden FRAX®-Werte.</small></div>
        <div id="fx-base-result" hidden><div class="fx-result-line"><span>Größere osteoporotische<br>Fraktur</span><strong id="fx-mof">—</strong></div><div class="fx-result-line"><span>Hüftfraktur</span><strong id="fx-hip">—</strong></div><div class="fx-summary-meta" id="fx-summary-meta"></div><button type="button" class="fx-button fx-button--mint" id="fx-goto-adjust">Mit FRAXplus® verfeinern <span aria-hidden="true">↓</span></button></div>
      </div><p class="fx-sidebar-note">Statistische Risikoschätzung zur Unterstützung der ärztlichen Beurteilung. Keine individuelle Vorhersage und keine automatische Therapieempfehlung.</p></aside>
    </form>

    <section class="fx-adjustments" id="fx-adjustments" aria-labelledby="fx-adjust-title">
      <div class="fx-section-heading"><div><p class="fx-eyebrow">Schritt 02</p><h2 id="fx-adjust-title">Ein Ergebnis. Mehr Kontext.</h2><p>Wählen Sie einen klinisch relevanten Zusatzfaktor, um das FRAX®-Ergebnis zu verfeinern.</p></div><span class="fx-tag fx-tag--mint">FRAXplus®</span></div>
      <p class="fx-adjust-note">Anpassungen werden einzeln auf den FRAX®-Ausgangswert angewendet. Mehrere Faktoren werden nicht zu einem Gesamtrisiko kombiniert.</p>
      <div id="fx-adjustment-list"></div>
    </section>

    <section class="fx-final" id="fx-final" aria-labelledby="fx-final-title" hidden>
      <div class="fx-section-heading"><div><p class="fx-eyebrow">Schritt 03</p><h2 id="fx-final-title">Das Risiko im Vergleich</h2><p id="fx-final-description"></p></div><span class="fx-badge" id="fx-final-example" hidden>Beispieldaten</span></div>
      <div class="fx-result-context" id="fx-result-context"></div>
      <div class="fx-comparison"><div class="fx-comparison-header"><span>10-Jahres-Frakturwahrscheinlichkeit</span><span>FRAX®</span><span>FRAXplus® angepasst</span></div><div class="fx-comparison-row"><span>Größere osteoporotische Fraktur</span><strong id="fx-original-mof">—</strong><strong id="fx-adjusted-mof">—</strong></div><div class="fx-comparison-row"><span>Hüftfraktur</span><strong id="fx-original-hip">—</strong><strong id="fx-adjusted-hip">—</strong></div></div>
      <p class="fx-footnote">Prozentwerte beziehen sich auf einen Zeitraum von zehn Jahren. Die Anpassung ist eine eigenständige Alternative zum Ausgangsergebnis.</p>
      <div class="fx-final-actions"><button type="button" class="fx-button fx-button--outline" id="fx-alternative">Andere Anpassung ansehen</button><button type="button" class="fx-text-button" id="fx-print">Ergebnis drucken</button></div>
    </section>
    <footer class="fx-disclaimer"><strong>Hinweise zur Anwendung</strong><p>Die FRAXplus®-Anpassungen ersetzen keine klinische Beurteilung. Die Genauigkeit einer Kombination mehrerer Anpassungen ist nicht belegt. Beachten Sie die Voraussetzungen und Hinweise des jeweiligen Verfahrens. In dieser Vorschau werden keine Patientenakten angelegt und keine Eingaben dauerhaft gespeichert.</p><p>FRAX® und FRAXplus® sind eingetragene Marken. <a href="https://www.fraxplus.org/frax-plus" target="_blank" rel="noopener noreferrer">Informationen zum Verfahren ↗</a></p></footer>
  </div>
</main>
<?php get_footer(); ?>
