$(document).ready(function(){

	extraServObj.init({
		data: {
			extraServQuantity: 0,
			extraServOldQuantity: 0,
			floorRiseChecked: false,
			elevatorChecked: false
		}
	});

	BX.addCustomEvent('onRefreshOrder', onRefreshOrderHandler);
});

function onRefreshOrderHandler(orderResult){
	extraServObj.refresh({
		orderResult: orderResult
	});
}

extraServObj = {
	data: {},
	orderResult: {},
	$obj: '',
	orderReload: '',

	init: function(parameters){
		$obj = this;
		this.data = parameters.data || {};
	},

	refresh: function(parameters){
		this.orderResult = parameters.orderResult || {};
		this.onLoad();
	},

	onLoad: function(){
		$obj.floorRiseHeedHandler();
		$obj.elevatorHandler();
		$obj.changeQuantityHandler();
	},

	floorRiseHeedHandler: function(){
		$obj.data.extraServQuantity = $('.delivery-lifting-cost input[type="hidden"]').val();
		$('.delivery-lifting-cost input[type="text"]').val($obj.data.extraServOldQuantity);

		if(!$obj.data.elevatorChecked){
			$obj.data.extraServOldQuantity = $('.delivery-lifting-cost input[type="text"]').val();
		}

		if($obj.data.extraServQuantity != 0){
			$('.floor-rise-need-block .switch_block input[type="checkbox"]').attr('checked', true);
		}
		$obj.checkLiftingCostActive($('.floor-rise-need-block .switch_block input[type="checkbox"]'));


		if(!$obj.data.elevatorChecked && $obj.data.floorRiseChecked){
			$('.delivery-lifting-cost .switch_block_counter').removeClass('disabled');
		}
		else{
			$('.delivery-lifting-cost .switch_block_counter').addClass('disabled');
		}

		
		$('.floor-rise-need-block input[type="checkbox"]').on("click", function(){
			elem = $(this);
			if($obj.data.extraServOldQuantity == 0){
				$obj.data.extraServOldQuantity = 1;
			}
			$obj.checkLiftingCostActive($(this).parents('.floor-rise-need-block').find('input[type="checkbox"]'));

			setTimeout(function(){
				elem.parents('.delivery-lifting-cost').find('input[type="hidden"]').trigger('onchange');
			}, 100);

		});
	},

	elevatorHandler: function(){
		if($obj.data.elevatorChecked){
			$('.elevator-block .switch_block input[type="checkbox"]').attr('checked', true);
		}
		$obj.checkElevatorActive($('.elevator-block .switch_block input[type="checkbox"]'));

		$('.elevator-block input[type="checkbox"]').on("click", function(){
			elem = $(this);
			$obj.checkElevatorActive($(this).parents('.elevator-block').find('input[type="checkbox"]'));
			setTimeout(function(){
				elem.parents('.delivery-lifting-cost').find('input[type="hidden"]').trigger('onchange');
			}, 100);
		});
	},

	changeQuantityHandler: function(){
		$('.delivery-lifting-cost .switch_block_counter span').on("click", function(){
			elem = $(this);
			if(!elem.parents('.switch_block_counter').is('.disabled') && !$obj.data.elevatorChecked){
				clearTimeout($obj.orderReload);
				var type = elem.attr('data-type');
				var quantity = elem.parents('.delivery-lifting-cost').find('input[type="text"]').val();
				if(type == 'plus'){
					quantity++;
				}
				else{
					quantity--;
				}
				if(quantity < 0){
					quantity = 0;
				}
				elem.parents('.delivery-lifting-cost').find('input[type="text"]').val(quantity);
				elem.parents('.delivery-lifting-cost').find('input[type="hidden"]').val(quantity);
				$obj.data.extraServOldQuantity = quantity;

				$obj.orderReload = setTimeout(function(){
					elem.parents('.delivery-lifting-cost').find('input[type="hidden"]').trigger('onchange');
				}, 600);
			}
		});

		$('.delivery-lifting-cost .switch_block_counter input[type="text"]').on("change", function(){
			elem = $(this);
			if(!elem.parents('.switch_block_counter').is('.disabled') && !$obj.data.elevatorChecked){
				var quantity = elem.val();
				elem.parents('.delivery-lifting-cost').find('input[type="hidden"]').val(quantity);
				$obj.data.extraServOldQuantity = quantity;
				$obj.orderReload = setTimeout(function(){
					elem.parents('.delivery-lifting-cost').find('input[type="hidden"]').trigger('onchange');
				}, 600);
			}
		});
	},

	checkLiftingCostActive: function(elem){
		if(!elem.is(':checked')){
			elem.parents('.delivery-lifting-cost').children('.switch_block_counter').addClass('disabled').children('input[type="hidden"]').attr('disabled', true).val(0);
			//elem.parents('.delivery-lifting-cost').children('.elevator-block').addClass('disabled').find('input[type="checkbox"]').attr('checked', false);
			//$obj.data.elevatorChecked = false;
			$obj.data.floorRiseChecked = false;
		}
		else{
			elem.parents('.delivery-lifting-cost').children('.switch_block_counter').removeClass('disabled').children('input[type="hidden"]').attr('disabled', false).val($obj.data.extraServOldQuantity);
			elem.parents('.delivery-lifting-cost').children('.elevator-block')/*.addClass('disabled')*/.find('input[type="checkbox"]').attr('checked', false);
			//elem.parents('.delivery-lifting-cost').children('.elevator-block').removeClass('disabled');
			$obj.data.floorRiseChecked = true;
			$obj.data.elevatorChecked = false;
			$('input[data-id=liftPriceUpdate]').val(0);
		}
	},

	checkElevatorActive: function(elem){
		if(!elem.is(':checked')){
			elem.parents('.delivery-lifting-cost').find('.switch_block_counter input[type="hidden"]').val($obj.data.extraServOldQuantity);
			$obj.data.elevatorChecked = false;
			$('input[data-id=liftPriceUpdate]').val(0);
		}
		else{
			elem.parents('.delivery-lifting-cost').find('.switch_block_counter input[type="hidden"]').val(1);
			$obj.data.elevatorChecked = true;
			$('input[data-id=liftPriceUpdate]').val(1);
			$obj.data.floorRiseChecked = false;
			elem.parents('.delivery-lifting-cost').children('.switch_block_counter').addClass('disabled').children('input[type="hidden"]').attr('disabled', true).val(0);
		}
	}
}