document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    var root = document.body;
    var threshold = 80;
    var ticking = false;

    function updateHeaderState() {
        var shouldCompact = window.scrollY > threshold;
        root.classList.toggle('is-header-scrolled', shouldCompact);
        ticking = false;
    }

    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(updateHeaderState);
            ticking = true;
        }
    }, { passive: true });

    updateHeaderState();
});
