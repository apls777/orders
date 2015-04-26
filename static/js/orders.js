var orders = {
    requestLock: false,
    saveOrder: function(el) {
        var _this = this;
        if (!_this.requestLock) {
            _this.requestLock = true;
            var formEl = $(el).parents('form');
            $.post('/customer/saveOrder/', formEl.serialize(), function(data) {
                if (data.ret) {
                    window.location.href = '/customer/?new=1';
                } else {
                    base.showErrorAlert(formEl.find('.error-message'), data.message);
                }
                _this.requestLock = false;
            });
        }
    },
    completeOrder: function(el, orderId, token) {
        var _this = this;
        if (!_this.requestLock) {
            _this.requestLock = true;
            $.post('/executor/completeOrder/', { order_id: orderId, token: token }, function(data) {
                if (data.html) {
                    base.popup.open(data.html);
                    $(el).attr('disabled', 'disabled').text(data.button_title);
                    if (data.ret) {
                        $('#user-balance span').text(data.new_balance);
                    }
                } else {
                    alert(data.message);
                }
                _this.requestLock = false;
            });
        }
    }
};