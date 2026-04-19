/*
 * sapjp.net ポップアップ制御
 * Design §6, §8 — Vanilla JS, IIFE, strict mode, jQuery 非依存
 * Cookie 制御 + 開閉ロジック + フォーカストラップ
 */
(function () {
	'use strict';

	var config = window.sapjpPopupConfig || {};
	var popupId = config.popupId || 'popup-001';
	var cookieDays = parseInt(config.cookieDays, 10);
	if (isNaN(cookieDays) || cookieDays < 1) cookieDays = 30;
	var delayMs = parseInt(config.delayMs, 10);
	if (isNaN(delayMs) || delayMs < 0) delayMs = 1500;
	var isPreview = !!config.isPreview;

	var COOKIE_PREFIX = 'sapjp_popup_';
	var cookieName = COOKIE_PREFIX + popupId;

	var backdrop = null;
	var modal = null;
	var closeBtn = null;
	var wrapper = null;
	var previouslyFocused = null;

	var memoryStore = {};

	/* ====================================================================
	 * Cookie / storage utilities — Design §8 フォールバック階層
	 * ==================================================================== */
	function getCookie(name) {
		try {
			var pairs = document.cookie ? document.cookie.split('; ') : [];
			for (var i = 0; i < pairs.length; i++) {
				var eq = pairs[i].indexOf('=');
				if (eq > -1 && pairs[i].slice(0, eq) === name) {
					return decodeURIComponent(pairs[i].slice(eq + 1));
				}
			}
		} catch (e) {}
		return null;
	}

	function setCookie(name, value, days) {
		var expires = '';
		if (days) {
			var d = new Date();
			d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
			expires = '; expires=' + d.toUTCString();
		}
		var secure = location.protocol === 'https:' ? '; Secure' : '';
		try {
			document.cookie = name + '=' + encodeURIComponent(value) + expires +
				'; path=/; SameSite=Lax' + secure;
		} catch (e) {}
	}

	function hasSeenPopup() {
		if (getCookie(cookieName)) return true;
		try {
			if (window.sessionStorage && sessionStorage.getItem(cookieName)) return true;
		} catch (e) {}
		if (memoryStore[cookieName]) return true;
		return false;
	}

	function markSeen() {
		setCookie(cookieName, '1', cookieDays);
		if (!getCookie(cookieName)) {
			try {
				if (window.sessionStorage) sessionStorage.setItem(cookieName, '1');
			} catch (e) {}
			memoryStore[cookieName] = true;
		}
	}

	/* ====================================================================
	 * Focus trap
	 * ==================================================================== */
	var FOCUSABLE_SELECTOR = [
		'a[href]',
		'button:not([disabled])',
		'input:not([disabled])',
		'select:not([disabled])',
		'textarea:not([disabled])',
		'[tabindex]:not([tabindex="-1"])'
	].join(',');

	function getFocusable() {
		if (!modal) return [];
		var nodes = modal.querySelectorAll(FOCUSABLE_SELECTOR);
		return Array.prototype.filter.call(nodes, function (n) {
			return !n.hasAttribute('disabled') && n.offsetParent !== null;
		});
	}

	function onKeydown(e) {
		if (e.key === 'Escape' || e.keyCode === 27) {
			e.preventDefault();
			closePopup();
			return;
		}
		if (e.key !== 'Tab' && e.keyCode !== 9) return;

		var focusables = getFocusable();
		if (focusables.length === 0) {
			e.preventDefault();
			return;
		}
		var first = focusables[0];
		var last = focusables[focusables.length - 1];
		var active = document.activeElement;

		if (e.shiftKey) {
			if (active === first || !modal.contains(active)) {
				e.preventDefault();
				last.focus();
			}
		} else {
			if (active === last) {
				e.preventDefault();
				first.focus();
			}
		}
	}

	/* ====================================================================
	 * Open / Close
	 * ==================================================================== */
	function openPopup() {
		if (!backdrop || backdrop.classList.contains('open')) return;

		previouslyFocused = document.activeElement;

		backdrop.classList.add('open');
		backdrop.setAttribute('aria-hidden', 'false');
		document.body.dataset.sapjpPopupOpen = '1';
		document.body.style.overflow = 'hidden';

		wrapper = document.getElementById('wrapper');
		if (wrapper) wrapper.setAttribute('aria-hidden', 'true');

		document.addEventListener('keydown', onKeydown);

		setTimeout(function () {
			if (closeBtn) {
				closeBtn.focus();
			} else {
				var focusables = getFocusable();
				if (focusables.length) focusables[0].focus();
			}
		}, 50);
	}

	function closePopup() {
		if (!backdrop || !backdrop.classList.contains('open')) return;

		backdrop.classList.remove('open');
		backdrop.setAttribute('aria-hidden', 'true');
		delete document.body.dataset.sapjpPopupOpen;
		document.body.style.overflow = '';

		if (wrapper) wrapper.removeAttribute('aria-hidden');

		document.removeEventListener('keydown', onKeydown);

		if (!isPreview) markSeen();

		if (previouslyFocused && typeof previouslyFocused.focus === 'function') {
			try { previouslyFocused.focus(); } catch (e) {}
		}
	}

	/* ====================================================================
	 * Bind events
	 * ==================================================================== */
	function bindEvents() {
		closeBtn = backdrop.querySelector('.sapjp-popup__close');
		if (closeBtn) {
			closeBtn.addEventListener('click', function (e) {
				e.preventDefault();
				closePopup();
			});
		}

		backdrop.addEventListener('click', function (e) {
			if (e.target === backdrop) closePopup();
		});

		/* URL 無しのボタン（primary/ghost 両方）はクリックで閉じる */
		var buttons = backdrop.querySelectorAll('.sapjp-popup__btn');
		Array.prototype.forEach.call(buttons, function (btn) {
			if (btn.tagName === 'BUTTON') {
				btn.addEventListener('click', function (e) {
					e.preventDefault();
					closePopup();
				});
			}
		});
	}

	/* ====================================================================
	 * Init
	 * ==================================================================== */
	function init() {
		backdrop = document.getElementById('sapjp-popup-backdrop');
		if (!backdrop) return;

		modal = backdrop.querySelector('.sapjp-popup');
		if (!modal) return;

		bindEvents();

		if (!isPreview && hasSeenPopup()) return;

		setTimeout(openPopup, delayMs);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
