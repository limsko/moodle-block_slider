// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Karuzela slajdów bez zewnętrznych bibliotek (wzorzec WAI-ARIA Carousel).
 *
 * Przesuwanie (transform) albo przenikanie, autoodtwarzanie z pauzą na żądanie, przy najechaniu
 * i przy fokusie, gesty przesunięcia, klawiatura oraz poszanowanie prefers-reduced-motion.
 *
 * @module     block_slider/slider
 * @copyright  (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    'use strict';

    /** @var {number} Minimalne przesunięcie palca/myszy uznawane za gest (px). */
    var SWIPE_THRESHOLD = 40;

    /** @var {Array} Progi szerokości slajdera ograniczające liczbę widocznych slajdów. */
    var BREAKPOINTS = [
        {maxwidth: 480, perview: 1},
        {maxwidth: 768, perview: 2}
    ];

    /** @var {Array} Progi szerokości pojedynczego slajdu dla mniejszych napisów i sterowania. */
    var SIZES = [
        {maxwidth: 420, name: 'block-slider--narrow'},
        {maxwidth: 640, name: 'block-slider--compact'}
    ];

    /**
     * Czy użytkownik prosi o ograniczenie ruchu.
     *
     * @returns {boolean}
     */
    var prefersReducedMotion = function() {
        return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    };

    /**
     * Pojedyncza karuzela.
     *
     * @param {HTMLElement} root główny element slajdera
     * @class
     */
    var Slider = function(root) {
        this.root = root;
        this.config = JSON.parse(root.dataset.config || '{}');
        this.strings = this.config.strings || {};
        this.region = root.querySelector('.block-slider-region');
        this.viewport = root.querySelector('.block-slider-viewport');
        this.track = root.querySelector('.block-slider-track');
        this.slides = Array.prototype.slice.call(root.querySelectorAll('.block-slider-slide'));
        this.prevButton = root.querySelector('[data-action="prev"]');
        this.nextButton = root.querySelector('[data-action="next"]');
        this.playButton = root.querySelector('[data-action="playpause"]');
        this.dots = root.querySelector('.block-slider-dots');

        this.fade = this.config.effect === 'fade';
        this.rtl = window.getComputedStyle(root).direction === 'rtl';
        this.index = 0;
        this.perview = 1;
        this.timer = null;
        this.playing = false;
        this.hovered = false;
        this.focused = false;
        this.suppressclick = false;

        this.overlaycaptions = root.classList.contains('block-slider--caption-overlay');
        this.watchImages();
        this.updatePerview();
        this.fitCaptions();
        this.buildDots();
        this.render(true);
        this.addListeners();

        this.playing = !!this.config.autoplay && !prefersReducedMotion() && this.maxIndex() > 0;
        this.updatePlayButton();
        this.schedule();
        root.classList.add('block-slider--ready');
    };

    /**
     * Największy indeks pierwszego widocznego slajdu.
     *
     * @returns {number}
     */
    Slider.prototype.maxIndex = function() {
        return Math.max(0, this.slides.length - this.perview);
    };

    /**
     * Liczba widocznych slajdów zależna od szerokości slajdera.
     *
     * @returns {boolean} czy wartość się zmieniła
     */
    Slider.prototype.updatePerview = function() {
        var perview = this.fade ? 1 : Math.max(1, parseInt(this.config.perview, 10) || 1);
        var width = this.root.clientWidth;
        BREAKPOINTS.forEach(function(breakpoint) {
            if (width && width < breakpoint.maxwidth) {
                perview = Math.min(perview, breakpoint.perview);
            }
        });
        perview = Math.min(perview, this.slides.length);
        var changed = perview !== this.perview;
        this.perview = perview;
        this.root.style.setProperty('--block-slider-visible', perview);

        // Wielkość napisów zależy od szerokości slajdu, a nie ekranu (blok bywa w wąskiej kolumnie).
        var slidewidth = width / perview;
        var root = this.root;
        var matched = false;
        SIZES.forEach(function(size) {
            var match = !matched && width > 0 && slidewidth < size.maxwidth;
            matched = matched || match;
            root.classList.toggle(size.name, match);
        });
        return changed;
    };

    /**
     * Napis, który nie mieści się na grafice, trafia pod nią (zamiast wystawać poza ramkę).
     */
    Slider.prototype.fitCaptions = function() {
        var root = this.root;
        // Strzałki na grafice są centrowane względem samej grafiki (napis może być pod nią).
        var media = root.querySelector('.block-slider-slide.is-active .block-slider-media');
        if (media && media.offsetHeight) {
            root.style.setProperty('--block-slider-arrow-top', (media.offsetHeight / 2) + 'px');
        }
        if (!this.overlaycaptions) {
            return;
        }
        root.classList.add('block-slider--caption-overlay');
        root.classList.remove('block-slider--caption-stacked');
        var overflow = this.slides.some(function(slide) {
            var caption = slide.querySelector('.block-slider-caption');
            if (!caption) {
                return false;
            }
            var style = window.getComputedStyle(caption);
            var available = caption.clientHeight - parseFloat(style.paddingTop) - parseFloat(style.paddingBottom);
            return caption.firstElementChild.offsetHeight > available + 1;
        });
        if (overflow) {
            root.classList.remove('block-slider--caption-overlay');
            root.classList.add('block-slider--caption-stacked');
        }
    };

    /**
     * Grafika, której nie udało się wczytać, jest zastępowana ikoną.
     */
    Slider.prototype.watchImages = function() {
        Array.prototype.forEach.call(this.root.querySelectorAll('.block-slider-image'), function(image) {
            var markBroken = function() {
                image.closest('.block-slider-media').classList.add('is-broken');
            };
            if (image.complete && image.naturalWidth === 0 && image.currentSrc) {
                markBroken();
            }
            image.addEventListener('error', markBroken);
        });
    };

    /**
     * Przyciski paginacji - jeden na każdą możliwą pozycję.
     */
    Slider.prototype.buildDots = function() {
        if (!this.dots) {
            return;
        }
        var template = this.strings.gotoslide || '{n}';
        this.dots.innerHTML = '';
        for (var i = 0; i <= this.maxIndex(); i++) {
            var dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'block-slider-dot';
            dot.dataset.index = i;
            dot.setAttribute('aria-label', template.replace('{n}', i + 1));
            this.dots.appendChild(dot);
        }
        this.root.classList.toggle('block-slider--static', this.maxIndex() === 0);
    };

    /**
     * Przejście do pozycji.
     *
     * @param {number} index docelowy indeks
     */
    Slider.prototype.goTo = function(index) {
        var max = this.maxIndex();
        if (this.config.loop) {
            if (index > max) {
                index = 0;
            } else if (index < 0) {
                index = max;
            }
        }
        this.index = Math.max(0, Math.min(max, index));
        this.render(false);
        this.schedule();
    };

    /**
     * Następna pozycja.
     */
    Slider.prototype.next = function() {
        this.goTo(this.index + 1);
    };

    /**
     * Poprzednia pozycja.
     */
    Slider.prototype.prev = function() {
        this.goTo(this.index - 1);
    };

    /**
     * Ustawia położenie toru, widoczność slajdów i stan sterowania.
     *
     * @param {boolean} instant bez animacji
     */
    Slider.prototype.render = function(instant) {
        var self = this;
        if (instant) {
            this.root.classList.add('block-slider--instant');
        }

        if (!this.fade) {
            var direction = this.rtl ? 1 : -1;
            this.track.style.transform = 'translateX(calc(' + (direction * this.index) +
                ' * (100% + var(--block-slider-gap)) / ' + this.perview + '))';
        }

        this.slides.forEach(function(slide, i) {
            var visible = i >= self.index && i < self.index + self.perview;
            slide.classList.toggle('is-active', visible);
            slide.setAttribute('aria-hidden', visible ? 'false' : 'true');
            if (visible) {
                slide.removeAttribute('inert');
            } else {
                slide.setAttribute('inert', '');
            }
        });

        if (this.dots) {
            Array.prototype.forEach.call(this.dots.children, function(dot, i) {
                var active = i === self.index;
                dot.classList.toggle('active', active);
                if (active) {
                    dot.setAttribute('aria-current', 'true');
                } else {
                    dot.removeAttribute('aria-current');
                }
            });
        }

        if (this.prevButton && this.nextButton) {
            this.prevButton.disabled = !this.config.loop && this.index === 0;
            this.nextButton.disabled = !this.config.loop && this.index === this.maxIndex();
        }

        if (instant) {
            // Wymuszenie przeliczenia stylów przed przywróceniem animacji.
            this.track.getBoundingClientRect();
            window.requestAnimationFrame(function() {
                self.root.classList.remove('block-slider--instant');
            });
        }
    };

    /**
     * Planuje kolejne przejście, jeśli autoodtwarzanie może teraz działać.
     */
    Slider.prototype.schedule = function() {
        var self = this;
        window.clearTimeout(this.timer);
        this.timer = null;

        var paused = (this.hovered && this.config.pauseonhover) || this.focused || document.hidden;
        if (this.playing && !paused && this.maxIndex() > 0) {
            this.timer = window.setTimeout(function() {
                if (!self.config.loop && self.index === self.maxIndex()) {
                    self.goTo(0);
                } else {
                    self.next();
                }
            }, Math.max(1000, parseInt(this.config.interval, 10) || 5000));
        }
        // Zmiany slajdów ogłaszamy tylko, gdy nie przewijają się same.
        this.track.setAttribute('aria-live', this.timer ? 'off' : 'polite');
    };

    /**
     * Przełącza autoodtwarzanie (przycisk pauza/odtwarzaj).
     */
    Slider.prototype.togglePlay = function() {
        this.playing = !this.playing;
        this.updatePlayButton();
        this.schedule();
    };

    /**
     * Etykieta i ikona przycisku pauza/odtwarzaj.
     */
    Slider.prototype.updatePlayButton = function() {
        if (!this.playButton) {
            return;
        }
        this.root.classList.toggle('block-slider--paused', !this.playing);
        this.playButton.setAttribute('aria-label', this.playing ? this.strings.pause : this.strings.play);
    };

    /**
     * Zdarzenia sterowania, myszy, fokusu, klawiatury, gestów i zmiany rozmiaru.
     */
    Slider.prototype.addListeners = function() {
        var self = this;

        if (this.prevButton) {
            this.prevButton.addEventListener('click', function() {
                self.prev();
            });
        }
        if (this.nextButton) {
            this.nextButton.addEventListener('click', function() {
                self.next();
            });
        }
        if (this.playButton) {
            this.playButton.addEventListener('click', function() {
                self.togglePlay();
            });
        }
        if (this.dots) {
            this.dots.addEventListener('click', function(e) {
                var dot = e.target.closest('.block-slider-dot');
                if (dot) {
                    self.goTo(parseInt(dot.dataset.index, 10));
                }
            });
        }

        this.region.addEventListener('mouseenter', function() {
            self.hovered = true;
            self.schedule();
        });
        this.region.addEventListener('mouseleave', function() {
            self.hovered = false;
            self.schedule();
        });
        this.region.addEventListener('focusin', function() {
            self.focused = true;
            self.schedule();
        });
        this.region.addEventListener('focusout', function(e) {
            if (!self.region.contains(e.relatedTarget)) {
                self.focused = false;
                self.schedule();
            }
        });
        document.addEventListener('visibilitychange', function() {
            self.schedule();
        });

        this.region.addEventListener('keydown', function(e) {
            var tag = (e.target.tagName || '').toLowerCase();
            if (tag === 'input' || tag === 'textarea' || tag === 'select') {
                return;
            }
            if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                e.preventDefault();
                var forward = (e.key === 'ArrowRight') !== self.rtl;
                if (forward) {
                    self.next();
                } else {
                    self.prev();
                }
            }
        });

        if (this.config.swipe) {
            this.addSwipe();
        }

        var resize = function() {
            if (self.updatePerview()) {
                self.buildDots();
                self.index = Math.min(self.index, self.maxIndex());
            }
            self.fitCaptions();
            self.render(true);
        };
        // Przy proporcjach naturalnych wysokość ramki znana jest dopiero po wczytaniu grafiki.
        Array.prototype.forEach.call(this.root.querySelectorAll('.block-slider-image'), function(image) {
            image.addEventListener('load', function() {
                self.fitCaptions();
            });
        });
        if (window.ResizeObserver) {
            new window.ResizeObserver(resize).observe(this.root);
        } else {
            window.addEventListener('resize', resize);
        }
    };

    /**
     * Przesuwanie gestem (dotyk i mysz).
     */
    Slider.prototype.addSwipe = function() {
        var self = this;
        var start = null;

        this.viewport.addEventListener('pointerdown', function(e) {
            if (!e.isPrimary || e.button > 0 || e.target.closest('.block-slider-control')) {
                return;
            }
            start = {x: e.clientX, y: e.clientY};
        });
        this.viewport.addEventListener('pointerup', function(e) {
            if (!start) {
                return;
            }
            var dx = e.clientX - start.x;
            var dy = e.clientY - start.y;
            start = null;
            if (Math.abs(dx) < SWIPE_THRESHOLD || Math.abs(dx) < Math.abs(dy)) {
                return;
            }
            // Gest nie może jednocześnie otworzyć linku slajdu.
            self.suppressclick = true;
            var forward = (dx < 0) !== self.rtl;
            if (forward) {
                self.next();
            } else {
                self.prev();
            }
        });
        this.viewport.addEventListener('pointercancel', function() {
            start = null;
        });
        this.viewport.addEventListener('click', function(e) {
            if (self.suppressclick) {
                e.preventDefault();
                e.stopPropagation();
                self.suppressclick = false;
            }
        }, true);
    };

    return {
        /**
         * Inicjalizacja slajdera.
         *
         * @param {string} selector selektor głównego elementu
         */
        init: function(selector) {
            var root = document.querySelector(selector);
            if (root && !root.dataset.initialised) {
                root.dataset.initialised = '1';
                // eslint-disable-next-line no-new
                new Slider(root);
            }
        }
    };
});
