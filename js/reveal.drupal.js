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
      once('revealjs', 'html').forEach(function (element) {
        // Reveal.js always runs on entire documetn and there is no way to select the element to target
        // So, we restrict it only once in a page.
        Reveal.initialize({
          embedded: true,
          scrollActivationWidth: null,
          controls: false,
          // hash: true,
          // // Learn about plugins: https://revealjs.com/plugins/
          // plugins: [ RevealMarkdown, RevealHighlight, RevealNotes ]
        });
        Reveal.on('slidechanged', (event) => {
          // Check if we're on the last slide
          const currentSlideIndex = Reveal.getIndices(event.currentSlide);
          const isLastSlide =
            currentSlideIndex.h === Reveal.getTotalSlides() - 1;

          // Show controls only on the last slide.
          if (isLastSlide) {
            Reveal.configure({ controls: true });
          } else {
            Reveal.configure({ controls: false });
          }
        });
        // Reveal.on('slidetransitionend', (event) => {
        //   console.log(event);
        // });
      })
    },
  };
})(Drupal, drupalSettings, Reveal, once);
