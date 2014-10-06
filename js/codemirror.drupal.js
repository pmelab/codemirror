(function (Drupal, debounce, CodeMirror, $) {
  "use strict";

  Drupal.editors.codemirror = {
    attach: function (element, format) {
      element.CodeMirror = CodeMirror.fromTextArea(element, format.editorSettings);
    },

    detach: function (element, format, trigger) {
      element.CodeMirror.toTextArea();
    },

    onChange: function (element, callback) {
    }
  };

})(Drupal, Drupal.debounce, CodeMirror, jQuery);