document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    /* パーティクルアニメーション（ライト背景用） */
    var canvas = document.getElementById('star-canvas');
    if (canvas) {
        var ctx = canvas.getContext('2d');
        var particles = [];
        var dpr = window.devicePixelRatio || 1;

        /* パーティクルの色パレット（淡い青・紫・グレー） */
        var colors = [
            { r: 120, g: 160, b: 255 },
            { r: 160, g: 120, b: 220 },
            { r: 100, g: 190, b: 210 },
            { r: 180, g: 180, b: 195 }
        ];

        function resizeCanvas() {
            var rect = canvas.parentElement.getBoundingClientRect();
            canvas.width = rect.width * dpr;
            canvas.height = rect.height * dpr;
            ctx.scale(dpr, dpr);
        }

        function createParticles() {
            var w = canvas.width / dpr;
            var h = canvas.height / dpr;
            var count = Math.floor((w * h) / 2500);
            particles = [];
            for (var i = 0; i < count; i++) {
                var c = colors[Math.floor(Math.random() * colors.length)];
                particles.push({
                    x: Math.random() * w,
                    y: Math.random() * h,
                    r: Math.random() * 2 + 0.5,
                    color: c,
                    alpha: Math.random() * 0.15 + 0.05,
                    vy: (Math.random() - 0.5) * 0.15,
                    vx: (Math.random() - 0.5) * 0.1,
                    pulseSpeed: Math.random() * 0.006 + 0.002,
                    phase: Math.random() * Math.PI * 2
                });
            }
        }

        function draw(time) {
            var w = canvas.width / dpr;
            var h = canvas.height / dpr;
            ctx.clearRect(0, 0, w, h);

            for (var i = 0; i < particles.length; i++) {
                var p = particles[i];

                /* ゆっくり漂う */
                p.x += p.vx;
                p.y += p.vy;

                /* 画面端で折り返し */
                if (p.x < -10) p.x = w + 10;
                if (p.x > w + 10) p.x = -10;
                if (p.y < -10) p.y = h + 10;
                if (p.y > h + 10) p.y = -10;

                /* 脈動 */
                var pulse = Math.sin(time * p.pulseSpeed + p.phase) * 0.4 + 0.6;

                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(' + p.color.r + ',' + p.color.g + ',' + p.color.b + ',' + (p.alpha * pulse) + ')';
                ctx.fill();
            }

            requestAnimationFrame(draw);
        }

        resizeCanvas();
        createParticles();
        requestAnimationFrame(draw);

        var resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                resizeCanvas();
                createParticles();
            }, 200);
        });
    }

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
