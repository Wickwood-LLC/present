(function ($, Drupal, Reveal, once) {

  'use strict';

  /**
   * Initialize reveal.js! with custom settings.
   */
  Drupal.behaviors.present = {
    attach: function (context, settings) {
      once('revealjs', '.reveal').forEach(function (element) {
        let config = JSON.parse(element.getAttribute('data-config-options'));
        let reveal_deck = new Reveal(element, config);
        let presentation_id = element.getAttribute('data-presentation-id');
        reveal_deck.initialize();
        reveal_deck.on('slidechanged', (event) => {
          let config_override = JSON.parse(event.currentSlide.getAttribute('data-config-options'));
          if ('slidechanged' in config_override) {
            reveal_deck.configure(config_override.slidechanged);
          }
        });
        reveal_deck.on('slidetransitionend', (event) => {
          let config_override = JSON.parse(event.currentSlide.getAttribute('data-config-options'));
          if ('slidetransitionend' in config_override) {
            reveal_deck.configure(config_override.slidetransitionend);
          }
        });
        let events_to_track = JSON.parse(element.getAttribute('data-events-to-track'));
        events_to_track.forEach((event_name) => {
          reveal_deck.on(event_name, (event_data) => {
            let data = {};
            if ('currentSlide' in event_data) {
              data['current_slide_number'] = event_data.currentSlide.getAttribute('data-slide-number');
            }
            if ('previousSlide' in event_data) {
              data['previous_slide_number'] = event_data.previousSlide.getAttribute('data-slide-number');
            }
            if ('size' in event_data) {
              data['size'] = event_data.size;
            }
            if ('scale' in event_data) {
              data['scale'] = event_data.scale;
            }
            Drupal.ajax({
              url: Drupal.url('ajax/presentation/event'),
              type: 'POST',
              submit: {
                name: event_data.type,
                data: data,
                presentation_id: presentation_id,
              }
            })
            .execute();
          });
        });
      })
    },
  };
})(jQuery, Drupal, Reveal, once);
