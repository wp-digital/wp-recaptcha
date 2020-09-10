jQuery(function() {
    jQuery('[data-role="innocode_recaptcha_action_button"]').click(function(e) {
        e.preventDefault();
        var _btn = jQuery(this);
        var _gif = _btn.next('.spinner');
        _btn.hide();
        _gif.addClass('is-active');
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: { action: _btn.data('action') },
            success: function() {
                _btn.show();
                _gif.removeClass('is-active');
            }
        });
    });
});