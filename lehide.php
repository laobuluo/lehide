<?php
/**
 * Plugin Name: LeHide
 * Plugin URI: https://www.lezaiyun.com/886.html
 * Description: 这款插件适合用于WordPress公众号涨粉功能，界面美观实用。（公众号：老蒋朋友圈）
 * Version: 1.0.0
 * Author: 老蒋和他的小伙伴
 * Author URI: https://www.lezaiyun.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: lehide
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('LEHIDE_VERSION', '1.0.0');
define('LEHIDE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LEHIDE_PLUGIN_URL', plugin_dir_url(__FILE__));

// 包含必要的文件
require_once LEHIDE_PLUGIN_DIR . 'includes/admin-settings.php';
require_once LEHIDE_PLUGIN_DIR . 'includes/shortcode.php';
require_once LEHIDE_PLUGIN_DIR . 'includes/editor-button.php';

// 插件激活时的钩子
register_activation_hook(__FILE__, 'lehide_activate');

// 插件停用时的钩子
register_deactivation_hook(__FILE__, 'lehide_deactivate');

// 插件卸载时的钩子
register_uninstall_hook(__FILE__, 'lehide_uninstall');

// 激活插件时的操作
function lehide_activate() {
    // 添加默认选项
    add_option('lehide_enabled', '1');
    add_option('lehide_qrcode_url', '');
    add_option('lehide_verification_code', '123456');
    add_option('lehide_module_text', '隐藏内容，扫码公众号查看，发【验证码】获验证码');
}

// 停用插件时的操作
function lehide_deactivate() {
    // 停用时的清理工作
}

// 卸载插件时的操作
function lehide_uninstall() {
    // 删除插件选项
    delete_option('lehide_enabled');
    delete_option('lehide_qrcode_url');
    delete_option('lehide_verification_code');
    delete_option('lehide_module_text');
}

// WordPress.org 会自动加载翻译文件，无需手动调用 load_plugin_textdomain()

// 加载CSS和JavaScript文件
function lehide_enqueue_scripts() {
    wp_enqueue_style('lehide-style', LEHIDE_PLUGIN_URL . 'assets/css/style.css', array(), LEHIDE_VERSION);
    wp_enqueue_script('lehide-script', LEHIDE_PLUGIN_URL . 'assets/js/script.js', array('jquery'), LEHIDE_VERSION, true);
    
    // 添加AJAX URL
    wp_localize_script('lehide-script', 'lehide_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('lehide-nonce')
    ));
}
add_action('wp_enqueue_scripts', 'lehide_enqueue_scripts');

// 在管理页面加载CSS和JavaScript文件
function lehide_admin_enqueue_scripts($hook) {
    if ('settings_page_lehide-settings' === $hook) {
        wp_enqueue_media();
    }
    
    // 在编辑页面加载编辑器按钮样式
    if (in_array($hook, ['post.php', 'post-new.php'])) {
        wp_enqueue_style(
            'lehide-editor-style',
            LEHIDE_PLUGIN_URL . 'assets/css/editor-style.css',
            array(),
            LEHIDE_VERSION
        );
    }
}
add_action('admin_enqueue_scripts', 'lehide_admin_enqueue_scripts'); 