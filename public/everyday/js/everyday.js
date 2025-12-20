$(document).ready(function(){
	//$('#inputPhone').mask('+7 (999) 999 99 99');
	$.mask.definitions['c']='[A-Za-z0-9]';
		$("#code").mask("+7 (999) 999 99 99");

	$('.btn').click(function () {
		var next = $(this).data('next');
		if (next) {
			$('.block').hide();
			$(next).show();
		}
		moveToAnchor ('#action', 0)
	});

	$('input[type="checkbox"]').click(function () {
		$(this).removeClass('error');
	});

	$('.helpMess').toggle(function(){
		$(this).show();
	},function(){
		$(this).hide();
	});

	$('.help').click(function () {
		$(this).children('.helpMess').toggle(1);
	});

	$('form[name="regaction"] input').each(function () {
		if ($(this).val() && $(this).attr('type') == 'text') {
			$('label[for="' + $(this).attr('id') + '"]').text($(this).attr('placeholder'));
		}
	});

	$('form[name="regaction"] input[type="text"]').focus(function () {
		$('label[for="' + $(this).attr('id') + '"]').text($(this).attr('placeholder'));
	});

	function moveToAnchor (selector, pos) {
		var destination = $(selector).offset().top - pos;
		$("body:not(:animated)").animate({scrollTop: destination}, 300);
		$('html').animate({scrollTop: destination}, 300);//*/
	}

	// Form everyday submit
    $('form[name="regaction"]').on('submit',function(e){
        console.log('form submit');
		e.preventDefault();

		$('#load_error').empty().hide();
        var thisForm = $(this);

		var validate = true;
		$('form[name="regaction"] input').each(function(index,val){
			$(this).removeClass('error');
			$(this).removeClass('success');

			switch (this.type) {
				case 'hidden':
				case 'text':
					if ($(this).val() == '' || $(this).val() == undefined) {
						$(this).addClass('error');
						validate = false;
					} else {
						$(this).addClass('success');
					}
					break;
				case 'file':
					if ($(this).val() == '' || $(this).val() == undefined) {
						$(this).parent().addClass('error');
						validate = false;
					} else {
						$(this).parent().addClass('success');
					}
					break;
				case 'checkbox':
					if (!this.checked) {
						$(this).addClass('error');
						validate = false;
					} else {
						$(this).addClass('success');
					}
					break;
			}
		});

		if (!/^\+7\s\(\d{3}\)\s\d{3}\s\d{2}\s\d{2}$/.test($('input[name="code"]').val())) {
			//проверить номер телефона по маске +7 (999) 999 99 99
			$('#load_error').append('Неверный номер телефона. (Формат номера телефона: "+7 (999) 999 99 99")<br />').show();
			$('input[name="code"]').removeClass('success');
			$('input[name="code"]').addClass('error');
			validate = false;
		} else {
			$('input[name="code"]').addClass('success');
			$('input[name="code"]').removeClass('error');
		}

		/*if ($('input[name="store"]').val().split(' ').length < 2) {
			//проверить, что название организации состоит минимум из 2 слов
			$('#load_error').append('Название организации должно содержать форму организации. (ООО, ИП и т.п.)<br />').show();
			$('input[name="store"]').removeClass('success');
			$('input[name="store"]').addClass('error');
			validate = false;
		} else {
			$('input[name="store"]').addClass('success');
			$('input[name="store"]').removeClass('error');
		}*/

		//var captcha = grecaptcha.getResponse();

		var formData = new FormData(thisForm.get(0));
		/*if (grecaptcha) {
			formData.append('recaptcha', captcha);
		}//*/

		if (validate /*&& captcha.length*/) {
			dataLayer.push({'event':'formSubmitEvent', 'formName':thisForm.attr('name')});

			$('.everyday .loader').show();
			$.ajax({
				url: thisForm.attr('action'),
				type: 'POST',
				data: formData,
				dataType: 'json',
				processData: false,
				contentType: false,
				success: function(data){
					$('.everyday .loader').hide();
					if (!data.error) {
						$('#step4').hide();
						$('#step5').show();

                        $('h1').text('Спасибо за регистрацию, для участия в акции осталось:').addClass('main_h');
                        $('h1').css({'padding-left':'200px', 'padding-right':'200px'});

                        //$('h1').text('Спасибо за регистрацию').addClass('main_h');
                        // |=>-
                        $('.everyday').css('background', '#fff');
                        $('.block').css('padding', '0');
                        $('.everyday').removeClass('everyday');
                        // |=>-
					} else {
						if (data.code !== undefined) {
							$('#load_error').append('Вы уже зарегистрировали этот номер телефона. ' + data.code).show();
						}
						if (data.textE !== undefined) {
							$('#load_error').append(data.textE.join('<br />')).show();
						}

						var destination = 45;
						$("body:not(:animated)").animate({scrollTop: destination}, 300);
						$('html').animate({scrollTop: destination}, 300);

						grecaptcha.reset();
					}
				},
				error: function( jqXHR, textStatus, errorThrown ){
					$('.everyday .loader').hide();
					$('#load_error').append('Что-то пошло не так.').show();

					grecaptcha.reset();

					var destination = 45;
					$("body:not(:animated)").animate({scrollTop: destination}, 300);
					$('html').animate({scrollTop: destination}, 300);
				}
			});
		} else {
			/*if (!captcha.length) {
				$('#load_error').append('Вы не прошли проверку "Я не робот". Проверка на стороне клиента.<br />').show();
			}

			grecaptcha.reset();//*/

			var destination = 45;
			$("body:not(:animated)").animate({scrollTop: destination}, 300);
			$('html').animate({scrollTop: destination}, 300);
		}

        return false;
    });
});