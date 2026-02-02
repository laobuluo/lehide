<?php
// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 添加管理菜单
function lehide_add_admin_menu() {
    add_options_page(
        'LeHide设置', 
        'LeHide设置',
        'manage_options',
        'lehide-settings',
        'lehide_settings_page'
    );
}
add_action('admin_menu', 'lehide_add_admin_menu');

// 注册设置
function lehide_register_settings() {
    register_setting('lehide_options', 'lehide_enabled', array(
        'sanitize_callback' => 'lehide_sanitize_checkbox'
    ));
    register_setting('lehide_options', 'lehide_qrcode_url', array(
        'sanitize_callback' => 'esc_url_raw'
    ));
    register_setting('lehide_options', 'lehide_verification_code', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('lehide_options', 'lehide_module_text', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));
}
add_action('admin_init', 'lehide_register_settings');

// 清理复选框值
function lehide_sanitize_checkbox($value) {
    return '1' === $value ? '1' : '';
}

// 设置页面HTML
function lehide_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p>在这里，我们需要配置插件的相关选项。<a href="https://www.lezaiyun.com/886.html" target="_blank">插件介绍</a>（关注公众号：<span style="color: red;">老蒋朋友圈</span>）</p>
        <form action="options.php" method="post">
            <?php
            settings_fields('lehide_options');
            do_settings_sections('lehide-settings');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="lehide_enabled">启用插件</label>
                    </th>
                    <td>
                        <input type="checkbox" id="lehide_enabled" name="lehide_enabled" value="1" <?php checked(get_option('lehide_enabled'), '1'); ?>>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="lehide_qrcode_url">二维码图片</label>
                    </th>
                    <td>
                        <input type="text" id="lehide_qrcode_url" name="lehide_qrcode_url" class="regular-text" value="<?php echo esc_attr(get_option('lehide_qrcode_url')); ?>">
                        <button type="button" class="button" id="upload_qrcode_button">选择图片</button>
                        <p class="description">选择或上传公众号二维码图片（建议尺寸：150x150像素）</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="lehide_verification_code">验证码</label>
                    </th>
                    <td>
                        <input type="text" id="lehide_verification_code" name="lehide_verification_code" class="regular-text" value="<?php echo esc_attr(get_option('lehide_verification_code')); ?>">
                        <p class="description">设置验证码，用户需要输入此验证码才能查看隐藏内容</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="lehide_module_text">模块介绍文字</label>
                    </th>
                    <td>
                        <input type="text" id="lehide_module_text" name="lehide_module_text" class="regular-text" value="<?php echo esc_attr(get_option('lehide_module_text')); ?>">
                        <p class="description">设置隐藏模块显示的提示文字</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#upload_qrcode_button').click(function(e) {
            e.preventDefault();
            var image = wp.media({
                title: '选择二维码图片',
                multiple: false
            }).open()
            .on('select', function() {
                var uploaded_image = image.state().get('selection').first();
                var image_url = uploaded_image.toJSON().url;
                $('#lehide_qrcode_url').val(image_url);
            });
        });
    });
    </script>
    <?php
} 