(function($){

  Drupal.wysiwyg.editor.attach.codemirror = function (context, params, settings) {
    $('#' + params.field, context).codemirror(settings.language);
  };

  Drupal.wysiwyg.editor.detach.codemirror = function (context, params, trigger) {
    if (trigger !== 'serialize') {
      $('#' + params.field, context).codemirrorRemove();
    }
  };

}(jQuery));