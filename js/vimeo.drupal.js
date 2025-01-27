(function ($, Drupal, once, Vimeo) {

  'use strict';

  function initVimeoPlayer(player_div, options, events) {
    var player = new Vimeo.Player(player_div, options);

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
  }

  /**
   * Initialize Vimeo players with custom settings.
   */
  Drupal.behaviors.present_vimeo_player = {
    attach: function (context, settings) {
      const elements = once('vimeo-player', '.vimeo-player-wrapper', context);
      $(elements).each(function () {
        var $this = $(this);
        var attr_options = $this.attr('data-vimeo-options');
        var options = JSON.parse(attr_options);

        // Get list events to cpatured and passed to the server for processing.
        var events = JSON.parse($this.attr('data-vimeo-events') || []);

        const mediaQueryList = window.matchMedia("(orientation: landscape)");
        if (typeof options.url === 'object') {
          if (options.url.hasOwnProperty('landscape') && options.url.hasOwnProperty('portrait')) {
            const landscape_options = Object.assign({}, options, {url: options.url.landscape});
            const portrait_options = Object.assign({}, options, {url: options.url.portrait});

            const landscape_player_div = document.createElement('div');
            landscape_player_div.setAttribute('class', 'vimeo-player');
            this.appendChild(landscape_player_div);
            initVimeoPlayer(landscape_player_div, landscape_options, events);

            const portrait_player_div = document.createElement('div');
            portrait_player_div.setAttribute('class', 'vimeo-player');
            this.appendChild(portrait_player_div);
            initVimeoPlayer(portrait_player_div, portrait_options, events);

            if (mediaQueryList.matches) {
              $(portrait_player_div).hide();
            }
            else {
              $(landscape_player_div).hide();
            }

            mediaQueryList.addEventListener("change", (e) => {
              if (e.matches) {
                $(portrait_player_div).hide();
                $(landscape_player_div).show();
              } else {
                $(landscape_player_div).hide();
                $(portrait_player_div).show();
              }
            });
          }
          else {

          }
        }
        else {
          const player_div = document.createElement('div');
          player_div.setAttribute('class', 'vimeo-player');
          this.appendChild(player_div);
          initVimeoPlayer(player_div, options, events)
        }
      })
    },
  };
})(jQuery, Drupal, once, Vimeo);
