$(function(){

	function ie6Filter(s){
		//解决ie6下png32显示的问题
		if($.browser.msie && parseInt($.browser.version)==6){
			var m = /url\s*\(([\'\"]?)(.*?)\1\)/, 
				selector = s || '.png32';
			$(selector).each(function(){
				var $this = $(this);
				if($this.attr('data-png32filter') == '1') return;
				var b = $this.css('background-image'),
					url = m.exec(b);
				if(!url || url.length < 3) return;
				url = url[2];
				if(url){
					$this.css({
						'filter':'progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale, src="'+url+'");',
						'background-image':'none'
					});
				}
				$this.attr('data-png32filter', '1');
			})
		}
	}
	ie6Filter();
	
	//首页题图渐入渐出效果
	$('.mod-home-slide').imgSlider();
	

})