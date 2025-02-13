window.BackgroundAudio = window.BackgroundAudio || {
    id: 'BackgroundAudio',
    playing: false,
    backup_config: {},
    deck: null,
    init: function(deck) {
        this.deck = deck;
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
    backupConfigs: function(configs_items) {
        let plugin = this;
        let config = plugin.deck.getConfig();
        configs_items.forEach((property, index) => {
            plugin.backup_config[property] = config[property];
        });
    },
    restoreConfigs: function(configs_items) {
        let plugin = this;
        let config = {};
        configs_items.forEach((property, index) => {
            config[property] = plugin.backup_config[property];
        });
        plugin.deck.configure(config);
    },
    startAudio: function() {
        let plugin = this;
        let config = plugin.deck.getConfig();
        if ('background_audio' in config) {
            let audio = new Audio(config.background_audio);
            if (audio) {
                plugin.backupConfigs(['autoSlide', 'autoSlideStoppable', 'controls'], config);
                plugin.deck.configure({autoSlide: 0, autoSlideStoppable: false, controls: false});
                audio.play();
                audio.addEventListener("ended", (event) => {
                    plugin.stopAudio()
                });
            }
        }
    },
    stopAudio: function() {
        this.restoreConfigs(['autoSlide', 'autoSlideStoppable', 'controls']);
    }

};