window.BackgroundAudio = window.BackgroundAudio || {
    id: 'BackgroundAudio',
    init: function(deck) {
        initBackgroundAudio(deck);
    }
};

const initBackgroundAudio = function(deck){
    
    deck.addEventListener('ready', function( event ) {
        let config = deck.getConfig();
        if ('background_audio' in config) {
            let audio = new Audio(config.background_audio);
            if (audio) {
                deck.configure({autoSlide: 5000, autoSlideStoppable: false, controls: false})
            }
            // audio.addEventListener("timeupdate", (event) => {
            //     // console.log(audio.currentTime);
            // });
            audio.play();
        }
		// selectAudio();
		// document.dispatchEvent( new CustomEvent('stopplayback') );
	} );

}