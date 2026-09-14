<?php
/** CONTACT. Expects $c, $page, $meta. */
$intro = $page['intro'];
$form  = $page['form'];
$integr = gns_get($c, 'integrations', array());
$email = trim((string)gns_get($c, 'site.email', ''));

$external = gns_get($integr, 'form_mode', 'builtin') === 'external'
    && trim((string)gns_get($integr, 'form_endpoint', '')) !== '';
$action = $external ? trim((string)$integr['form_endpoint']) : 'submit.php';

$calUrl = trim((string)gns_get($integr, 'cal_url', ''));
$submitLabel = $calUrl !== '' ? $form['submit_cal'] : $form['submit'];
include GNS_TEMPLATES . '/_head.php';
include GNS_TEMPLATES . '/_header.php';
?>

  <main id="main">
    <section class="contact-section">
      <div class="grain" aria-hidden="true"></div>

      <div class="shell contact-grid">

        <!-- LEFT -->
        <div class="contact-copy">
          <p class="hero-flag">
            <span class="pulse" aria-hidden="true"></span>
            <span><?= rich($intro['eyebrow']) ?></span>
          </p>
          <h1><?= rich($intro['h1']) ?></h1>
          <p class="lead"><?= rich($intro['lead']) ?></p>

          <hr class="hair">

          <ol class="steps">
<?php foreach ($page['steps'] as $i => $step): ?>
            <li>
              <span class="step-num"><?= $i + 1 ?></span>
              <div>
                <p class="step-title"><?= rich($step['title']) ?></p>
                <p class="step-desc"><?= rich($step['desc']) ?></p>
              </div>
            </li>
<?php endforeach; ?>
          </ol>

          <hr class="hair">

<?php if ($email !== ''): ?>
          <div class="contact-direct">
            <p class="contact-direct-label"><?= rich($page['direct_label']) ?></p>
            <a class="contact-mail" href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
          </div>
<?php endif; ?>
        </div>

        <!-- FORM -->
        <form class="form" id="form" action="<?= e($action) ?>" method="post"<?= $external ? '' : ' data-ajax="1"' ?>>
          <div class="form-head">
            <h2><?= rich($form['heading']) ?></h2>
            <span class="form-time"><?= rich($form['time']) ?></span>
          </div>

          <!-- Spam trap. A person never sees this; a bot fills everything in. -->
          <div class="form-trap" aria-hidden="true">
            <label for="website">Leave this empty</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
          </div>
          <input type="hidden" name="_started" value="">
          <input type="hidden" name="_next" value="thanks.html">
<?php if ($external): ?>
          <input type="hidden" name="_replyto" value="">
          <input type="hidden" name="_subject" value="<?= e(gns_get($integr, 'lead_subject', 'New enquiry')) ?>">
<?php endif; ?>

          <div class="field-row">
            <div class="field">
              <label for="name">Your name</label>
              <input type="text" id="name" name="name" autocomplete="name" placeholder="Jane Doe" maxlength="120" required>
            </div>
            <div class="field">
              <label for="business">Business name</label>
              <input type="text" id="business" name="business" autocomplete="organization" placeholder="Apex Detailing" maxlength="160" required>
            </div>
          </div>

          <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" autocomplete="email" placeholder="jane@apexdetailing.com" maxlength="190" required>
          </div>

          <!-- No option is pre-selected: a default would be recorded as an answer
               the visitor never gave, and these two are the qualifying questions. -->
          <fieldset class="field">
            <legend><?= rich($form['sector_legend']) ?></legend>
            <div class="chips">
<?php foreach ($form['sector_options'] as $opt): ?>
              <label class="chip"><input type="radio" name="sector" value="<?= e($opt['value']) ?>" required><span><?= rich($opt['label']) ?></span></label>
<?php endforeach; ?>
            </div>
          </fieldset>

          <fieldset class="field">
            <legend><?= rich($form['spend_legend']) ?></legend>
            <div class="chips">
<?php foreach ($form['spend_options'] as $opt): ?>
              <label class="chip"><input type="radio" name="spend" value="<?= e($opt['value']) ?>" required><span><?= rich($opt['label']) ?></span></label>
<?php endforeach; ?>
            </div>
          </fieldset>

          <div class="field">
            <label for="message"><?= rich($form['message_label']) ?></label>
            <textarea id="message" name="message" rows="4" maxlength="4000" placeholder="<?= e(plain($form['message_placeholder'])) ?>"></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-block btn-lg">
            <?= rich($submitLabel) ?>
            <?= gns_icon_arrow() ?>
          </button>

          <p class="form-status" id="form-status" role="status" aria-live="polite"></p>

          <p class="form-privacy">
            <svg viewBox="0 0 16 16" aria-hidden="true"><rect x="3" y="7" width="10" height="7" rx="1.5" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M5.5 7V5a2.5 2.5 0 0 1 5 0v2" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
            <span><?= rich($form['privacy']) ?> <a href="<?= e(gns_href($form['privacy_link'])) ?>">How we handle it</a>.</span>
          </p>
        </form>
      </div>

<?php if ($calUrl !== ''): ?>
      <div class="shell">
        <div class="scheduler reveal">
          <div class="scheduler-head">
            <h2><?= rich($integr['cal_label']) ?></h2>
            <p>If you would rather skip the reply-and-reschedule step, take a slot straight from the calendar.</p>
          </div>
          <!-- Loaded on demand rather than on page load: an iframe that nobody
               opens should not cost every visitor a third-party connection. -->
          <div class="scheduler-embed" data-scheduler="<?= e($calUrl) ?>">
            <button type="button" class="btn btn-secondary btn-lg" data-scheduler-open>
              Open the calendar
              <?= gns_icon_arrow() ?>
            </button>
            <noscript>
              <a class="btn btn-secondary btn-lg" href="<?= e($calUrl) ?>" target="_blank" rel="noopener noreferrer">Open the calendar</a>
            </noscript>
          </div>
        </div>
      </div>
<?php endif; ?>
    </section>
  </main>

<?php include GNS_TEMPLATES . '/_footer.php'; ?>
