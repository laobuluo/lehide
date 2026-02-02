<?php
// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 为经典编辑器（TinyMCE）添加LeHide按钮
function lehide_add_tinymce_button($buttons) {
    array_push($buttons, 'lehide_button');
    return $buttons;
}
add_filter('mce_buttons', 'lehide_add_tinymce_button');

// 注册TinyMCE插件
function lehide_register_tinymce_plugin($plugin_array) {
    $plugin_array['lehide_button'] = LEHIDE_PLUGIN_URL . 'assets/js/editor-button.js';
    return $plugin_array;
}
add_filter('mce_external_plugins', 'lehide_register_tinymce_plugin');

// 添加TinyMCE编辑器样式
function lehide_add_tinymce_styles($mce_css) {
    if (!empty($mce_css)) {
        $mce_css .= ',';
    }
    $mce_css .= LEHIDE_PLUGIN_URL . 'assets/css/editor-style.css';
    return $mce_css;
}
add_filter('mce_css', 'lehide_add_tinymce_styles');

// 在编辑器中加载脚本
function lehide_enqueue_editor_scripts($hook) {
    // 只在文章/页面编辑页面加载
    if (!in_array($hook, ['post.php', 'post-new.php'])) {
        return;
    }
    
    // 为块编辑器添加快捷方式
    wp_enqueue_script(
        'lehide-gutenberg-shortcut',
        LEHIDE_PLUGIN_URL . 'assets/js/gutenberg-shortcut.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
        LEHIDE_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'lehide_enqueue_editor_scripts');
