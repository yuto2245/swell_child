(function () {
  'use strict';

  var conversationHistory = [];
  var currentModel = null;
  var currentModelLabel = '';
  var currentModelType = '';
  var isStreaming = false;
  var webSearchEnabled = false;

  var messagesContainer = document.getElementById('chat-messages');
  var textarea = document.getElementById('chat-textarea');
  var sendBtn = document.getElementById('chat-send');
  var newChatBtn = document.getElementById('chat-new');

  var dropdown = document.getElementById('chat-model-dropdown');
  var trigger = document.getElementById('chat-model-trigger');
  var menu = document.getElementById('chat-model-menu');
  var iconEl = document.getElementById('chat-model-icon');
  var labelEl = document.getElementById('chat-model-label');
  var composerModelEl = document.getElementById('chat-composer-model');

  /* プラスメニュー要素 */
  var plusMenu = document.getElementById('chat-plus-menu');
  var plusTrigger = document.getElementById('chat-plus-trigger');

  function init() {
    if (!window.chatConfig) return;

    /* html admin-bar余白をリセット */
    document.documentElement.style.marginTop = '0';


    if (chatConfig.models && chatConfig.models.length > 0) {
      chatConfig.models.forEach(function (m) {
        var item = document.createElement('button');
        item.className = 'chat-dropdown__item';
        item.type = 'button';
        item.innerHTML = '<img src="' + chatConfig.iconBaseUrl + m.icon + '" class="chat-dropdown__item-icon" alt="">'
            + '<span>' + m.label + '</span>';
        item.addEventListener('click', function () {
          selectModel(m);
          closeDropdown();
        });
        menu.appendChild(item);
      });
      selectModel(chatConfig.models[0]);
    }

    trigger.addEventListener('click', function () { dropdown.classList.toggle('is-open'); });
    document.addEventListener('click', function (e) { if (!dropdown.contains(e.target)) closeDropdown(); });
    sendBtn.addEventListener('click', handleSend);
    textarea.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); handleSend(); }
    });
    textarea.addEventListener('input', autoResize);
    newChatBtn.addEventListener('click', handleNewChat);

    /* プラスメニュー */
    if (plusTrigger && plusMenu) {
      plusTrigger.addEventListener('click', function () { plusMenu.classList.toggle('is-open'); });
      document.addEventListener('click', function (e) { if (!plusMenu.contains(e.target)) plusMenu.classList.remove('is-open'); });
    }

    /* ウェブ検索トグル */
    var wsToggle = document.getElementById('chat-web-search-toggle');
    if (wsToggle) {
      wsToggle.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        webSearchEnabled = !webSearchEnabled;
        wsToggle.classList.toggle('is-active', webSearchEnabled);
      });
    }
  }

  function selectModel(m) {
    currentModel = m.id;
    currentModelLabel = m.label;
    currentModelType = m.type;
    iconEl.src = chatConfig.iconBaseUrl + m.icon;
    labelEl.textContent = m.label;
    composerModelEl.textContent = m.label;
  }

  function closeDropdown() { dropdown.classList.remove('is-open'); }

  function autoResize() {
    textarea.style.height = 'auto';
    var maxH = parseFloat(getComputedStyle(textarea).lineHeight) * 5 || 120;
    textarea.style.height = Math.min(textarea.scrollHeight, maxH) + 'px';
  }

  function handleNewChat() {
    conversationHistory = [];
    messagesContainer.innerHTML = '';
    textarea.value = '';
    textarea.style.height = 'auto';
    document.body.classList.add('is-empty');
    textarea.focus();
  }

  async function handleSend() {
    var content = textarea.value.trim();
    if (!content || isStreaming) return;

    conversationHistory.push({ role: 'user', content: content });
    appendMessage('user', 'You', content);

    textarea.value = '';
    textarea.style.height = 'auto';
    setLoading(true);

    var aiMsg = appendMessage('ai', currentModelLabel, '');
    var aiContent = aiMsg.querySelector('.chat-msg__content');
    aiContent.innerHTML = '<span class="chat-cursor"></span>';

    var fullText = '';

    try {
      var formData = new FormData();
      formData.append('action', 'swell_chat_stream');
      formData.append('_wpnonce', chatConfig.nonce);
      formData.append('model', currentModel);
      formData.append('type', currentModelType);
      formData.append('messages', JSON.stringify(conversationHistory));
      formData.append('web_search', webSearchEnabled ? '1' : '0');

      var response = await fetch(chatConfig.ajaxUrl, {
        method: 'POST',
        body: formData
      });

      if (!response.ok) throw new Error('HTTP ' + response.status);

      var reader = response.body.getReader();
      var decoder = new TextDecoder();
      var buffer = '';

      while (true) {
        var result = await reader.read();
        if (result.done) break;
        buffer += decoder.decode(result.value, { stream: true });
        var parts = buffer.split('\n\n');
        buffer = parts.pop();
        for (var i = 0; i < parts.length; i++) {
          var line = parts[i].trim();
          if (!line.startsWith('data: ')) continue;
          var jsonStr = line.slice(6);
          if (jsonStr === '[DONE]') continue;
          try {
            var data = JSON.parse(jsonStr);
            if (data.error) { aiContent.innerHTML = '<span class="chat-error">Error: ' + escapeHtml(data.error) + '</span>'; break; }
            if (data.token) { fullText += data.token; aiContent.innerHTML = renderMarkdown(fullText) + '<span class="chat-cursor"></span>'; scrollToBottom(); }
          } catch (e) {}
        }
      }

      aiContent.innerHTML = renderMarkdown(fullText);
      if (fullText) conversationHistory.push({ role: 'assistant', content: fullText });
    } catch (err) {
      aiContent.innerHTML = '<span class="chat-error">Error: ' + escapeHtml(err.message) + '</span>';
    } finally {
      setLoading(false);
      scrollToBottom();
    }
  }

  function setLoading(loading) {
    isStreaming = loading;
    textarea.disabled = loading;
    sendBtn.disabled = loading;
    sendBtn.classList.toggle('is-loading', loading);
  }

  function appendMessage(role, label, content) {
    document.body.classList.remove('is-empty');
    var msg = document.createElement('div');
    msg.className = 'chat-msg chat-msg--' + role;
    var labelDiv = document.createElement('div');
    labelDiv.className = 'chat-msg__label';
    labelDiv.textContent = label;
    var contentEl = document.createElement('div');
    contentEl.className = 'chat-msg__content';
    contentEl.innerHTML = role === 'user' ? escapeHtml(content).replace(/\n/g, '<br>') : content;
    msg.appendChild(labelDiv);
    msg.appendChild(contentEl);
    messagesContainer.appendChild(msg);
    scrollToBottom();
    return msg;
  }

  function scrollToBottom() { messagesContainer.scrollTop = messagesContainer.scrollHeight; }

  function escapeHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
  }

  function renderMarkdown(text) {
    var codeBlocks = [];
    text = text.replace(/```(\w*)\n([\s\S]*?)```/g, function (_, lang, code) {
      var idx = codeBlocks.length;
      var cls = lang ? ' class="language-' + escapeHtml(lang) + '"' : '';
      codeBlocks.push('<pre class="chat-code-block"><code' + cls + '>' + escapeHtml(code.replace(/\n$/, '')) + '</code></pre>');
      return '\x00CB' + idx + '\x00';
    });
    var inlineCodes = [];
    text = text.replace(/`([^`\n]+)`/g, function (_, code) {
      var idx = inlineCodes.length;
      inlineCodes.push('<code class="chat-inline-code">' + escapeHtml(code) + '</code>');
      return '\x00IC' + idx + '\x00';
    });
    var paragraphs = text.split(/\n\n+/);
    var output = [];
    for (var p = 0; p < paragraphs.length; p++) {
      var para = paragraphs[p].trim();
      if (!para) continue;
      if (/^\x00CB\d+\x00$/.test(para)) { output.push(para); continue; }
      var lines = para.split('\n');
      var isUl = lines.every(function (l) { return /^\s*[-*]\s/.test(l) || !l.trim(); });
      var isOl = lines.every(function (l) { return /^\s*\d+\.\s/.test(l) || !l.trim(); });
      if (isUl) {
        var html = '<ul>';
        for (var j = 0; j < lines.length; j++) { var li = lines[j].replace(/^\s*[-*]\s/, '').trim(); if (li) html += '<li>' + inlineFmt(li) + '</li>'; }
        output.push(html + '</ul>');
      } else if (isOl) {
        var html = '<ol>';
        for (var j = 0; j < lines.length; j++) { var li = lines[j].replace(/^\s*\d+\.\s/, '').trim(); if (li) html += '<li>' + inlineFmt(li) + '</li>'; }
        output.push(html + '</ol>');
      } else {
        var hp = [];
        for (var j = 0; j < lines.length; j++) { var hm = lines[j].match(/^(#{1,6})\s+(.+)/); if (hm) { hp.push('<h' + hm[1].length + '>' + inlineFmt(hm[2]) + '</h' + hm[1].length + '>'); } else { hp.push(inlineFmt(lines[j])); } }
        var joined = hp.join('<br>');
        output.push(/^<h\d>/.test(joined) ? joined : '<p>' + joined + '</p>');
      }
    }
    var result = output.join('');
    result = result.replace(/\x00CB(\d+)\x00/g, function (_, i) { return codeBlocks[+i]; });
    result = result.replace(/\x00IC(\d+)\x00/g, function (_, i) { return inlineCodes[+i]; });
    return result;
  }

  function inlineFmt(text) { return text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>'); }

  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
