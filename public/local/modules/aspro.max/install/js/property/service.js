BX.ready(function(){
	var workarea = BX('adm-workarea');
	if(workarea){
		var divs = BX.findChild(workarea, {tag: 'div', class: 'aspro_property_service--hidden'}, true, true);
		if(divs){
			for(var i in divs){
				var a = BX.findPreviousSibling(divs[i], {tag: 'a'});
				if(a){
					BX.bind(a, 'click', function(e){
						BX.PreventDefault(e);
						var a = e.target;
						if(div = BX.findNextSibling(a, {tag: 'div'})){
							BX.removeClass(div, 'aspro_property_service--hidden');
							BX.cleanNode(a, true);
						}
					});
				}
			}
		}
	}
});