/*
    A simple jQuery modal (http://github.com/kylefox/jquery-modal)
    Version 0.5.5
*/
(function($) {

  var current = null;

  $.modaluji = function(el, options) {
    $.modaluji.close(); // Close any open modals.
    var remove, target;
    this.$body = $('body');
    this.options = $.extend({}, $.modaluji.defaults, options);
    this.options.doFade = !isNaN(parseInt(this.options.fadeDuration, 10));
    if (el.is('a')) {
      target = el.attr('href');
      //Select element by id from href
      if (/^#/.test(target)) {
        this.$elm = $(target);
        if (this.$elm.length !== 1) return null;
        this.open();
      //AJAX
      } else {
        this.$elm = $('<div>');
        this.$body.append(this.$elm);
        remove = function(event, modal) { modal.elm.remove(); };
        this.showSpinner();
        el.trigger($.modaluji.AJAX_SEND);
        $.get(target).done(function(html) {
          if (!current) return;
          el.trigger($.modaluji.AJAX_SUCCESS);
          current.$elm.empty().append(html).on($.modaluji.CLOSE, remove);
          current.hideSpinner();
          current.open();
          el.trigger($.modaluji.AJAX_COMPLETE);
        }).fail(function() {
          el.trigger($.modaluji.AJAX_FAIL);
          current.hideSpinner();
          el.trigger($.modaluji.AJAX_COMPLETE);
        });
      }
    } else {
      this.$elm = el;
      this.open();
    }
  };

  $.modaluji.prototype = {
    constructor: $.modaluji,

    open: function() {
      var m = this;
      if(this.options.doFade) {
        this.block();
        setTimeout(function() {
          m.show();
        }, this.options.fadeDuration * this.options.fadeDelay);
      } else {
        this.block();
        this.show();
      }
      if (this.options.escapeClose) {
        $(document).on('keydown.modaluji', function(event) {
          if (event.which == 27) $.modaluji.close();
        });
      }
      if (this.options.clickClose) this.blocker.click($.modaluji.close);
    },

    close: function() {
      this.unblock();
      this.hide();
      $(document).off('keydown.modaluji');
    },

    block: function() {
      var initialOpacity = this.options.doFade ? 0 : this.options.opacity;
      this.$elm.trigger($.modaluji.BEFORE_BLOCK, [this._ctx()]);
      this.blocker = $('<div class="jquery-modal blocker"></div>').css({
        top: 0, right: 0, bottom: 0, left: 0,
        width: "100%", height: "100%",
        position: "fixed",
        zIndex: this.options.zIndex,
        background: this.options.overlay,
        opacity: initialOpacity
      });
      this.$body.append(this.blocker);
      if(this.options.doFade) {
        this.blocker.animate({opacity: this.options.opacity}, this.options.fadeDuration);
      }
      this.$elm.trigger($.modaluji.BLOCK, [this._ctx()]);
    },

    unblock: function() {
      if(this.options.doFade) {
        this.blocker.fadeOut(this.options.fadeDuration, function() {
          $(this).remove();
        });
      } else {
        this.blocker.remove();
      }
    },

    show: function() {
      this.$elm.trigger($.modaluji.BEFORE_OPEN, [this._ctx()]);
      if (this.options.showClose) {
        this.closeButton = $('<a href="#close-modal" rel="modal:close" class="close-modal ' + this.options.closeClass + '">' + this.options.closeText + '</a>');
        this.$elm.append(this.closeButton);
      }
      this.$elm.addClass(this.options.modalujiClass + ' current');
      this.center();
      if(this.options.doFade) {
        this.$elm.fadeIn(this.options.fadeDuration);
      } else {
        this.$elm.show();
      }
      this.$elm.trigger($.modaluji.OPEN, [this._ctx()]);
    },

    hide: function() {
      this.$elm.trigger($.modaluji.BEFORE_CLOSE, [this._ctx()]);
      if (this.closeButton) this.closeButton.remove();
      this.$elm.removeClass('current');

      if(this.options.doFade) {
        this.$elm.fadeOut(this.options.fadeDuration);
      } else {
        this.$elm.hide();
      }
      this.$elm.trigger($.modaluji.CLOSE, [this._ctx()]);
    },

    showSpinner: function() {
      if (!this.options.showSpinner) return;
      this.spinner = this.spinner || $('<div class="' + this.options.modalujiClass + '-spinner"></div>')
        .append(this.options.spinnerHtml);
      this.$body.append(this.spinner);
      this.spinner.show();
    },

    hideSpinner: function() {
      if (this.spinner) this.spinner.remove();
    },

    center: function() {
      this.$elm.css({
        position: 'fixed',
        top: "50%",
        left: "50%",
        marginTop: - (this.$elm.outerHeight() / 2),
        marginLeft: - (this.$elm.outerWidth() / 2),
        zIndex: this.options.zIndex + 1
      });
    },

    //Return context for custom events
    _ctx: function() {
      return { elm: this.$elm, blocker: this.blocker, options: this.options };
    }
  };

  //resize is alias for center for now
  $.modaluji.prototype.resize = $.modaluji.prototype.center;

  $.modaluji.close = function(event) {
    if (!current) return;
    if (event) event.preventDefault();
    current.close();
    var that = current.$elm;
    current = null;
    return that;
  };

  $.modaluji.resize = function() {
    if (!current) return;
    current.resize();
  };

  // Returns if there currently is an active modal
  $.modaluji.isActive = function () {
    return current ? true : false;
  }

  $.modaluji.defaults = {
    overlay: "#000",
    opacity: 0.75,
    zIndex: 1,
    escapeClose: true,
    clickClose: true,
    closeText: 'Close',
    closeClass: '',
    modalujiClass: "ujimodal",
    spinnerHtml: null,
    showSpinner: true,
    showClose: true,
    fadeDuration: null,   // Number of milliseconds the fade animation takes.
    fadeDelay: 1.0        // Point during the overlay's fade-in that the modal begins to fade in (.5 = 50%, 1.5 = 150%, etc.)
  };

  // Event constants
  $.modaluji.BEFORE_BLOCK = 'modal:before-block';
  $.modaluji.BLOCK = 'modal:block';
  $.modaluji.BEFORE_OPEN = 'modal:before-open';
  $.modaluji.OPEN = 'modal:open';
  $.modaluji.BEFORE_CLOSE = 'modal:before-close';
  $.modaluji.CLOSE = 'modal:close';
  $.modaluji.AJAX_SEND = 'modal:ajax:send';
  $.modaluji.AJAX_SUCCESS = 'modal:ajax:success';
  $.modaluji.AJAX_FAIL = 'modal:ajax:fail';
  $.modaluji.AJAX_COMPLETE = 'modal:ajax:complete';

  $.fn.modaluji = function(options){
    if (this.length === 1) {
      current = new $.modaluji(this, options);
    }
    return this;
  };

  // Automatically bind links with rel="modal:close" to, well, close the modal.
  $(document).on('click.modaluji', 'a[rel="modal:close"]', $.modaluji.close);
  $(document).on('click.modaluji', 'a[rel="modal:open"]', function(event) {
    event.preventDefault();
    $(this).modaluji();
  });
})(jQuery);
