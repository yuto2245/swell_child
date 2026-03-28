/**
 * Anthropic風コードブロック
 * - WordPress標準コードブロックの自動ラップ（フローティング言語ラベル + コピーアイコン）
 * - 行番号の自動付与（DOMツリー走査ベース — Prismトークンを壊さない）
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

  var ICON_CHEVRON =
    '<svg class="c-codeBlock__lang-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' +
    '<polyline points="6 9 12 15 18 9"/>' +
    '</svg>';

  /* ========== 言語名の表示マッピング ========== */
  var LANG_MAP = {
    js: 'javascript',
    javascript: 'javascript',
    ts: 'typescript',
    typescript: 'typescript',
    py: 'python',
    python: 'python',
    php: 'php',
    html: 'html',
    css: 'css',
    scss: 'scss',
    sass: 'sass',
    json: 'json',
    xml: 'xml',
    yaml: 'yaml',
    yml: 'yaml',
    sql: 'sql',
    bash: 'bash',
    shell: 'shell',
    sh: 'shell',
    zsh: 'zsh',
    powershell: 'powershell',
    ruby: 'ruby',
    rb: 'ruby',
    go: 'go',
    rust: 'rust',
    java: 'java',
    kotlin: 'kotlin',
    swift: 'swift',
    c: 'c',
    cpp: 'c++',
    csharp: 'c#',
    cs: 'c#',
    abap: 'abap',
    markdown: 'markdown',
    md: 'markdown',
    diff: 'diff',
    dockerfile: 'dockerfile',
    docker: 'docker',
    curl: 'curl'
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
   * 言語キーを表示名に変換（小文字のまま）
   */
  function langLabel(key) {
    return LANG_MAP[key] || key || '';
  }

  /**
   * コピーボタンのクリック処理
   */
  function handleCopy(btn, codeEl) {
    var text = codeEl.textContent || '';
    navigator.clipboard.writeText(text).then(function () {
      btn.innerHTML = ICON_CHECK;
      btn.classList.add('is-copied');
      setTimeout(function () {
        btn.innerHTML = ICON_COPY;
        btn.classList.remove('is-copied');
      }, 2000);
    });
  }

  /**
   * DOMツリーを再帰走査し、テキストノード内の改行で行を分割する。
   * Prismが生成したトークンspan（<span class="token keyword">）が複数行にまたがる場合でも
   * 正しく分割される。
   */
  function wrapLines(codeEl) {
    var lines = [];
    var currentLine = document.createDocumentFragment();

    function commitLine() {
      lines.push(currentLine);
      currentLine = document.createDocumentFragment();
    }

    function copyAttributes(el) {
      var attrs = {};
      for (var i = 0; i < el.attributes.length; i++) {
        attrs[el.attributes[i].name] = el.attributes[i].value;
      }
      return attrs;
    }

    function applyAttributes(el, attrs) {
      for (var key in attrs) {
        if (attrs.hasOwnProperty(key)) {
          el.setAttribute(key, attrs[key]);
        }
      }
    }

    function appendWrapped(textNode, wrapperStack) {
      var wrapped = textNode;
      for (var i = wrapperStack.length - 1; i >= 0; i--) {
        var info = wrapperStack[i];
        var el = document.createElement(info.tagName);
        applyAttributes(el, info.attributes);
        el.appendChild(wrapped);
        wrapped = el;
      }
      currentLine.appendChild(wrapped);
    }

    function processNode(node, wrapperStack) {
      if (node.nodeType === Node.TEXT_NODE) {
        var parts = node.textContent.split('\n');
        for (var i = 0; i < parts.length; i++) {
          if (i > 0) commitLine();
          if (parts[i].length > 0) {
            appendWrapped(document.createTextNode(parts[i]), wrapperStack);
          }
        }
      } else if (node.nodeType === Node.ELEMENT_NODE) {
        var newStack = wrapperStack.concat([{
          tagName: node.tagName,
          attributes: copyAttributes(node)
        }]);
        var child = node.firstChild;
        while (child) {
          var next = child.nextSibling;
          processNode(child, newStack);
          child = next;
        }
      }
    }

    /* 子ノードを走査 */
    var child = codeEl.firstChild;
    while (child) {
      var next = child.nextSibling;
      processNode(child, []);
      child = next;
    }
    commitLine();

    /* 末尾の空行を除去 */
    while (lines.length > 1 && lines[lines.length - 1].childNodes.length === 0) {
      lines.pop();
    }

    /* codeEl をクリアして行spanを挿入 */
    codeEl.textContent = '';
    lines.forEach(function (lineFragment, idx) {
      var lineSpan = document.createElement('span');
      lineSpan.className = 'c-codeBlock__line';

      if (lineFragment.childNodes.length === 0) {
        lineSpan.appendChild(document.createTextNode(' '));
      } else {
        lineSpan.appendChild(lineFragment);
      }

      if (idx > 0) {
        codeEl.appendChild(document.createTextNode('\n'));
      }
      codeEl.appendChild(lineSpan);
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
      var label = langLabel(lang);

      /* ※ wrapLines() はここでは呼ばない（Prismハイライト後に実行） */

      /* ラッパー作成 */
      var wrapper = document.createElement('div');
      wrapper.className = 'c-codeBlock';

      /* 言語ラベル（フローティング） */
      if (label) {
        var langSpan = document.createElement('span');
        langSpan.className = 'c-codeBlock__lang';
        langSpan.innerHTML = label + ' ' + ICON_CHEVRON;
        wrapper.appendChild(langSpan);
      }

      /* コピーボタン（アイコンのみ） */
      var copyBtn = document.createElement('button');
      copyBtn.className = 'c-codeBlock__copy';
      copyBtn.type = 'button';
      copyBtn.setAttribute('aria-label', 'Copy code');
      copyBtn.innerHTML = ICON_COPY;
      copyBtn.addEventListener('click', function () {
        handleCopy(copyBtn, codeEl);
      });
      wrapper.appendChild(copyBtn);

      /* ボディ */
      var body = document.createElement('div');
      body.className = 'c-codeBlock__body';

      /* 既存の pre を移動 */
      block.parentNode.insertBefore(wrapper, block);
      body.appendChild(block);
      wrapper.appendChild(body);

      /* wp-block-code の元スタイルをリセット */
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

      /* ※ wrapLines() はここでは呼ばない（Prismハイライト後に実行） */

      /* 初期表示 */
      tabs[0].setAttribute('aria-selected', 'true');
      panels[0].classList.add('is-active');

      /* タブクリック */
      tabs.forEach(function (tab, i) {
        tab.addEventListener('click', function () {
          tabs.forEach(function (t) { t.setAttribute('aria-selected', 'false'); });
          panels.forEach(function (p) { p.classList.remove('is-active'); });
          tab.setAttribute('aria-selected', 'true');
          if (panels[i]) panels[i].classList.add('is-active');
        });
      });

      /* キーボード（左右矢印） */
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

      /* コピーボタン */
      if (copyBtn) {
        copyBtn.innerHTML = ICON_COPY;
        copyBtn.addEventListener('click', function () {
          var active = group.querySelector('.c-codeGroup__panel.is-active code');
          if (active) handleCopy(copyBtn, active);
        });
      }
    });
  }

  /* ========== 全コードブロックに行番号を適用 ========== */
  function applyLineWrapping() {
    var codes = document.querySelectorAll('.c-codeBlock__body code, .c-codeGroup__panel code');
    codes.forEach(function (codeEl) {
      wrapLines(codeEl);
    });
  }

  /* ========== 初期化 ========== */
  function init() {
    /* ステップ1: DOMラッパー構築（wrapLines なし） */
    wrapWpCodeBlocks();
    initCodeGroups();

    /* ステップ2: Prismによるシンタックスハイライト */
    if (window.Prism) {
      Prism.highlightAll();
    }

    /* ステップ3: トークン化済みDOMに対して行番号を付与 */
    applyLineWrapping();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
