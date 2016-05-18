(function($){
	$.fn.imgSlider = function(){
		return this.each(function(){
			var lis = $('li', this).hide(),len = lis.length, i = 0;
			function showItem(){
				var l = i++;
				if(i>=len){
					i = 0;
				}
				$(lis[l]).fadeIn(function(){
					var me = this;
					setTimeout(function(){
						$(me).fadeOut(function(){
							showItem();
						});
					},5000)
				})
			}
			showItem();
		})
	}
})(jQuery)