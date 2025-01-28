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
          // hash: true,
          // // Learn about plugins: https://revealjs.com/plugins/
          // plugins: [ RevealMarkdown, RevealHighlight, RevealNotes ]
        });
        Reveal.on('slidechanged', (event) => {
          console.log(event);
        });
        // Reveal.on('slidetransitionend', (event) => {
        //   console.log(event);
        // });
      })
    },
  };
})(Drupal, drupalSettings, Reveal, once);
