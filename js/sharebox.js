function myPopup(opts_obj,opts_mask,opts_close) {
	this._obj=$(opts_obj);
	this._mask=$(opts_mask);
	this._objclose=$(opts_close);
	var _this=this
	$(window).resize(function () {
		_this.mask();
		_this.setPos();
	});

}
myPopup.prototype.pup= function() {
	var _this=this;
	_this.mask();
	_this.setPos();
	_this._objclose.bind("click",function(){
		_this.close()
		$(this).unbind("click");
	})

}
myPopup.prototype.setPos=function(){
	var _this=this
	var _width=_this._obj.width(), //获取弹出框宽度
		_height=_this._obj.height(),
		_top;
	if($.browser.msie && $.browser.version<7) {
		var _srcoll_top=$(window).scrollTop();
		$(window).bind("scroll", function() {
			_top=$(window).scrollTop()+($(window).height()-_height)/2;
			_this._obj.css("top",_top);
		});
		_this._obj.css("position","absolute");
		_top=_srcoll_top+($(window).height()-_height)/2;

	} else {
		_this._obj.css("position","fixed");
		_top=($(window).height()-_height)/2;
	}
	//垂直居中
	var _left=(($(window).width())-_width)/2;
	//设置水平
	_this._obj.css({
		"top":  _top,
		"left": _left
	})
		.fadeIn(200);
}
myPopup.prototype.close= function() {
	var _this=this
	_this._obj.fadeOut(0);
	_this._mask.css("display","none");
	$(window).unbind("scroll");
	$(window).unbind("resize");
}
myPopup.prototype.mask= function() {
	var _this=this;
	var mask_height=Math.max($("body").height(),$(window).height());
	var mask_width=$("body").width();
	_this._mask.css({
		"height":mask_height,
		"width":mask_width,
		"display":"block"
	})
}
myPopup.prototype.srcoll= function(_srcoll_top) {
	var _this=this
	var srcoll_top=$(window).scrollTop();
	if($.browser.msie && $.browser.version<7) {
		_this._obj.css("top",srcoll_top+_top-_srcoll_top);
	} else {
		_this._obj.css("top",_top)
	}
}