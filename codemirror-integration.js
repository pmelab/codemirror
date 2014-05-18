(function($, Drupal) {
  Drupal.codemirror = {};

  Drupal.behaviors.codemirror = {
    attach: function (context, settings) {
      $('code[data-mime]', context).once('codemirror').codeMirrorHighlight();
      $('textarea[data-mime]', context).once('codemirror').codemirror();
    }
  };

  /**
   * Load all required assets for a specific mime type and provide configuration
   * values.
   *
   * @param mime
   * @param editable
   * @param callback
   */
  function loadConfiguration(mime, editable, callback) {
    var path = Drupal.settings.codemirror.path;
    var mod = Drupal.settings.codemirror.module;
    var js = [
      path + '/lib/codemirror.js',
      path + '/addon/mode/loadmode.js',
      mod + '/codemirror-load.js'
    ];

    // Load highlighting runmode if not editable.
    if (!editable) {
      js.push(path + '/addon/runmode/runmode.js');
    }
    var css = [
      path + '/lib/codemirror.css'
    ];
    var lang = Drupal.settings.codemirror.languages._default_;
    if (Drupal.settings.codemirror.languages[mime]) {
      lang = Drupal.settings.codemirror.languages[mime];
    }
    else if (Drupal.settings.codemirror.mimes[mime]) {
      lang.mode = Drupal.settings.codemirror.mimes[mime];
    }

    // Only load extension css if editable.
    if (editable) {
      js = js.concat(lang.js);
    }

    css = css.concat(lang.css);

    var assets = $.merge(js, css);
    Drupal.summon(assets, function() {
      // Give modules the possibility to alter configuration values.
      var hooks = Drupal.codemirror[mime];
      if (hooks && hooks.hasOwnProperty('config')) {
        hooks.config(lang);
      }
      if (lang.mode) {
        CodeMirror.requireMode(lang.mode, function(){
          callback(lang);
        });
      }
      else {
        callback(lang);
      }
    });
  }

  /**
   * Add Codemirror.
   */
  $.fn.codemirror = function(mime) {
    $(this).each(function(){
      var m = mime || $(this).attr('data-mime') || 'text/plain';
      var textarea = this;
      loadConfiguration(m, true, function(config){
        // Initiate CodeMirror
        textarea.CodeMirror = CodeMirror.fromTextArea(textarea, config);

        // Set a timeout for textarea updates.
        var timeout = false;
        textarea.CodeMirror.on('change', function(instance){
          if (timeout) { window.clearTimeout(timeout); }
          timeout = window.setTimeout(function (){
            $(textarea).val(instance.getValue());
          }, 300);
        });

        // Remove old grippie, append a new one.
        removeGrippie(textarea.CodeMirror);
        appendGrippie(textarea.CodeMirror);

        // Add a toolbar if available.
        if (config.hasOwnProperty('toolbar')) {
          var $wrapper = $('<div class="codemirror-toolbar-wrapper"></div>');
          $wrapper.append(buildToolbar(textarea.CodeMirror, config.toolbar));
          $(textarea.CodeMirror.display.wrapper).parent().prepend($wrapper);
        }
        var $cm = $(textarea.CodeMirror.display.wrapper);
        $cm.css({
          'line-height': config.lineheight + 'px',
          'height': (config.lineheight * parseInt($(textarea).attr('rows'))) + 'px',
          'font-size': config.fontsize + 'px',
          'font-family': config.font
        });
        textarea.CodeMirror.refresh();
      });
    });
  };

  /**
   * Highlight content.
   */
  $.fn.codeMirrorHighlight = function(mime) {
    $(this).each(function(){
      var m = mime || $(this).attr('data-mime') || 'text/plain';
      var container = this;
      loadConfiguration(m, false, function(config) {
        var text = $(container).text().replace(/^\s+|\s+$/g, '');
        var lines = text.split('\n').length;
        CodeMirror.runMode(text, m, container, config);
        $.each(config.theme.split(' '), function(index, theme){
          $(container).parent().addClass('cm-s-' + theme);
        });
        $(container).parent().addClass('CodeMirror');
        $(container).parent().css({
          'height': (parseInt($(container).css('line-height'), 10) * lines) + 'px',
          'font-family': config.font
        });
        $(container).css('font-family', config.font);
      });
    });
  };

  /**
   * Remove Codemirror (Textareas only).
   */
  $.fn.codemirrorRemove = function() {
    this.each(function(){
      if (!this.hasOwnProperty('CodeMirror')) {
        return;
      }
      $(this).removeClass('codemirror-processed');
      removeGrippie(this.CodeMirror);
      removeToolbar(this.CodeMirror);
      this.CodeMirror.toTextArea();
      this.CodeMirror = false;
    });
  }

  /**
   * Build the toolbar.
   */
  function buildToolbar(cm, toolbar) {
    var $bar = $('<ul class="codemirror-toolbar"></ul>');
    $.each(toolbar, function(label, elem) {
      var $leaf = $('<li></li>');
      if (elem.hasOwnProperty('children')) {
        $leaf.append(buildToolbar(cm, elem.children));
      }
      else {
        $button = $('<a href="#">' + elem.label + '</a>');
        $button.click(function() {
          cm.execCommand(elem.command);
          return false;
        });
        $leaf.append($button);
      }
      $leaf.appendTo($bar);
    });
    return $bar;
  }

  /**
   * Remove the toolbar.
   */
  function removeToolbar(cm) {
    $(cm.display.wrapper).parent().find('.codemirror-toolbar-wrapper').remove();
  }

  /**
   * Adds the standard drupal textarea grippie.
   */
  function appendGrippie(cm) {
    $wrapper = $(cm.display.wrapper);
    $wrapper.parent().addClass('resizable-textarea');
    var grippie = $('<div class="grippie"></div>').mousedown(startDrag);
    grippie.insertAfter($wrapper);
    function startDrag(e) {
      staticOffset = $wrapper.height() - e.pageY;
      $wrapper.css('opacity', 0.25);
      $(document).mousemove(performDrag).mouseup(endDrag);
      return false;
    }
    function performDrag(e) {
      $wrapper.height(Math.max(32, staticOffset + e.pageY) + 'px');
      return false;
    }
    function endDrag(e) {
      $(document).unbind('mousemove', performDrag).unbind('mouseup', endDrag);
      $wrapper.css('opacity', 1);
    }
  }

  /**
   * Remove the grippie, so there are no duplicates.
   */
  function removeGrippie(cm) {
    $(cm.display.wrapper).parent().find('.grippie').remove();
  }
}(jQuery, Drupal));