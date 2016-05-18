var mail=0;
var myurl=window.location.href;
var hash=window.location.hash;
$(function(){
	if(myurl.indexOf("Case")>0){
		$(".h .hMu a.pro").addClass("hover");
	}else if(myurl.indexOf("News")>0){
		$(".h .hMu a.new").addClass("hover");
	}else if(myurl.indexOf("Service")>0){
		$(".h .hMu a.ser").addClass("hover");
	}else if(myurl.indexOf("About")>0 && hash==""){
		$(".h .hMu a.abt").addClass("hover");
	}else if(myurl.indexOf("About")>0 && hash.indexOf("Contact")>0){
		$(".h .hMu a.cnt").addClass("hover");
	};
	$("ul.hMu li a:not(.hover)").hover(function(){$(this).find("em").stop().animate({"top":"-121px"},200);},function(){$(this).find("em").stop().animate({"top":"0"},200);});
	$("ul.hMu li a.hover em").css({"top":"-121px"});
	$(".ber .scl").scrollable({size:1,speed:400,activeClass:"active",easing:"swing",loop:true,items:".ber .scl ul",prev:".pv",next:".nt"});
	$(".sRc .rCm").scrollable({size:1,speed:400,activeClass:"active",easing:"swing",loop:true,items:".sRc .rCm ul",prev:".pv",next:".nt"});
	$(".oBx").scrollable({size:1,speed:400,activeClass:"active",easing:"swing",loop:true,items:".oBx .oSc",prev:".pv",next:".nt"});
	$(".nLt .nLl").scrollable({size:5,speed:400,vertical:true,activeClass:"active",easing:"swing",loop:true,items:".nLt .nLl ul",prev:".pv",next:".nt"}).navigator({navi:".iav",naviItem:"li",activeClass:"hover"});
	$(".cs11 .hCl").scrollable({size:8,speed:400,vertical:true,activeClass:"active",easing:"swing",loop:true,items:".cs11 .hCl ul",prevPage:".pv",nextPage:".nt"});
//	$(".cs11 a.pv").click(function(){var api = $(".cs11 .hCl").data("scrollable");api.prev();});
//	$(".cs11 a.nt").click(function(){var api = $(".cs11 .hCl").data("scrollable");api.next();});
//	$(".cs11 a.pv").click(function(){  $(".cs11 .hCl ul").animate({"top":"-115px"},200);  });
//	$(".cs11 a.nt").click(function(){  $(".cs11 .hCl ul").animate({"top":"-115px"},200);  });
	$(".bw .bws").scrollable({size:3,speed:400,activeClass:"active",easing:"swing",loop:true,items:".bw .bws ul",prev:".pv",next:".nt"});
	$(".iB .nSd").scrollable({size:1,speed:400,activeClass:"active",easing:"swing",loop:true,items:".bw .bws ul",prev:".pv",next:".nt"});
	$(".pD .hdL .m").scrollable({size:1,speed:400,activeClass:"active",easing:"swing",loop:true,items:".bw .bws ul",prev:".pv",next:".nt"});
	$(".nDt ul.pn li a.pv").click(function(){var api = $(".pD .hdL .m").data("scrollable");api.prev();});
	$(".nDt ul.pn li a.nt").click(function(){var api = $(".pD .hdL .m").data("scrollable");api.next();});
	
	if($(".PDi .cTl ul li").length<=0)
	{
		$(".PDi .cTl ul").html("<li><a href=\"/Case/Planning/\" title=\"品牌策划网站\" class=\"ty1\">品牌策划网站</a></li><li><a href=\"/Case/Visual/\" title=\"视觉设计网站\" class=\"ty2\">视觉设计网站</a></li><li><a href=\"/Case/Interaction/\" title=\"创意互动网站 \"\" class=\"ty3\">创意互动网站</a></li><li><a href=\"/Case/Other/\" title=\"其他类别网站\" class=\"ty4\">其他类别网站</a></li>");
	};
	
	if( $(".PDi .cTl ul li.hover").length>0 ){
		$(".PDi .cTl ul li.hover a").addClass("ty"+(parseInt($(".PDi .cTl ul li.hover").prevAll().length)+1));
	};
	
	$(".ber .scl ul li").each(function(i){
	   $(this).width($(window).width()-0);
	   $(this).find("a").width($(window).width()-0);
	});
	
	$(window).resize(function(){
		$(".ber .scl ul li").each(function(i){
		   $(this).width($(window).width()-0);
		   $(this).find("a").width($(window).width()-0);
		});
	});
	
	$("span input,span textarea").focus(function(){
		$(this).parent("span").addClass("hover");
	});
	
	$("span input,span textarea").blur(function(){
		$(this).parent("span").removeClass("hover");
	});
	
	$(".bKt span input,.bKt span textarea").blur(function(){
		$(this).parent("span").removeClass("hover");
		if(	Trim($(".con ul.fbx li.li1 input:eq(0)").val())!="" && Trim($(".con ul.fbx li.li1 input:eq(1)").val())!="" && Trim($(".con ul.fbx li.li1 input:eq(2)").val())!=""){
			$(".con ul.fbx li.li1 ins").addClass("hover");
		}
		else
		{
			$(".con ul.fbx li.li1 ins").removeClass("hover");
		};
		if(Trim($(".con ul.fbx li.li4 textarea").val())!=""){
			$(".con ul.fbx li.li4 ins").addClass("hover");
		}
		else
		{
			$(".con ul.fbx li.li4 ins").removeClass("hover");
		};
		
	});	
	
	$(".con ul.fbx li.li2 a").click(function(){
		$(".con ul.fbx li.li2 a.hover").removeClass("hover");
		$(this).addClass("hover");
		$(".con ul.fbx li.li2 input:hidden.fws").val($(this).text());
		return false;
	});

	$(".sLt ul.tLt li a").click(function(){
		$(this).parents("ul").find("a").removeClass("hover");
		$(this).addClass("hover");
		var lis=-($(".sRc .rCm ul li").width()*$(this).parent().prevAll().length);
		$(".sRc .rCm ul").animate({"left":lis+"px"},200);
	});
	//$("ul.cEr li:odd").addClass("odd");
	IE6();
	$("a[href='#']").click(function(){return false;});
	
	$.fn.ScrollTo = function(speed, callback) {
	var top = $(this).offset().top-121;
	if ('BODY' == $(this).attr('tagName')) {// for IE6
	top = 0;
	}
	//	$($.browser.safari ? 'body' : 'html')
	$('html, body').animate({scrollTop: top}, speed, 'swing', callback);
	};
	
	$(".sT").click(function(){
		var scrolltargetval = $(this).attr('href')			
		if (scrolltargetval.length == 1){
			var scrolltarget = 'body'
		}
		else {
			var scrolltarget = scrolltargetval
		}
		$(scrolltarget).ScrollTo(800);
		return false;})
	if(hash!=""){
		var ht=$(hash).offset().top-121;
		//alert($(window.location.hash).offset().top-121)
		$('html, body').animate({scrollTop: ht},200);
		return false;
	};
	
});

function IE6(){
	if($.browser.msie && $.browser.version=="6.0"){
		$(".sRc .rCm ul li").hover(function(){$(this).addClass("hover");},function(){$(this).removeClass("hover");});
	};
}

function Trim(str){return str.replace(/(^\s*)|(\s*$)/g, "");}