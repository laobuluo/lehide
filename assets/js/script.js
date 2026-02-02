jQuery(document).ready(function($) {
    function showError($form, message) {
        var $errorMessage = $form.find('.lehide-error-message');
        var $input = $form.find('.lehide-verify-input');
        
        $errorMessage.text(message).addClass('show');
        $input.addClass('error');
        
        setTimeout(function() {
            $errorMessage.removeClass('show');
            $input.removeClass('error');
        }, 3000);
    }

    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length === 2) {
            return parts.pop().split(";").shift();
        }
        return null;
    }

    function isVerificationValid(postId, verificationTimes) {
        if (!verificationTimes || !verificationTimes[postId]) {
            return false;
        }
        
        var verifyTime = parseInt(verificationTimes[postId]);
        var now = Math.floor(Date.now() / 1000);
        return (now - verifyTime) < (7 * 24 * 3600); // 7天内有效
    }

    // 处理验证码提交
    $('.lehide-verify-button').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var form = button.closest('.lehide-verify-form');
        var input = form.find('.lehide-verify-input');
        var contentId = button.data('content-id');
        var postId = button.data('post-id');
        var code = input.val().trim();
        
        if (!code) {
            showError(form, '请输入验证码');
            return;
        }
        
        button.prop('disabled', true).text('验证中...');
        
        $.ajax({
            url: lehide_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'lehide_verify_code',
                code: code,
                content_id: contentId,
                post_id: postId,
                nonce: lehide_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // 显示获取到的内容
                    $('#' + contentId)
                        .html(response.data.content)
                        .slideDown();
                    // 隐藏验证模块
                    button.closest('.lehide-content-wrapper').slideUp();
                } else {
                    showError(form, response.data.message);
                    button.prop('disabled', false).text('提交');
                }
            },
            error: function() {
                showError(form, '验证请求失败，请重试');
                button.prop('disabled', false).text('提交');
            }
        });
    });
    
    // 检查当前文章是否已验证且在有效期内
    var verifiedPosts = getCookie('lehide_verified_posts');
    var verificationTimes = getCookie('lehide_verification_times');
    
    if (verifiedPosts && verificationTimes) {
        try {
            verifiedPosts = JSON.parse(decodeURIComponent(verifiedPosts));
            verificationTimes = JSON.parse(decodeURIComponent(verificationTimes));
            
            if (Array.isArray(verifiedPosts)) {
                // 遍历所有验证按钮
                $('.lehide-verify-button').each(function() {
                    var button = $(this);
                    var postId = parseInt(button.data('post-id'));
                    
                    // 如果文章已验证且在有效期内，直接刷新页面获取内容
                    if (verifiedPosts.indexOf(postId) !== -1 && isVerificationValid(postId, verificationTimes)) {
                        location.reload();
                        return false; // 终止循环
                    }
                });
            }
        } catch (e) {
            console.error('Error parsing verification data:', e);
            // 清除可能损坏的cookie
            document.cookie = 'lehide_verified_posts=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
            document.cookie = 'lehide_verification_times=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        }
    }
}); 