(function ($, Drupal, once, Vimeo, Reveal) {

  'use strict';

  function initVimeoPlayer(player_div, options, events) {
    const player = new Vimeo.Player(player_div, options);

    if (events) {
      $.each(events, function(index, event) {
        player.on(event, function(data) {
          if (event == 'ended') {
            Reveal.next();
          }
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
      // Emebdded vimeo cause some issue in the slide when the video is in the first slide.
      // Force it to adjust the size.
      // Make the presentation on this windwos layout with https://revealjs.com/postmessage/
      window.postMessage( JSON.stringify({ method: 'layout', args: [] }));
    });
    return player;
  }

  function switchPlayer(player_from, player_to) {
    player_from.getPaused().then(function(paused) {
      player_from.getCurrentTime().then(function(seconds) {
        player_to.setCurrentTime(seconds).then(function () {
          if (!paused) {
            player_from.pause();
            player_to.play();
          }
        })
      });
    }).catch(function(error) {
        console.error('Error occurred:', error);
    });
  }

  /**
   * Initialize Vimeo players with custom settings.
   */
  Drupal.behaviors.present_vimeo_player = {
    attach: function (context, settings) {
      const elements = once('vimeo-player', '.vimeo-player-wrapper', context);
      $(elements).each(function () {
        let $this = $(this);
        let attr_options = $this.attr('data-vimeo-options');
        let options = JSON.parse(attr_options);

        // Get list events to cpatured and passed to the server for processing.
        let events = JSON.parse($this.attr('data-vimeo-events') || []);
        if (!events.includes('ended')) {
          events.push('ended');
        }

        const mediaQueryList = window.matchMedia("(orientation: landscape)");
        if (typeof options.url === 'object') {
          if (options.url.hasOwnProperty('landscape') && options.url.hasOwnProperty('portrait')) {
            const landscape_options = Object.assign({}, options, {url: options.url.landscape});
            const portrait_options = Object.assign({}, options, {url: options.url.portrait});

            const landscape_player_div = document.createElement('div');
            landscape_player_div.setAttribute('class', 'vimeo-player');
            this.appendChild(landscape_player_div);
            const landscape_player = initVimeoPlayer(landscape_player_div, landscape_options, events);

            const portrait_player_div = document.createElement('div');
            portrait_player_div.setAttribute('class', 'vimeo-player');
            this.appendChild(portrait_player_div);
            const portrait_player = initVimeoPlayer(portrait_player_div, portrait_options, events);

            if (mediaQueryList.matches) {
              $(portrait_player_div).hide();
            }
            else {
              $(landscape_player_div).hide();
            }

            // Listen for screen orientation changes.
            mediaQueryList.addEventListener("change", (e) => {
              if (e.matches) {
                $(portrait_player_div).hide();
                $(landscape_player_div).show();
                switchPlayer(portrait_player, landscape_player);

              } else {
                $(landscape_player_div).hide();
                $(portrait_player_div).show();
                switchPlayer(landscape_player, portrait_player);
              }
            });
          }
          else {
            console.log('Video is not configured properly.');
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
})(jQuery, Drupal, once, Vimeo, Reveal);
