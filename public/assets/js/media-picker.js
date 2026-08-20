/**
 * KayaCMS Media Picker modal helper.
 *
 * Usage inside an opener page (admin content form with CKEditor):
 *
 *   window.kayaCmsMediaSelect = function (media) {
 *       // media = { url, alt, type, path }
 *       if (window.CKEDITOR) {
 *           window.CKEDITOR.instances['body'].insertHtml(
 *               media.type === 'image'
 *                   ? '<figure><img src="' + media.url + '" alt="' + media.alt + '"></figure>'
 *                   : '<a href="' + media.url + '">' + (media.alt || media.url) + '</a>'
 *           );
 *       } else {
 *           window.kayaCmsMediaSelection = media;
 *       }
 *   };
 *
 *   // Open the picker modal
 *   window.kayaCmsOpenMediaPicker('some-target-id');
 */
(function () {
  window.kayaCmsMediaSelect = window.kayaCmsMediaSelect || null;
  window.kayaCmsMediaPickerTarget = window.kayaCmsMediaPickerTarget || null;

  var modal = null;
  var panel = null;
  var lastTrigger = null;

  function ensureModal() {
    if (modal) return modal;

    modal = document.createElement('div');
    modal.className = 'bp-media-modal';
    modal.setAttribute('aria-hidden', 'true');
    modal.innerHTML = '<div class="bp-media-modal-backdrop" data-media-picker-close></div>' +
      '<section class="bp-media-modal-dialog" role="dialog" aria-modal="true" aria-label="Media library">' +
      '<div class="bp-media-modal-loading">Loading media library...</div>' +
      '</section>';
    document.body.appendChild(modal);
    panel = modal.querySelector('.bp-media-modal-dialog');
    hardenModalStyles();

    modal.addEventListener('click', function (event) {
      var close = event.target.closest('[data-media-picker-close]');
      if (close) closeModal();
    });

    modal.addEventListener('submit', function (event) {
      var filter = event.target.closest('[data-media-picker-filter]');
      if (filter) {
        event.preventDefault();
        loadPicker(filter.getAttribute('action') + '?' + new URLSearchParams(new FormData(filter)).toString());
        return;
      }

      var upload = event.target.closest('[data-media-upload-form]');
      if (upload) {
        event.preventDefault();
        uploadFiles(upload);
      }
    });

    modal.addEventListener('click', function (event) {
      var item = event.target.closest('[data-media-select]');
      if (item) {
        selectMedia({
          url: item.getAttribute('data-media-url') || '',
          alt: item.getAttribute('data-media-alt') || '',
          type: item.getAttribute('data-media-type') || 'file',
          path: item.getAttribute('data-media-path') || item.getAttribute('data-media-url') || ''
        });
        return;
      }

      var page = event.target.closest('[data-media-picker-page]');
      if (page) {
        event.preventDefault();
        loadPicker(page.getAttribute('href'));
      }
    });

    modal.addEventListener('change', function (event) {
      var input = event.target.closest('[data-media-upload-input]');
      if (input) renderUploadStatus(input.closest('[data-media-upload-form]'), input.files);
    });

    modal.addEventListener('dragover', function (event) {
      var drop = event.target.closest('[data-media-upload-drop]');
      if (!drop) return;
      event.preventDefault();
      drop.classList.add('is-dragging');
    });

    modal.addEventListener('dragleave', function (event) {
      var drop = event.target.closest('[data-media-upload-drop]');
      if (drop) drop.classList.remove('is-dragging');
    });

    modal.addEventListener('drop', function (event) {
      var drop = event.target.closest('[data-media-upload-drop]');
      if (!drop) return;
      event.preventDefault();
      drop.classList.remove('is-dragging');
      var input = drop.querySelector('[data-media-upload-input]');
      if (input) {
        input.files = event.dataTransfer.files;
        renderUploadStatus(input.closest('[data-media-upload-form]'), input.files);
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && modal && modal.classList.contains('is-open')) closeModal();
    });

    return modal;
  }

  function setImportant(el, props) {
    Object.keys(props).forEach(function (key) {
      el.style.setProperty(key, props[key], 'important');
    });
  }

  function hardenModalStyles() {
    if (!modal || !panel) return;
    setImportant(modal, {
      display: 'none',
      inset: '0',
      position: 'fixed',
      'z-index': '2147483000',
      width: '100vw',
      height: '100vh'
    });
    var backdrop = modal.querySelector('.bp-media-modal-backdrop');
    if (backdrop) {
      setImportant(backdrop, {
        background: 'rgba(10,18,15,.62)',
        inset: '0',
        position: 'absolute'
      });
    }
    setImportant(panel, {
      background: '#fff',
      'border-radius': '24px',
      'box-shadow': '0 30px 90px rgba(15,23,42,.32)',
      inset: 'clamp(12px, 3vw, 34px)',
      overflow: 'auto',
      padding: 'clamp(16px, 3vw, 28px)',
      position: 'absolute',
      width: 'auto',
      height: 'auto',
      margin: '0',
      transform: 'none'
    });
  }

  function openModal() {
    ensureModal();
    modal.classList.add('is-open');
    modal.style.setProperty('display', 'block', 'important');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('bp-media-modal-open');
  }

  function closeModal() {
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.style.setProperty('display', 'none', 'important');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('bp-media-modal-open');
    if (lastTrigger && typeof lastTrigger.focus === 'function') lastTrigger.focus();
  }

  function loadPicker(url) {
    openModal();
    panel.innerHTML = '<div class="bp-media-modal-loading">Loading media library...</div>';
    var targetUrl = new URL(url || '/admin/media/picker', window.location.origin);
    targetUrl.searchParams.set('modal', '1');

    fetch(targetUrl.toString(), {
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (response) {
        if (!response.ok) throw new Error('picker_load_failed');
        return response.text();
      })
      .then(function (html) {
        panel.innerHTML = html;
        if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
        var search = panel.querySelector('[name="search"]');
        if (search) search.focus();
      })
      .catch(function () {
        panel.innerHTML = '<div class="bp-media-modal-error">Media library could not be loaded.</div>';
      });
  }

  function renderUploadStatus(form, files) {
    if (!form) return;
    var status = form.querySelector('[data-media-upload-status]');
    if (!status) return;
    var list = Array.prototype.slice.call(files || []);
    status.innerHTML = list.length
      ? list.map(function (file) { return '<span>' + escapeHtml(file.name) + '</span>'; }).join('')
      : '';
  }

  function uploadFiles(form) {
    var input = form.querySelector('[data-media-upload-input]');
    var status = form.querySelector('[data-media-upload-status]');
    if (!input || !input.files || !input.files.length) {
      if (status) status.innerHTML = '<strong class="is-error">Please select at least one file.</strong>';
      return;
    }

    var submit = form.querySelector('[type="submit"]');
    if (submit) submit.disabled = true;
    if (status) status.innerHTML = '<strong>Uploading...</strong>';

    fetch(form.getAttribute('action'), {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: new FormData(form)
    })
      .then(function (response) { return response.json().then(function (payload) { return { ok: response.ok, payload: payload }; }); })
      .then(function (result) {
        var payload = result.payload || {};
        if (!result.ok || !payload.success) throw payload;
        if (status) status.innerHTML = '<strong class="is-success">' + escapeHtml(payload.message || 'Upload complete.') + '</strong>';
        if (payload.csrf && payload.csrf.name) {
          var csrf = form.querySelector('input[name="' + payload.csrf.name + '"]');
          if (csrf) csrf.value = payload.csrf.hash;
        }
        loadPicker('/admin/media/picker?modal=1');
      })
      .catch(function (payload) {
        var message = payload && payload.message ? payload.message : 'Upload failed.';
        if (status) status.innerHTML = '<strong class="is-error">' + escapeHtml(message) + '</strong>';
      })
      .finally(function () {
        if (submit) submit.disabled = false;
      });
  }

  function selectMedia(media) {
    if (typeof window.kayaCmsMediaSelect === 'function') {
      window.kayaCmsMediaSelect(media);
    } else {
      window.kayaCmsMediaSelection = media;
    }
    closeModal();
  }

  function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function (char) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
    });
  }

  window.kayaCmsOpenMediaPicker = function (target) {
    window.kayaCmsMediaPickerTarget = target || null;
    lastTrigger = document.activeElement;
    loadPicker('/admin/media/picker?modal=1');
  };
})();
