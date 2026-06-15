(function ($) {
    "use strict";

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/vapfem_video_player.default',
            function ($scope) {
                window.LeanPL.player.initAll($scope[0]);
            }
        );

        elementorFrontend.hooks.addAction(
            'frontend/element_ready/vapfem_audio_player.default',
            function ($scope) {
                window.LeanPL.player.initAll($scope[0]);
            }
        );

        elementorFrontend.hooks.addAction(
            'frontend/element_ready/lpl_playlist.default',
            function ($scope) {
                window.LeanPL.playlist.initAll($scope[0]);
            }
        );
    });

})(jQuery);
