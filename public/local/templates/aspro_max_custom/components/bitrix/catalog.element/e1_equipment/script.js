$(document).ready(function () {
    //делает сборку активной и неактивной
    $(".custom_service_chekbox-detail.basket-item-property-custom .switch_block").on("click", function () {
        let self = this;
        let hasClass = $(self).hasClass('active');
        $(".custom_service_chekbox-detail.basket-item-property-custom .switch_block").each(function (el) {
            $(this).removeClass('active');
            $(this).find('input[type=checkbox]').prop('checked', false);
        });

        if (!hasClass) {
            $(self).toggleClass('active');
            if ($(self).hasClass('active')) {
                $(self).find('input[type=checkbox]').prop('checked', true);
            } else {
                $(self).find('input[type=checkbox]').prop('checked', false);
            }
        }
    });
});