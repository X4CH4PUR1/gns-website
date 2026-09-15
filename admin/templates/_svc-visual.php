<?php
/**
 * The illustrative panel beside a service section. Expects $sec.
 * Everything here is our own mock-up, never a client asset, so it carries no
 * implied result — the copy on the page says so too.
 */
$goldClass = !empty($sec['gold']) ? ' card-gold' : '';
switch ($sec['visual']):
    case 'funnel':
?>
        <div class="svc-visual card<?= $goldClass ?> reveal">
          <p class="svc-visual-label">How the funnel is built</p>
          <div class="stage-list">
            <span class="stage" style="--w:100%;--c:#d2ad5c">Cold — broad prospecting</span>
            <span class="stage" style="--w:66%;--c:#b4914a">Warm — engagers</span>
            <span class="stage" style="--w:42%;--c:#f7e7be">Retarget</span>
          </div>
          <hr class="hair">
          <div class="mini-stats">
            <div><p>Concepts</p><strong>4—6</strong><em>per cycle</em></div>
            <div><p>Refresh</p><strong>Monthly</strong><em>Growth tier</em></div>
            <div><p>Reporting</p><strong>Weekly</strong><em>all tiers</em></div>
          </div>
        </div>
<?php
        break;
    case 'serp':
?>
        <div class="svc-visual card<?= $goldClass ?> reveal">
          <p class="svc-visual-label">Search results page</p>
          <div class="query">
            <svg viewBox="0 0 16 16" aria-hidden="true"><circle cx="7" cy="7" r="4.6" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M10.4 10.4 14 14" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            ceramic coating near me
          </div>
          <div class="result is-ad">
            <p class="result-meta"><span class="serp-tag">Sponsored</span> yourbrand.com</p>
            <p class="result-title">5-Year Ceramic Coating — Free Paint Inspection</p>
            <p class="result-desc">Certified installers. Book a 20-minute inspection, get a written quote the same day.</p>
          </div>
          <div class="result is-ghost"><span style="--w:52%"></span><span style="--w:78%"></span></div>
          <div class="result is-ghost is-fainter"><span style="--w:46%"></span><span style="--w:68%"></span></div>
          <p class="svc-visual-foot">Our own mock-up, not a live campaign.</p>
        </div>
<?php
        break;
    case 'browser':
    default:
?>
        <div class="svc-visual card<?= $goldClass ?> reveal">
          <div class="browser">
            <div class="browser-bar">
              <span></span><span></span><span></span>
              <em>yourbrand.com/ceramic-coating</em>
            </div>
            <div class="browser-body">
              <p class="browser-kicker">Certified installer</p>
              <p class="browser-head">Keep the showroom finish for five years.</p>
              <div class="browser-actions">
                <span class="browser-btn">Book inspection</span>
                <span class="browser-btn is-ghost">See pricing</span>
              </div>
              <hr class="hair">
              <div class="browser-grid"><span></span><span></span><span></span></div>
            </div>
          </div>
          <p class="browser-foot"><span>Built as</span><strong>Static HTML</strong></p>
        </div>
<?php
endswitch;
