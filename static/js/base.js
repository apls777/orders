var base = {
    showSuccessAlert: function(el, msg) {
        this.showAlert(el, 'success', msg);
    },
    showInfoAlert: function(el, msg) {
        this.showAlert(el, 'info', msg);
    },
    showWarningAlert: function(el, msg) {
        this.showAlert(el, 'warning', msg);
    },
    showErrorAlert: function(el, msg) {
        this.showAlert(el, 'danger', msg);
    },
    showAlert: function(el, type, msg) {
        var msgEl = $(el);
        msgEl.html('<div class="alert alert-' + type + '"><p class="text-small">' + msg + '</p></div>');
        msgEl.slideDown('slow');
        setTimeout(function() {
            msgEl.slideUp('slow');
        }, 3000);
    },
    popup: {
        counter: 0,
        queue: [],
        open: function(html) {
            this.counter++;
            this.queue[this.counter] = true;
            var popupHtml = ''+
                '<div id="popup-' + this.counter + '" class="modal" tabindex="-1" role="dialog" aria-hidden="true">'+
                    '<div class="modal-dialog">'+
                        '<div class="modal-content">'+html+'</div>'+
                    '</div>'+
                '</div>';
            $(popupHtml).modal();
            return this.counter;
        },
        close: function(popupId) {
            popupId = popupId || false;
            if (!popupId) {
                for (var i = this.counter; i > 0; i--) {
                    if (this.queue[i]) {
                        popupId = i;
                        break;
                    }
                }
            }
            var popupEl = $('#popup-' + popupId);
            if (popupEl.length) {
                popupEl.modal('hide').remove();
                delete this.queue[popupId];
            }
        }
    },
    signOut: function() {
        $.post('/ajax/signOut/', function () {
            window.location.href = '/';
        });
    }
};