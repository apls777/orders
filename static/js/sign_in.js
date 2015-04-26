var singIn = {
    auth: function(el) {
        var formEl = $(el).parents('form');
        $.post('/ajax/auth/', formEl.serialize(), function(data) {
            if (data.ret) {
                window.location.href = '/';
            } else {
                base.showErrorAlert(formEl.find('.error-message'), data.message);
            }
        });
    },
    register: function(el) {
        var formEl = $(el).parents('form');
        $.post('/ajax/register/', formEl.serialize(), function(data) {
            if (data.ret) {
                window.location.href = '/';
            } else {
                base.showErrorAlert(formEl.find('.error-message'), data.message);
            }
        });
    }
};