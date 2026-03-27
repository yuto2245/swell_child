/**
 * コードブロック エディタ拡張
 * WordPress core/code ブロックに言語セレクタを追加する
 */
(function (wp) {
  'use strict';

  var el = wp.element.createElement;
  var addFilter = wp.hooks.addFilter;
  var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var PanelBody = wp.components.PanelBody;
  var SelectControl = wp.components.SelectControl;
  var Fragment = wp.element.Fragment;

  /* 対応言語リスト */
  var LANGUAGES = [
    { label: '言語を選択', value: '' },
    { label: 'ABAP', value: 'abap' },
    { label: 'Bash', value: 'bash' },
    { label: 'C', value: 'c' },
    { label: 'C#', value: 'csharp' },
    { label: 'C++', value: 'cpp' },
    { label: 'CSS', value: 'css' },
    { label: 'Diff', value: 'diff' },
    { label: 'Go', value: 'go' },
    { label: 'HTML', value: 'markup' },
    { label: 'Java', value: 'java' },
    { label: 'JavaScript', value: 'javascript' },
    { label: 'JSON', value: 'json' },
    { label: 'Kotlin', value: 'kotlin' },
    { label: 'PHP', value: 'php' },
    { label: 'Python', value: 'python' },
    { label: 'Ruby', value: 'ruby' },
    { label: 'Rust', value: 'rust' },
    { label: 'SQL', value: 'sql' },
    { label: 'Swift', value: 'swift' },
    { label: 'TypeScript', value: 'typescript' },
    { label: 'XML', value: 'xml' },
    { label: 'YAML', value: 'yaml' }
  ];

  /* 1. core/code ブロックにカスタム属性を追加 */
  addFilter(
    'blocks.registerBlockType',
    'swell-child/code-block-lang',
    function (settings, name) {
      if (name !== 'core/code') return settings;

      return Object.assign({}, settings, {
        attributes: Object.assign({}, settings.attributes, {
          codeLanguage: {
            type: 'string',
            default: ''
          }
        })
      });
    }
  );

  /* 2. エディタのサイドバーに言語セレクタを追加 */
  var withLanguageControl = createHigherOrderComponent(function (BlockEdit) {
    return function (props) {
      if (props.name !== 'core/code') {
        return el(BlockEdit, props);
      }

      var codeLanguage = props.attributes.codeLanguage || '';

      return el(
        Fragment,
        null,
        el(BlockEdit, props),
        el(
          InspectorControls,
          null,
          el(
            PanelBody,
            { title: 'コードブロック設定', initialOpen: true },
            el(SelectControl, {
              label: 'プログラミング言語',
              value: codeLanguage,
              options: LANGUAGES,
              onChange: function (value) {
                props.setAttributes({ codeLanguage: value });
              }
            })
          )
        )
      );
    };
  }, 'withLanguageControl');

  addFilter(
    'editor.BlockEdit',
    'swell-child/code-block-lang-control',
    withLanguageControl
  );

  /* 3. 保存時にクラスを付与（エディタ側の extraProps） */
  addFilter(
    'blocks.getSaveContent.extraProps',
    'swell-child/code-block-lang-class',
    function (extraProps, blockType, attributes) {
      if (blockType.name !== 'core/code') return extraProps;

      if (attributes.codeLanguage) {
        var cls = 'language-' + attributes.codeLanguage;
        extraProps.className = extraProps.className
          ? extraProps.className + ' ' + cls
          : cls;
      }

      return extraProps;
    }
  );
})(window.wp);
