/**
 * LeHide Gutenberg编辑器快捷方式
 * 为块编辑器添加插入短代码的功能
 */
(function() {
    'use strict';

    // 等待WordPress编辑器加载完成
    if (typeof wp === 'undefined' || typeof wp.domReady === 'undefined') {
        return;
    }

    wp.domReady(function() {
        // 检查必要的WordPress API是否可用
        if (typeof wp.blocks === 'undefined' || typeof wp.element === 'undefined') {
            return;
        }

        var el = wp.element.createElement;
        var __ = wp.i18n.__;
        var registerBlockType = wp.blocks.registerBlockType;
        var InspectorControls = wp.blockEditor ? wp.blockEditor.InspectorControls : wp.editor.InspectorControls;
        var TextControl = wp.components ? wp.components.TextControl : null;

        // 为短代码块添加LeHide快捷插入功能
        // 用户可以直接在短代码块中输入 [lehide]...[/lehide]
        
        // 添加插入提示（通过控制台）
        if (typeof console !== 'undefined') {
            console.log('LeHide: 在块编辑器中，您可以：');
            console.log('1. 添加"短代码"块，然后输入 [lehide]这里是需要隐藏的内容[/lehide]');
            console.log('2. 或者在段落块中直接输入短代码');
        }

        // 如果支持插件API，可以添加工具栏按钮
        if (wp.plugins && wp.plugins.registerPlugin && wp.editPost) {
            var PluginSidebar = wp.editPost ? wp.editPost.PluginSidebar : null;
            var PluginSidebarMoreMenuItem = wp.editPost ? wp.editPost.PluginSidebarMoreMenuItem : null;
            
            if (PluginSidebar && PluginSidebarMoreMenuItem) {
                // 这里可以添加侧边栏插件，但为了简化，我们只提供控制台提示
            }
        }
    });
})();
