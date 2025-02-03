(function (Drupal, Reveal, once) {

  'use strict';

  /**
   * Initialize reveal.js! with custom settings.
   */
  Drupal.behaviors.present = {
    attach: function (context, settings) {
      once('revealjs', '.reveal').forEach(function (element) {
        let config = JSON.parse(element.getAttribute('data-config-options'));
        let reveal_deck = new Reveal(element, config);
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
      })
    },
  };
})(Drupal, Reveal, once);
