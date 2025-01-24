(function ($, Drupal, once, Vimeo) {

  'use strict';

  /**
   * Initialize Vimeo players with custom settings.
   */
  Drupal.behaviors.present_vimeo_player = {
    attach: function (context, settings) {
      const elements = once('vimeo-player', '.vimeo-player', context);
      $(elements).each(function () {
        var $this = $(this);
        var attr_options = $this.attr('data-vimeo-options');
        var options = JSON.parse(attr_options);
        var player = new Vimeo.Player(this, options);
        
        // Get list events to cpatured and passed to the server for processing.
        var events = JSON.parse($this.attr('data-vimeo-events') || []);
        if (events) {
          $.each(events, function(index, event) {
            player.on(event, function(data) {
              Drupal.ajax({
                url: Drupal.url('ajax/present/vimeo-event'),
                type: 'POST',
                submit: {
                  name: event,
                  data: data,
                  embed_options: options,
                }
              })
              .execute();
            });
          });
        }
        player.ready().then(function() {
          // var $iframe = $(":first-child", $this);
          // $iframe.attr('style', $this.attr('style'));
          // $iframe.removeAttr('width');
          // $iframe.removeAttr('height');
        });
      })
    },
  };
})(jQuery, Drupal, once, Vimeo);
