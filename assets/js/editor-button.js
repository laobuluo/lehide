/**
 * LeHide TinyMCE编辑器按钮
 * 用于在经典编辑器中插入 [lehide]...[/lehide] 短代码
 */
(function() {
    'use strict';

    tinymce.PluginManager.add('lehide_button', function(editor, url) {
        // 添加按钮 - 使用文本而不是图标
        editor.addButton('lehide_button', {
            title: '插入LeHide隐藏内容',
            text: 'LeHide',
            tooltip: '插入LeHide隐藏内容',
            onclick: function() {
                // 检查是否有选中的文本
                var selectedText = editor.selection.getContent({format: 'text'});
                var defaultContent = selectedText || '这里是需要隐藏的内容';
                
                // 打开对话框
                editor.windowManager.open({
                    title: '插入LeHide隐藏内容',
                    body: [
                        {
                            type: 'textbox',
                            name: 'content',
                            label: '隐藏的内容',
                            multiline: true,
                            minWidth: 400,
                            minHeight: 200,
                            value: defaultContent
                        }
                    ],
                    onsubmit: function(e) {
                        var content = e.data.content.trim();
                        if (content) {
                            // 插入短代码
                            editor.insertContent('[lehide]' + content + '[/lehide]');
                        } else {
                            // 如果没有内容，插入默认标签
                            editor.insertContent('[lehide]这里是需要隐藏的内容[/lehide]');
                        }
                    }
                });
            }
        });

        // 添加菜单项
        editor.addMenuItem('lehide_button', {
            text: '插入LeHide隐藏内容',
            context: 'insert',
            onclick: function() {
                var selectedText = editor.selection.getContent({format: 'text'});
                var defaultContent = selectedText || '这里是需要隐藏的内容';
                
                editor.windowManager.open({
                    title: '插入LeHide隐藏内容',
                    body: [
                        {
                            type: 'textbox',
                            name: 'content',
                            label: '隐藏的内容',
                            multiline: true,
                            minWidth: 400,
                            minHeight: 200,
                            value: defaultContent
                        }
                    ],
                    onsubmit: function(e) {
                        var content = e.data.content.trim();
                        if (content) {
                            editor.insertContent('[lehide]' + content + '[/lehide]');
                        } else {
                            editor.insertContent('[lehide]这里是需要隐藏的内容[/lehide]');
                        }
                    }
                });
            }
        });
    });
})();
