<?php
// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 注册短代码
function lehide_register_shortcode(): void {
    add_shortcode('lehide', 'lehide_shortcode_handler');
}
add_action('init', 'lehide_register_shortcode');

// 短代码处理函数
function lehide_shortcode_handler(?array $atts = [], ?string $content = null): string {
    // 如果插件未启用，直接显示内容
    if (get_option('lehide_enabled') !== '1') {
        return do_shortcode((string)$content);
    }

    // 获取当前文章ID
    $post_id = get_the_ID();
    
    // 生成唯一ID
    $unique_id = 'lehide_' . uniqid();
    
    // 获取设置
    $qrcode_url = get_option('lehide_qrcode_url', '');
    $module_text = get_option('lehide_module_text', '');
    
    // 检查当前文章是否已验证
    $verified_posts = [];
    $verification_times = [];
    
    // 安全地获取和清理 cookie 值
    $verified_posts_cookie = filter_input(INPUT_COOKIE, 'lehide_verified_posts', FILTER_UNSAFE_RAW);
    if ($verified_posts_cookie !== null && $verified_posts_cookie !== false) {
        $cookie_value = wp_unslash($verified_posts_cookie);
        $cookie_value = sanitize_text_field($cookie_value);
        $decoded = json_decode($cookie_value, true);
        $verified_posts = is_array($decoded) ? $decoded : [];
    }
    
    $verification_times_cookie = filter_input(INPUT_COOKIE, 'lehide_verification_times', FILTER_UNSAFE_RAW);
    if ($verification_times_cookie !== null && $verification_times_cookie !== false) {
        $cookie_value = wp_unslash($verification_times_cookie);
        $cookie_value = sanitize_text_field($cookie_value);
        $decoded = json_decode($cookie_value, true);
        $verification_times = is_array($decoded) ? $decoded : [];
    }
    
    // 验证cookie数据的有效性
    if (!is_array($verified_posts) || !is_array($verification_times)) {
        $verified_posts = [];
        $verification_times = [];
        setcookie('lehide_verified_posts', json_encode([]), [
            'expires' => time() + (7 * 24 * 3600),
            'path' => '/',
            'samesite' => 'Lax'
        ]);
        setcookie('lehide_verification_times', json_encode([]), [
            'expires' => time() + (7 * 24 * 3600),
            'path' => '/',
            'samesite' => 'Lax'
        ]);
    }
    
    // 检查是否需要重新验证（7天后）
    $needs_verification = true;
    if (in_array($post_id, $verified_posts, true) && isset($verification_times[$post_id])) {
        $verify_time = (int)$verification_times[$post_id];
        if (time() - $verify_time < 7 * 24 * 3600) { // 7天内验证过
            $needs_verification = false;
        } else {
            // 移除过期的验证状态
            $verified_posts = array_diff($verified_posts, [$post_id]);
            unset($verification_times[$post_id]);
            setcookie('lehide_verified_posts', json_encode($verified_posts), [
                'expires' => time() + (7 * 24 * 3600),
                'path' => '/',
                'samesite' => 'Lax'
            ]);
            setcookie('lehide_verification_times', json_encode($verification_times), [
                'expires' => time() + (7 * 24 * 3600),
                'path' => '/',
                'samesite' => 'Lax'
            ]);
        }
    }

    if (!$needs_verification) {
        return do_shortcode((string)$content);
    }

    // 将内容存储在临时数据中，设置1小时过期
    set_transient($unique_id, [
        'content' => $content,
        'post_id' => $post_id,
        'time' => time()
    ], HOUR_IN_SECONDS);

    // 构建隐藏内容的HTML
    $output = '<div class="lehide-container">';
    $output .= '<div class="lehide-content-wrapper">';
    $output .= '<div class="lehide-qrcode">';
    $output .= '<img src="' . esc_url($qrcode_url) . '" alt="QR Code">';
    $output .= '</div>';
    $output .= '<div class="lehide-right-section">';
    $output .= '<div class="lehide-text">' . esc_html($module_text) . '</div>';
    $output .= '<div class="lehide-verify-form">';
    $output .= '<div class="lehide-error-message"></div>';
    $output .= '<input type="text" class="lehide-verify-input" placeholder="请输入验证码">';
    $output .= '<button class="lehide-verify-button" data-content-id="' . esc_attr($unique_id) . '" data-post-id="' . esc_attr((string)$post_id) . '">提交</button>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '<div class="lehide-hidden-content" id="' . esc_attr($unique_id) . '" style="display:none;"></div>';
    $output .= '</div>';

    return $output;
}

// 处理AJAX验证请求
function lehide_verify_code(): void {
    check_ajax_referer('lehide-nonce', 'nonce');
    
    if (!isset($_POST['code'], $_POST['content_id'], $_POST['post_id'])) {
        wp_send_json_error(['message' => '请求参数不完整']);
        return;
    }
    
    $user_code = sanitize_text_field(wp_unslash($_POST['code']));
    $content_id = sanitize_text_field(wp_unslash($_POST['content_id']));
    $post_id = absint(wp_unslash($_POST['post_id']));
    $stored_code = get_option('lehide_verification_code', '');
    
    if ($user_code === $stored_code) {
        // 获取存储的内容
        $stored_data = get_transient($content_id);
        if ($stored_data && is_array($stored_data) && isset($stored_data['content'])) {
            // 验证存储的文章ID
            if ($stored_data['post_id'] !== $post_id) {
                wp_send_json_error(['message' => '验证信息不匹配，请刷新页面重试']);
                return;
            }
            
            // 检查内容是否已过期（1小时）
            if (time() - (int)$stored_data['time'] > HOUR_IN_SECONDS) {
                wp_send_json_error(['message' => '验证信息已过期，请刷新页面重试']);
                return;
            }
            
            // 更新已验证文章的cookie
            $verified_posts = [];
            $verification_times = [];
            
            // 安全地获取和清理 cookie 值
            $verified_posts_cookie = filter_input(INPUT_COOKIE, 'lehide_verified_posts', FILTER_UNSAFE_RAW);
            if ($verified_posts_cookie !== null && $verified_posts_cookie !== false) {
                $cookie_value = wp_unslash($verified_posts_cookie);
                $cookie_value = sanitize_text_field($cookie_value);
                $decoded = json_decode($cookie_value, true);
                $verified_posts = is_array($decoded) ? $decoded : [];
            }
            
            $verification_times_cookie = filter_input(INPUT_COOKIE, 'lehide_verification_times', FILTER_UNSAFE_RAW);
            if ($verification_times_cookie !== null && $verification_times_cookie !== false) {
                $cookie_value = wp_unslash($verification_times_cookie);
                $cookie_value = sanitize_text_field($cookie_value);
                $decoded = json_decode($cookie_value, true);
                $verification_times = is_array($decoded) ? $decoded : [];
            }
            
            if (!is_array($verified_posts)) $verified_posts = [];
            if (!is_array($verification_times)) $verification_times = [];
            
            if (!in_array($post_id, $verified_posts, true)) {
                $verified_posts[] = $post_id;
            }
            
            // 记录验证时间
            $verification_times[$post_id] = time();
            
            // 设置7天过期的cookie
            setcookie('lehide_verified_posts', json_encode($verified_posts), [
                'expires' => time() + (7 * 24 * 3600),
                'path' => '/',
                'samesite' => 'Lax'
            ]);
            setcookie('lehide_verification_times', json_encode($verification_times), [
                'expires' => time() + (7 * 24 * 3600),
                'path' => '/',
                'samesite' => 'Lax'
            ]);
            
            // 删除临时数据
            delete_transient($content_id);
            
            wp_send_json_success([
                'message' => '验证成功',
                'content' => do_shortcode($stored_data['content'])
            ]);
        } else {
            wp_send_json_error(['message' => '内容已过期，请刷新页面重试']);
        }
    } else {
        wp_send_json_error(['message' => '验证码错误，请重试']);
    }
}
add_action('wp_ajax_lehide_verify_code', 'lehide_verify_code');
add_action('wp_ajax_nopriv_lehide_verify_code', 'lehide_verify_code'); 