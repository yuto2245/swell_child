/**
 * Anthropic風コードブロック
 * - WordPress標準コードブロックの自動ラップ（ヘッダー + コピーボタン付与）
 * - コードグループ（タブ切り替え）
 * - Clipboard API によるコピー機能
 */
(function () {
  'use strict';

  /* ========== SVGアイコン ========== */
  var ICON_COPY =
    '<svg class="c-codeBlock__copy-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
    '<rect x="9" y="9" width="13" height="13" rx="2"/>' +
    '<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>' +
    '</svg>';

  var ICON_CHECK =
    '<svg class="c-codeBlock__copy-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
    '<polyline points="20 6 9 17 4 12"/>' +
    '</svg>';

  /* ========== 言語名の表示マッピング ========== */
  var LANG_MAP = {
    js: 'JavaScript',
    javascript: 'JavaScript',
    ts: 'TypeScript',
    typescript: 'TypeScript',
    py: 'Python',
    python: 'Python',
    php: 'PHP',
    html: 'HTML',
    css: 'CSS',
    scss: 'SCSS',
    sass: 'Sass',
    json: 'JSON',
    xml: 'XML',
    yaml: 'YAML',
    yml: 'YAML',
    sql: 'SQL',
    bash: 'Bash',
    shell: 'Shell',
    sh: 'Shell',
    zsh: 'Zsh',
    powershell: 'PowerShell',
    ruby: 'Ruby',
    rb: 'Ruby',
    go: 'Go',
    rust: 'Rust',
    java: 'Java',
    kotlin: 'Kotlin',
    swift: 'Swift',
    c: 'C',
    cpp: 'C++',
    csharp: 'C#',
    cs: 'C#',
    abap: 'ABAP',
    markdown: 'Markdown',
    md: 'Markdown',
    diff: 'Diff',
    dockerfile: 'Dockerfile',
    docker: 'Docker',
    curl: 'cURL'
  };

  /**
   * 言語クラスから言語キーを取得
   */
  function detectLang(codeEl) {
    var cls = (codeEl.className || '') + ' ' + (codeEl.parentElement.className || '');
    var match = cls.match(/\blang(?:uage)?-(\S+)/);
    return match ? match[1].toLowerCase() : '';
  }

  /**
   * 言語キーを表示名に変換
   */
  function langLabel(key) {
    return LANG_MAP[key] || (key ? key.toUpperCase() : 'Code');
  }

  /**
   * コピーボタンのクリック処理
   */
  function handleCopy(btn, codeEl) {
    var text = codeEl.textContent || '';
    navigator.clipboard.writeText(text).then(function () {
      btn.innerHTML = ICON_CHECK + 'Copied!';
      btn.classList.add('is-copied');
      setTimeout(function () {
        btn.innerHTML = ICON_COPY + 'Copy';
        btn.classList.remove('is-copied');
      }, 2000);
    });
  }

  /* ========== WordPress標準コードブロックをラップ ========== */
  function wrapWpCodeBlocks() {
    var blocks = document.querySelectorAll('.wp-block-code');

    blocks.forEach(function (block) {
      if (block.dataset.codeWrapped) return;
      block.dataset.codeWrapped = 'true';

      var codeEl = block.querySelector('code');
      if (!codeEl) return;

      var lang = detectLang(codeEl);

      // ラッパー作成
      var wrapper = document.createElement('div');
      wrapper.className = 'c-codeBlock';

      // ヘッダー
      var header = document.createElement('div');
      header.className = 'c-codeBlock__header';

      var langSpan = document.createElement('span');
      langSpan.className = 'c-codeBlock__lang';
      langSpan.textContent = langLabel(lang);

      var copyBtn = document.createElement('button');
      copyBtn.className = 'c-codeBlock__copy';
      copyBtn.type = 'button';
      copyBtn.setAttribute('aria-label', 'Copy code');
      copyBtn.innerHTML = ICON_COPY + 'Copy';
      copyBtn.addEventListener('click', function () {
        handleCopy(copyBtn, codeEl);
      });

      header.appendChild(langSpan);
      header.appendChild(copyBtn);

      // ボディ
      var body = document.createElement('div');
      body.className = 'c-codeBlock__body';

      // 既存のpreをボディ内に移動
      block.parentNode.insertBefore(wrapper, block);
      body.appendChild(block);
      wrapper.appendChild(header);
      wrapper.appendChild(body);

      // wp-block-codeの元スタイルをリセット
      block.style.margin = '0';
      block.style.borderRadius = '0';
    });
  }

  /* ========== コードグループ（タブ切り替え） ========== */
  function initCodeGroups() {
    var groups = document.querySelectorAll('.c-codeGroup');

    groups.forEach(function (group) {
      if (group.dataset.codeGroupInit) return;
      group.dataset.codeGroupInit = 'true';

      var tabs = group.querySelectorAll('.c-codeGroup__tab');
      var panels = group.querySelectorAll('.c-codeGroup__panel');
      var copyBtn = group.querySelector('.c-codeGroup__copy');

      if (tabs.length === 0 || panels.length === 0) return;

      // 初期表示
      tabs[0].setAttribute('aria-selected', 'true');
      panels[0].classList.add('is-active');

      // タブクリック
      tabs.forEach(function (tab, i) {
        tab.addEventListener('click', function () {
          tabs.forEach(function (t) { t.setAttribute('aria-selected', 'false'); });
          panels.forEach(function (p) { p.classList.remove('is-active'); });
          tab.setAttribute('aria-selected', 'true');
          if (panels[i]) panels[i].classList.add('is-active');
        });
      });

      // キーボード（左右矢印）
      var tabList = group.querySelector('.c-codeGroup__tabs');
      if (tabList) {
        tabList.addEventListener('keydown', function (e) {
          var idx = Array.prototype.indexOf.call(tabs, document.activeElement);
          if (idx < 0) return;
          var next = -1;
          if (e.key === 'ArrowRight') next = (idx + 1) % tabs.length;
          if (e.key === 'ArrowLeft') next = (idx - 1 + tabs.length) % tabs.length;
          if (next >= 0) {
            e.preventDefault();
            tabs[next].focus();
            tabs[next].click();
          }
        });
      }

      // コピーボタン
      if (copyBtn) {
        copyBtn.addEventListener('click', function () {
          var active = group.querySelector('.c-codeGroup__panel.is-active code');
          if (active) handleCopy(copyBtn, active);
        });
      }
    });
  }

  /* ========== 初期化 ========== */
  function init() {
    wrapWpCodeBlocks();
    initCodeGroups();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
