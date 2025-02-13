window.BackgroundAudio = window.BackgroundAudio || {
    id: 'BackgroundAudio',
    playing: false,
    init: function(deck) {
        let reveal_element = deck.getRevealElement();
        let plugin = this;
        // Get all start buttons
        const bg_start_buttons = reveal_element.querySelectorAll('[data-bg-audio-start-button]');

        // Loop through the buttons and attach click event listener
        bg_start_buttons.forEach(child => {
            child.addEventListener('click', function (event){
                plugin.startAudio(deck);
            });
        });
    },
    startAudio: function(deck) {
        let config = deck.getConfig();
        if ('background_audio' in config) {
            let audio = new Audio(config.background_audio);
            if (audio) {
                deck.configure({autoSlide: 5000, autoSlideStoppable: false, controls: false})
                audio.play();
            }
            // audio.addEventListener("timeupdate", (event) => {
            //     // console.log(audio.currentTime);
            // });
        }
    }

};