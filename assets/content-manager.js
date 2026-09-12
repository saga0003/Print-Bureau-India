jQuery(function($){
  const $form = $('#pbi-cm-editor-form');
  const $grid = $('#pbi-cm-gallery-grid');
  const $field = $('#pbi-cm-gallery-ids');
  const $unsaved = $('#pbi-cm-unsaved');
  const $save = $('#pbi-cm-save-button');
  let dirty = false;

  const markDirty = () => {
    if (!$form.length) return;
    dirty = true;
    $unsaved.addClass('is-dirty').find('b').text('Unsaved changes');
    $save.text('Save changes');
  };

  const markSaved = () => {
    dirty = false;
    $unsaved.removeClass('is-dirty').find('b').text('All changes saved');
  };

  const applyProductFilter = () => {
    const q = ($('#pbi-cm-product-search').val() || '').toLowerCase().trim();
    const state = $('.pbi-cm-filter-chips button.is-active').data('cm-filter') || 'all';
    let visible = 0;
    $('#pbi-cm-product-grid .pbi-cm-product-card').each(function(){
      const $card = $(this);
      const title = ($card.data('title') || '').toString();
      const cardState = ($card.data('state') || '').toString();
      const searchMatch = !q || title.indexOf(q) !== -1;
      const stateMatch = state === 'all' || cardState === state;
      const show = searchMatch && stateMatch;
      $card.prop('hidden', !show);
      if (show) visible++;
    });
    $('#pbi-cm-empty-state').prop('hidden', visible > 0);
  };
  $('#pbi-cm-product-search').on('input', applyProductFilter);
  $('.pbi-cm-filter-chips').on('click', 'button', function(){
    $(this).addClass('is-active').siblings().removeClass('is-active');
    applyProductFilter();
  });

  const activateTab = (name, updateHash = true) => {
    if (!name) return;
    const $button = $('.pbi-cm-editor-nav [data-cm-tab="'+name+'"]');
    const $panel = $('.pbi-cm-editor-panel[data-cm-panel="'+name+'"]');
    if (!$button.length || !$panel.length) return;
    $button.addClass('is-active').siblings().removeClass('is-active');
    $panel.addClass('is-active').siblings('.pbi-cm-editor-panel').removeClass('is-active');
    if (updateHash && history.replaceState) history.replaceState(null, '', '#'+name);
    if (name === 'content' && window.tinyMCE) {
      window.setTimeout(function(){
        const ed = tinyMCE.get('pbi_cm_product_content');
        if (ed) ed.execCommand('mceRepaint');
      }, 60);
    }
  };
  $('.pbi-cm-editor-nav').on('click', '[data-cm-tab]', function(){ activateTab($(this).data('cm-tab')); });
  const initialHash = (window.location.hash || '').replace('#','');
  if (initialHash) activateTab(initialHash, false);

  const updateCounter = ($input) => {
    const key = $input.data('char-counter');
    if (!key) return;
    const len = ($input.val() || '').length;
    const max = parseInt($input.attr('maxlength') || '0', 10);
    const $counter = $('[data-count-for="'+key+'"]');
    if (!$counter.length) return;
    $counter.text(max ? len+' / '+max : len);
    $counter.toggleClass('is-warning', max && len > max * .9);
  };
  $('[data-char-counter]').each(function(){ updateCounter($(this)); }).on('input', function(){ updateCounter($(this)); });

  const updateSeoPreview = () => {
    const title = $('[name="seo_title"]').val() || $('[name="product_title"]').val() || 'Print Bureau India';
    const desc = $('[name="meta_description"]').val() || $('[name="product_excerpt"]').val() || '';
    $('#pbi-cm-seo-preview-title').text(title);
    $('#pbi-cm-seo-preview-description').text(desc);
  };
  $('[name="seo_title"],[name="meta_description"],[name="product_title"],[name="product_excerpt"]').on('input', updateSeoPreview);

  if ($form.length) {
    $form.on('input change', 'input,textarea,select', markDirty);
    if (window.tinyMCE) {
      $(document).on('tinymce-editor-init', function(event, editor){
        if (editor.id === 'pbi_cm_product_content') editor.on('change keyup undo redo', markDirty);
      });
    }
    $form.on('submit', function(){
      dirty = false;
      $save.prop('disabled', true).text('Saving…');
      $unsaved.removeClass('is-dirty').find('b').text('Publishing changes…');
    });
    $(window).on('beforeunload', function(){ if (dirty) return 'You have unsaved changes.'; });
    $(document).on('keydown', function(e){
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
        e.preventDefault();
        if (!$save.prop('disabled')) $form.trigger('submit');
      }
    });
  }

  if ($grid.length && $field.length) {
    const syncGallery = () => {
      const ids = $grid.find('.pbi-cm-gallery-item').map(function(){ return $(this).data('id'); }).get();
      $field.val(ids.join(','));
      $grid.find('.pbi-cm-main-badge').remove();
      const $first = $grid.find('.pbi-cm-gallery-item').first();
      if ($first.length) $first.append('<span class="pbi-cm-main-badge">Main</span>');
      $('#pbi-cm-default-gallery').toggle(ids.length === 0);
      markDirty();
    };

    $grid.sortable({
      items: '.pbi-cm-gallery-item',
      placeholder: 'ui-sortable-placeholder',
      tolerance: 'pointer',
      update: syncGallery
    });

    $('#pbi-cm-add-images').on('click', function(e){
      e.preventDefault();
      const frame = wp.media({
        title: 'Choose product images',
        button: { text: 'Use selected images' },
        multiple: true,
        library: { type: 'image' }
      });
      frame.on('select', function(){
        frame.state().get('selection').each(function(model){
          const a = model.toJSON();
          if ($grid.find('[data-id="'+a.id+'"]').length) return;
          const src = (a.sizes && a.sizes.medium) ? a.sizes.medium.url : ((a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url);
          $grid.append(
            '<div class="pbi-cm-gallery-item" data-id="'+a.id+'">' +
              '<img src="'+src+'" alt="">' +
              '<button type="button" class="pbi-cm-remove-image" aria-label="Remove image">×</button>' +
              '<span class="pbi-cm-drag" aria-hidden="true">⋮⋮</span>' +
            '</div>'
          );
        });
        syncGallery();
      });
      frame.open();
    });

    $grid.on('click', '.pbi-cm-remove-image', function(e){
      e.preventDefault();
      $(this).closest('.pbi-cm-gallery-item').remove();
      syncGallery();
    });
  }

  const $dangerActions = $('.pbi-cm-danger-actions');
  const $resetForms = $('#pbi-cm-reset-forms');
  if ($dangerActions.length && $resetForms.length) {
    $resetForms.find('form').each(function(){
      const $formReset = $(this);
      $formReset.find('button').addClass('pbi-cm-danger-button');
      $dangerActions.append($formReset);
    });
    $resetForms.remove();
  }

  window.setTimeout(function(){ $('.pbi-cm-toast').fadeOut(260); }, 4200);
  markSaved();
});
