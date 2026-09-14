/* =========================================================
   GN SCALES — ADMIN
   Repeatable lists, the media picker, lead actions, and the
   unsaved-changes guard. Every screen works without any of
   this; it only removes friction.
   ========================================================= */

(function () {
  'use strict';

  var CSRF = (function () {
    var el = document.querySelector('input[name="_csrf"]');
    return el ? el.value : '';
  })();

  function api(action, body, isForm) {
    var options = {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'X-CSRF-Token': CSRF, Accept: 'application/json' }
    };
    if (isForm) {
      options.body = body;
    } else {
      options.headers['Content-Type'] = 'application/x-www-form-urlencoded';
      options.body = new URLSearchParams(body).toString();
    }
    return fetch('api.php?action=' + encodeURIComponent(action), options)
      .then(function (r) { return r.json(); });
  }

  function on(selector, event, handler) {
    document.addEventListener(event, function (e) {
      var target = e.target.closest(selector);
      if (target) handler(e, target);
    });
  }

  /* ---------------------------------------------------------------
     Repeatable lists
     --------------------------------------------------------------- */

  /**
   * Row indexes only have to be unique and sortable — PHP re-keys them on
   * save — so a new row takes a timestamp rather than trying to renumber
   * everything that already exists.
   */
  on('[data-list-add]', 'click', function (e, button) {
    var path = button.getAttribute('data-list-add');
    var tpl = document.querySelector('template[data-list-template="' + cssEscape(path) + '"]');
    var list = document.querySelector('[data-list][data-path="' + cssEscape(path) + '"]');
    if (!tpl || !list) return;

    var html = tpl.innerHTML.split('__i__').join('n' + Date.now());
    var holder = document.createElement('div');
    holder.innerHTML = html;
    var row = holder.firstElementChild;

    var empty = list.querySelector('[data-list-empty]');
    if (empty) empty.remove();

    list.appendChild(row);
    relabel(list);
    markDirty();

    var firstInput = row.querySelector('input, textarea, select');
    if (firstInput) firstInput.focus();
  });

  on('[data-row-remove]', 'click', function (e, button) {
    var row = button.closest('[data-list-row]');
    var list = row ? row.closest('[data-list]') : null;
    if (!row) return;
    if (!window.confirm('Remove this item? It is only gone for good once you save.')) return;
    row.remove();
    if (list) relabel(list);
    markDirty();
  });

  on('[data-row-up]', 'click', function (e, button) {
    var row = button.closest('[data-list-row]');
    if (row && row.previousElementSibling) {
      row.parentNode.insertBefore(row, row.previousElementSibling);
      relabel(row.closest('[data-list]'));
      markDirty();
    }
  });

  on('[data-row-down]', 'click', function (e, button) {
    var row = button.closest('[data-list-row]');
    if (row && row.nextElementSibling && row.nextElementSibling.hasAttribute('data-list-row')) {
      row.parentNode.insertBefore(row.nextElementSibling, row);
      relabel(row.closest('[data-list]'));
      markDirty();
    }
  });

  /**
   * Number the rows, and label each one with its own first value so a long
   * list reads as "Meta & Instagram" rather than as eleven copies of "Item".
   */
  function relabel(list) {
    if (!list) return;
    var rows = list.querySelectorAll(':scope > [data-list-row]');
    rows.forEach(function (row, i) {
      var label = row.querySelector('[data-row-label]');
      if (!label) return;
      var first = row.querySelector('input[type="text"], textarea');
      var text = first && first.value ? first.value.replace(/<[^>]*>/g, '').trim() : '';
      label.textContent = (i + 1) + (text ? ' · ' + text.slice(0, 48) : '');
    });
  }

  document.addEventListener('input', function (e) {
    if (!e.target.closest('[data-list-row]')) return;
    relabel(e.target.closest('[data-list]'));
  });

  // CSS.escape is not everywhere, and these values are dotted paths.
  function cssEscape(value) {
    return window.CSS && CSS.escape ? CSS.escape(value) : value.replace(/[^\w-]/g, '\\$&');
  }

  /* ---------------------------------------------------------------
     Toggles and colour inputs
     --------------------------------------------------------------- */
  on('.toggle input[type="checkbox"]', 'change', function (e, input) {
    var text = input.parentNode.querySelector('.toggle-text');
    if (text) text.textContent = input.checked ? 'On' : 'Off';
  });

  document.addEventListener('input', function (e) {
    var target = e.target;
    if (target.type === 'color' && target.hasAttribute('data-color-for')) {
      var text = document.getElementById(target.getAttribute('data-color-for'));
      if (text) text.value = target.value;
      markDirty();
    }
  });

  /* ---------------------------------------------------------------
     Image fields
     --------------------------------------------------------------- */
  on('[data-image-clear]', 'click', function (e, button) {
    var wrap = button.closest('[data-imagefield]');
    wrap.querySelector('[data-image-path]').value = '';
    paintPreview(wrap);
    markDirty();
  });

  on('[data-image-upload]', 'click', function (e, button) {
    var wrap = button.closest('[data-imagefield]');
    var input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.addEventListener('change', function () {
      if (!input.files.length) return;
      var form = new FormData();
      form.append('file', input.files[0]);
      button.disabled = true;
      button.textContent = 'Uploading…';
      api('media.upload', form, true)
        .then(function (data) {
          if (!data.ok) throw new Error(data.error || 'upload failed');
          wrap.querySelector('[data-image-path]').value = data.file.url;
          paintPreview(wrap);
          markDirty();
        })
        .catch(function (err) { window.alert(err.message); })
        .then(function () { button.disabled = false; button.textContent = 'Upload'; });
    });
    input.click();
  });

  on('[data-image-pick]', 'click', function (e, button) {
    var wrap = button.closest('[data-imagefield]');
    openPicker(function (url) {
      wrap.querySelector('[data-image-path]').value = url;
      paintPreview(wrap);
      markDirty();
    });
  });

  document.addEventListener('input', function (e) {
    if (e.target.hasAttribute && e.target.hasAttribute('data-image-path')) {
      paintPreview(e.target.closest('[data-imagefield]'));
    }
  });

  function paintPreview(wrap) {
    if (!wrap) return;
    var value = wrap.querySelector('[data-image-path]').value.trim();
    var box = wrap.querySelector('[data-preview]');
    box.innerHTML = value
      ? '<img src="' + value.replace(/"/g, '&quot;') + '" alt="">'
      : '<span>none</span>';
  }

  /** Modal media picker, built on demand from the library. */
  function openPicker(choose) {
    var modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = '<div class="modal-inner" role="dialog" aria-modal="true" aria-label="Choose an image">'
      + '<div class="panel-head"><h2>Choose an image</h2>'
      + '<button type="button" class="btn btn-quiet btn-xs" data-close>Close</button></div>'
      + '<div class="panel-body"><p class="muted">Loading…</p></div></div>';
    document.body.appendChild(modal);

    function close() { modal.remove(); document.removeEventListener('keydown', onKey); }
    function onKey(e) { if (e.key === 'Escape') close(); }
    document.addEventListener('keydown', onKey);

    modal.addEventListener('click', function (e) {
      if (e.target === modal || e.target.closest('[data-close]')) { close(); return; }
      var pick = e.target.closest('[data-pick]');
      if (pick) { choose(pick.getAttribute('data-pick')); close(); }
    });

    api('media.list', {}).then(function (data) {
      var body = modal.querySelector('.panel-body');
      if (!data.ok || !data.files.length) {
        body.innerHTML = '<p class="muted">Nothing uploaded yet. Use the Upload button, '
          + 'or add files under Media.</p>';
        return;
      }
      body.innerHTML = '<div class="media">' + data.files.map(function (f) {
        return '<figure class="media-item"><img src="' + f.url + '" alt="" loading="lazy">'
          + '<figcaption class="media-meta"><span class="media-name">' + escapeHtml(f.name) + '</span></figcaption>'
          + '<div class="media-actions"><button type="button" class="btn btn-ghost btn-xs" data-pick="'
          + escapeHtml(f.url) + '">Use this</button></div></figure>';
      }).join('') + '</div>';
    });
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  /* ---------------------------------------------------------------
     Media library page
     --------------------------------------------------------------- */
  (function mediaPage() {
    var drop = document.querySelector('[data-drop]');
    if (!drop) return;
    var input = drop.querySelector('[data-drop-input]');
    var status = drop.querySelector('[data-drop-status]');

    ['dragenter', 'dragover'].forEach(function (name) {
      drop.addEventListener(name, function (e) { e.preventDefault(); drop.classList.add('is-over'); });
    });
    ['dragleave', 'drop'].forEach(function (name) {
      drop.addEventListener(name, function (e) { e.preventDefault(); drop.classList.remove('is-over'); });
    });
    drop.addEventListener('drop', function (e) { upload(e.dataTransfer.files); });
    input.addEventListener('change', function () { upload(input.files); });

    function upload(files) {
      var list = Array.prototype.slice.call(files);
      if (!list.length) return;
      var done = 0;
      var failed = [];
      status.textContent = 'Uploading 0 of ' + list.length + '…';

      // One at a time: a shared host will happily drop half a dozen
      // simultaneous multipart uploads on the floor.
      (function next() {
        if (!list.length) {
          status.textContent = failed.length
            ? failed.length + ' failed: ' + failed.join(', ')
            : 'Uploaded ' + done + '. Reloading…';
          if (!failed.length) window.setTimeout(function () { window.location.reload(); }, 600);
          return;
        }
        var file = list.shift();
        var form = new FormData();
        form.append('file', file);
        api('media.upload', form, true)
          .then(function (data) {
            if (data.ok) { done++; } else { failed.push(file.name + ' (' + data.error + ')'); }
          })
          .catch(function () { failed.push(file.name); })
          .then(function () {
            status.textContent = 'Uploading ' + done + ' of ' + (done + list.length) + '…';
            next();
          });
      })();
    }

    on('[data-media-delete]', 'click', function (e, button) {
      var name = button.getAttribute('data-media-delete');
      if (!window.confirm('Delete ' + name + '? Anything still pointing at it will break.')) return;
      api('media.delete', { name: name }).then(function (data) {
        if (data.ok) {
          var card = document.querySelector('[data-media="' + cssEscape(name) + '"]');
          if (card) card.remove();
        } else {
          window.alert(data.error || 'Could not delete that file.');
        }
      });
    });

    on('[data-media-copy]', 'click', function (e, button) {
      var url = button.getAttribute('data-media-copy');
      var label = button.textContent;
      var reset = function () { window.setTimeout(function () { button.textContent = label; }, 1200); };
      if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(function () { button.textContent = 'Copied'; reset(); });
      } else {
        window.prompt('Copy this path', url);
      }
    });
  })();

  /* ---------------------------------------------------------------
     Leads
     --------------------------------------------------------------- */
  on('[data-lead-toggle]', 'click', function (e, button) {
    var row = button.closest('[data-lead]');
    var read = row.classList.contains('is-unread');
    api('lead.read', { id: row.getAttribute('data-lead'), read: read ? '1' : '' }).then(function (data) {
      if (data.ok) row.classList.toggle('is-unread', !read);
    });
  });

  on('[data-lead-delete]', 'click', function (e, button) {
    var row = button.closest('[data-lead]');
    if (!window.confirm('Delete this enquiry permanently?')) return;
    api('lead.delete', { id: row.getAttribute('data-lead') }).then(function (data) {
      if (data.ok) row.remove();
    });
  });

  on('[data-leads-readall]', 'click', function () {
    api('lead.readall', {}).then(function (data) {
      if (data.ok) window.location.reload();
    });
  });

  /* ---------------------------------------------------------------
     Password strength
     --------------------------------------------------------------- */
  document.addEventListener('input', function (e) {
    if (!e.target.hasAttribute || !e.target.hasAttribute('data-pw')) return;
    var value = e.target.value;
    var meter = e.target.parentNode.querySelector('[data-pw-meter]');
    if (!meter) return;

    // Length does most of the work; variety is a tiebreak.
    var variety = (/[a-z]/.test(value) ? 1 : 0) + (/[A-Z]/.test(value) ? 1 : 0)
      + (/[0-9]/.test(value) ? 1 : 0) + (/[^A-Za-z0-9]/.test(value) ? 1 : 0);
    var score = Math.min(100, (value.length / 20) * 70 + variety * 7.5);

    meter.querySelector('span').style.width = score + '%';
    meter.classList.toggle('is-ok', score >= 70);
    meter.classList.toggle('is-mid', score >= 40 && score < 70);
  });

  /* ---------------------------------------------------------------
     Unsaved-changes guard
     --------------------------------------------------------------- */
  var dirty = false;

  function markDirty() {
    if (dirty) return;
    dirty = true;
    var bar = document.querySelector('[data-savebar]');
    var text = document.querySelector('[data-dirty-text]');
    if (bar) bar.classList.add('is-dirty');
    if (text) text.textContent = 'Unsaved changes.';
  }

  var watched = document.querySelector('[data-dirty-watch]');
  if (watched) {
    watched.addEventListener('input', markDirty);
    watched.addEventListener('change', markDirty);
    watched.addEventListener('submit', function () { dirty = false; });
    window.addEventListener('beforeunload', function (e) {
      if (!dirty) return;
      e.preventDefault();
      e.returnValue = '';
    });
  }

  // Number the rows that arrived with the page.
  document.querySelectorAll('[data-list]').forEach(relabel);

  /* Ctrl/Cmd+S saves, because that is what hands do. */
  document.addEventListener('keydown', function (e) {
    if (!(e.ctrlKey || e.metaKey) || e.key.toLowerCase() !== 's') return;
    var form = document.querySelector('[data-dirty-watch]');
    if (!form) return;
    e.preventDefault();
    form.requestSubmit ? form.requestSubmit() : form.submit();
  });
})();
