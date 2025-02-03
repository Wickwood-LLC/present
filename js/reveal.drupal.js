(function (Drupal, drupalSettings, Reveal) {

  'use strict';

  /**
   * Initialize reveal.js! with custom settings.
   */
  Drupal.behaviors.present = {
    config: false,
    manager: false,
    observing: false,
    needCallBehaviors: false,
    ready: false,
    attach: function (context, settings) {
      once('revealjs', '.reveal').forEach(function (element) {
        let config = JSON.parse(element.getAttribute('data-config-options'));
        let reveal_deck = new Reveal(element, config);
        // let reveal_deck = new Reveal(element, {
        //   embedded: true,
        //   scrollActivationWidth: null,
        //   controls: false,
        //   controlsLayout: 'edges',
        //   // hash: true,
        //   // // Learn about plugins: https://revealjs.com/plugins/
        //   // plugins: [ RevealMarkdown, RevealHighlight, RevealNotes ]
        // });
        reveal_deck.initialize();
        reveal_deck.on('slidechanged', (event) => {
          let a = 'w';
          let config_override = JSON.parse(event.currentSlide.getAttribute('data-config-options'));
          reveal_deck.configure(config_override);
          // // Check if we're on the last slide
          // const currentSlideIndex = reveal_deck.getIndices(event.currentSlide);
          // const isLastSlide =
          //   currentSlideIndex.h === reveal_deck.getTotalSlides() - 1;

          // // Show controls only on the last slide.
          // if (isLastSlide) {
          //   reveal_deck.configure({ controls: true });
          // } else {
          //   reveal_deck.configure({ controls: false });
          // }
        });
        // Reveal.on('slidetransitionend', (event) => {
        //   console.log(event);
        // });
      })
    },
  };
})(Drupal, drupalSettings, Reveal, once);
