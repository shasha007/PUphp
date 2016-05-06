$.extend({
    pay: function (amount, data, callback) {
        //show高度
        var showH = $('.h-PU-show').height();
        //密码盘高度
        var pswH = $('#keyboard').height();
        var paypwd = '';

        $('.h-PU-show').show();
        $('.h-back-cover').show();
        $('.h-show-num').text(amount);
        if ($.isFunction(data)) {
            callback = data;
            data = {};
        }
        $('.h-PU-show .close').click(function () {
            paypwd = '';
            $('.h-PU-show').hide();
            $('.h-back-cover').hide();
            // pw.setAttribute("data-pw", "");
            $('#password li').html('');
            $('#keyboard').css('bottom','-225px')
        });

        $('.h-set ul').off('touchstart').on('touchstart', function () {
            if ($(this).attr('data-inputable')) {
                $('.h-PU-show').css("top",(window.screen.height-showH-pswH)/2+"px");
                $('#keyboard').css('bottom', 0);
                $('#keyboard td').off('touchstart').on('touchstart', function () {
                    if ($(this).attr('data-delete')) {
                        $('#pwd-' + paypwd.length).text('');
                        paypwd = paypwd.substring(0, paypwd.length - 1);
                    } else {
                        if (paypwd.length >= 6) {
                            return false;
                        }
                        paypwd += $(this).text();
                        $('#pwd-' + paypwd.length).text('•');
                        if (paypwd.length == 6) {
                            $('#keyboard').css('bottom', '-225px');
                            $('.h-PU-show').css("top", "20%");
                            data = $.extend(data, {'paypwd' : paypwd});
                            $('.h-PU-show .close').click();
                            callback(data);

                            return true;
                        }
                    }
                });
            }
        });
    }
});