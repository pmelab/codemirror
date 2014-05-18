/**
 * @file
 * Intercept CodeMirror.requireMode to work with custom modes in modules.
 */
(function($, Drupal){
  var requireMode = CodeMirror.requireMode;
  CodeMirror.requireMode = function(mode, cont) {
    if (Drupal.settings.codemirror.modes.hasOwnProperty(mode)) {
      CodeMirror.modeURL = Drupal.settings.codemirror.modes[mode];
    }
    else {
      CodeMirror.modeURL = Drupal.settings.codemirror.path + '/mode/%N/%N.js';
    }
    requireMode.call(CodeMirror, mode, cont);
  };
}(jQuery, Drupal));