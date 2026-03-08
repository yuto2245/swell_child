document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    /* フェードインアニメーション */
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    document.querySelectorAll('.js-fade-in').forEach(function(el) {
        observer.observe(el);
    });

    /* Contentsタブ切り替え */
    var tabs = Array.prototype.slice.call(document.querySelectorAll('.contents__tab'));

    function activateTab(tab) {
        var slug = tab.dataset.tab;
        tabs.forEach(function(t) {
            t.classList.remove('is-active');
            t.setAttribute('aria-selected', 'false');
            t.setAttribute('tabindex', '-1');
        });
        document.querySelectorAll('.contents__panel').forEach(function(p) {
            p.classList.remove('is-active');
            if (p.getAttribute('data-panel') === slug) {
                p.classList.add('is-active');
            }
        });
        tab.classList.add('is-active');
        tab.setAttribute('aria-selected', 'true');
        tab.setAttribute('tabindex', '0');
        tab.focus();
    }

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() { activateTab(this); });

        tab.addEventListener('keydown', function(e) {
            var idx = tabs.indexOf(this);
            var next;
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                next = tabs[(idx + 1) % tabs.length];
            } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                next = tabs[(idx - 1 + tabs.length) % tabs.length];
            } else if (e.key === 'Home') {
                next = tabs[0];
            } else if (e.key === 'End') {
                next = tabs[tabs.length - 1];
            }
            if (next) {
                e.preventDefault();
                activateTab(next);
            }
        });
    });

    /* 特集：スクロール展開 + 横スクロール */
    var wrapper = document.querySelector('.featured-banner-wrapper');
    var pin = document.querySelector('.featured-pin');
    var track = document.querySelector('.featured-track');

    if (wrapper && pin && track) {
        var panels = track.querySelectorAll('.featured-panel');
        var panelCount = panels.length;
        var ticking = false;

        function update() {
            var pinRect = pin.getBoundingClientRect();
            var pinH = pin.offsetHeight;
            var windowH = window.innerHeight;
            var scrolled = -pinRect.top;
            var scrollable = pinH - windowH;

            /* 展開アニメーション（最初の画面分） */
            var expandEnd = windowH * 0.5;
            var expand = Math.min(1, Math.max(0, scrolled / expandEnd));
            wrapper.style.setProperty('--expand', expand);

            /* 横スクロール（展開後〜最後） */
            var hStart = expandEnd;
            var hRange = scrollable - expandEnd;
            var hProgress = Math.min(1, Math.max(0, (scrolled - hStart) / hRange));
            var maxShift = (panelCount - 1) * 100;
            track.style.transform = 'translateX(-' + (hProgress * maxShift / panelCount) + '%)';

            ticking = false;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) {
                requestAnimationFrame(update);
                ticking = true;
            }
        }, { passive: true });

        update();
    }
});
