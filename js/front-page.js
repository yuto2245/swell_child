document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    /* ヒーロー見出し（x.ai 風: 単語 blur reveal + ローテーション + 下線シマー） */
    function initHeroTitleAnimation() {
        var title = document.getElementById('hero-title');
        if (!title) {
            return;
        }

        title.classList.add('hero-title--js');

        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var staticWords = title.querySelectorAll('.hero-word:not(.hero-word--rotate)');
        var rotateRoot = title.querySelector('.hero-rotate');
        var rotateWord = title.querySelector('.hero-word--rotate');

        function revealStaticWords() {
            staticWords.forEach(function(word) {
                word.classList.add('is-visible');
            });
        }

        function buildCharSpans(word) {
            var frag = document.createDocumentFragment();
            word.split('').forEach(function(char, index) {
                var span = document.createElement('span');
                span.className = 'hero-char';
                span.style.setProperty('--hero-char-index', String(index));
                span.textContent = char;
                frag.appendChild(span);
            });
            return frag;
        }

        function measureRotateWidth(word, sizer, clip) {
            if (!sizer || !clip) {
                return;
            }
            sizer.textContent = word;
            var width = sizer.scrollWidth;
            clip.style.width = (width > 0 ? width : sizer.offsetWidth) + 'px';
        }

        function setRotateWord(rotateEl, word, animateIn) {
            var clip = rotateEl.querySelector('.hero-rotate__clip');
            var charsEl = rotateEl.querySelector('.hero-rotate__chars');
            var sizer = rotateEl.querySelector('.hero-rotate__sizer');
            if (!clip || !charsEl) {
                return;
            }

            charsEl.textContent = '';
            charsEl.appendChild(buildCharSpans(word));
            measureRotateWidth(word, sizer, clip);

            if (!animateIn) {
                charsEl.querySelectorAll('.hero-char').forEach(function(char) {
                    char.classList.add('is-visible');
                });
                rotateEl.classList.add('is-active');
                return;
            }

            window.requestAnimationFrame(function() {
                charsEl.querySelectorAll('.hero-char').forEach(function(char) {
                    char.classList.add('is-visible');
                });
                rotateEl.classList.add('is-active');
            });
        }

        function exitRotateChars(charsEl) {
            var chars = charsEl.querySelectorAll('.hero-char');
            chars.forEach(function(char) {
                char.classList.remove('is-visible');
                char.classList.add('is-exiting');
            });
            return chars.length;
        }

        function initRotate(rotateEl, rotateSlot) {
            var words = (rotateEl.getAttribute('data-rotate-words') || '')
                .split(',')
                .map(function(word) { return word.trim(); })
                .filter(Boolean);

            if (!words.length) {
                if (rotateSlot) {
                    rotateSlot.classList.add('is-visible');
                }
                return;
            }

            var index = 0;
            var charsEl = rotateEl.querySelector('.hero-rotate__chars');
            var cycling = false;

            setRotateWord(rotateEl, words[0], !reduceMotion);
            if (rotateSlot) {
                rotateSlot.classList.add('is-visible');
            }

            if (reduceMotion || words.length < 2) {
                return;
            }

            window.setInterval(function() {
                if (cycling || !charsEl) {
                    return;
                }
                cycling = true;
                index = (index + 1) % words.length;
                var nextWord = words[index];
                var exitCount = exitRotateChars(charsEl);

                window.setTimeout(function() {
                    setRotateWord(rotateEl, nextWord, true);
                    cycling = false;
                }, exitCount * 18 + 160);
            }, 3200);
        }

        if (reduceMotion) {
            revealStaticWords();
            if (rotateRoot) {
                initRotate(rotateRoot, rotateWord);
            }
            return;
        }

        window.requestAnimationFrame(function() {
            revealStaticWords();
        });

        var rotateDelay = 40 * staticWords.length + 320;
        window.setTimeout(function() {
            if (rotateRoot) {
                initRotate(rotateRoot, rotateWord);
            }
        }, rotateDelay);
    }

    function startHeroWhenReady() {
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(initHeroTitleAnimation).catch(initHeroTitleAnimation);
        } else {
            initHeroTitleAnimation();
        }
    }

    startHeroWhenReady();

    /* パーティクルアニメーション（ライト背景用・ヒーロー初期化とは独立） */
    function initStarCanvas() {
        var canvas = document.getElementById('star-canvas');
        if (!canvas) {
            return;
        }

        var ctx = canvas.getContext('2d');
        if (!ctx) {
            return;
        }

        var particles = [];
        var dpr = window.devicePixelRatio || 1;
        var colors = [
            { r: 120, g: 160, b: 255 },
            { r: 160, g: 120, b: 220 },
            { r: 100, g: 190, b: 210 },
            { r: 180, g: 180, b: 195 }
        ];

        function resizeCanvas() {
            var parent = canvas.parentElement;
            if (!parent) {
                return;
            }
            var rect = parent.getBoundingClientRect();
            if (rect.width <= 0 || rect.height <= 0) {
                return;
            }
            canvas.width = rect.width * dpr;
            canvas.height = rect.height * dpr;
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.scale(dpr, dpr);
        }

        function createParticles() {
            var w = canvas.width / dpr;
            var h = canvas.height / dpr;
            var count = Math.floor((w * h) / 2500);
            particles = [];
            for (var i = 0; i < count; i++) {
                var color = colors[Math.floor(Math.random() * colors.length)];
                particles.push({
                    x: Math.random() * w,
                    y: Math.random() * h,
                    r: Math.random() * 2 + 0.5,
                    color: color,
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
                p.x += p.vx;
                p.y += p.vy;
                if (p.x < -10) p.x = w + 10;
                if (p.x > w + 10) p.x = -10;
                if (p.y < -10) p.y = h + 10;
                if (p.y > h + 10) p.y = -10;
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

    try {
        initStarCanvas();
    } catch (err) {
        // 星空 canvas の失敗でヒーローアニメを止めない
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
    var tabList = document.querySelector('.contents__tabs');
    var tabIndicatorTimer;

    function updateTabIndicator(tab) {
        if (!tabList || !tab) {
            return;
        }

        var listRect = tabList.getBoundingClientRect();
        var tabRect = tab.getBoundingClientRect();
        var x = tabRect.left - listRect.left + tabList.scrollLeft;

        tabList.style.setProperty('--contents-tab-indicator-x', x + 'px');
        tabList.style.setProperty('--contents-tab-indicator-w', tabRect.width + 'px');
    }

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
        window.clearTimeout(tabIndicatorTimer);
        tabIndicatorTimer = window.setTimeout(function() {
            window.requestAnimationFrame(function() {
                updateTabIndicator(tab);
            });
        }, 120);
        if (document.activeElement && document.activeElement.classList && document.activeElement.classList.contains('contents__tab')) {
            tab.focus();
        }
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

    if (tabs.length) {
        updateTabIndicator(tabs.find(function(tab) {
            return tab.classList.contains('is-active');
        }) || tabs[0]);

        window.addEventListener('resize', function() {
            updateTabIndicator(tabs.find(function(tab) {
                return tab.classList.contains('is-active');
            }) || tabs[0]);
        });
    }

});
