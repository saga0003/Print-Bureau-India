jQuery(function($){
  const $grid = $('#pbi-cm-gallery-grid');
  const $field = $('#pbi-cm-gallery-ids');

  if (!$grid.length || !$field.length) return;

  const sync = () => {
    const ids = $grid.find('.pbi-cm-gallery-item').map(function(){
      return $(this).data('id');
    }).get();
    $field.val(ids.join(','));
  };

  $grid.sortable({
    items: '.pbi-cm-gallery-item',
    placeholder: 'ui-sortable-placeholder',
    tolerance: 'pointer',
    update: sync
  });

  $('#pbi-cm-add-images').on('click', function(e){
    e.preventDefault();

    const frame = wp.media({
      title: 'Choose product images',
      button: { text: 'Add selected images' },
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
      sync();
    });

    frame.open();
  });

  $grid.on('click', '.pbi-cm-remove-image', function(e){
    e.preventDefault();
    $(this).closest('.pbi-cm-gallery-item').remove();
    sync();
  });
});
