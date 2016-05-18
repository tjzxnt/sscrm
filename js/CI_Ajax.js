function ciAjax(Obj){
	//alert(Obj.Success);
	$.ajax({
		type: "GET",
		url: Obj.Url,
		data: "",
		dataType:Obj.DataType,
		timeout:Obj.Timeout,
		beforeSend:function(){Obj.BeforeSend()},
		success:function(html){ajax=html;Obj.Success(html);},
		error:function(){Obj.Error()}
	});
	
};

function nulls(){};

function nulls_box(a){return a;};

function CIErroe(){
	if(window.confirm('页面加载错误！是否刷新页面？'))
	{
		window.location.reload()
	}
	
};//;

function book_box(a){
	//alert(a);
	$(".gbk ul.gb").html(a);
	$(".bg i").remove();
};

function comment(){
	$(".gFb ul.form li.sub input").attr("disabled","disabled");
	$(".gFb ul.form li.sub").append("<i>正在提交中...</i>");
};

function comment_OK(a){
	if(a=="" || a=="0"){
		$(".gFb ul.form li.sub input").attr("disabled","");
		$(".gFb ul.form li.sub i").html("由于网络原因，留言失败！点留言重新提交！");
	}else if(a=="1")
	{
		$(".gFb ul.form li.sub input").attr("disabled","");
		$(".gFb ul.form li.sub i").html("留言成功！审核中");
		$(".gFb ul.form li input,.gFb ul.form li textarea").val("");
		setTimeout(function(){$(".gFb ul.form li.sub i").remove();},2000);
	}
};

function book_Send(){
	$(".bg").append("<i>正在加载中...</i>");
};

function book_Over(){alert("Over")};

//new ciAjax({Url:"/index.html",DataType:"html",Timeout:10000,BeforeSend:book_Send,Success:book_box,Error:CIErroe});

function pageClick(obj){
	$("div.pg li a:not(.no)").live("click",function(){
		$(this).parents("div.pg a").removeClass("hover");
		$(this).addClass("hover");
		
			var pgs = $(this).parents("div.pg").attr("pgs");
			
			obj.Fn=$(this).parents("div.pg").attr("fn");
			
			obj.onePage=$(this).parents("div.pg").attr("onePage");
		
			var nowpage=$(this).text();
			
			var prevpage=parseInt($(this).text())-2>0?parseInt($(this).text())-2:1;
			
			var nextpage=parseInt($(this).text())+2;
			
			if (parseInt($(this).text())-2<=0)
			{
				nextpage=nextpage+(-(parseInt($(this).text())-3))
			};
			
			if (parseInt($(this).text())+2>pgs && parseInt($(this).text())!=pgs)
			{
				prevpage=prevpage-1;
				//alert(pgs);
			}
			else if (parseInt($(this).text())+1>pgs)
			{
				prevpage=prevpage-2;
				//alert("b");
			};
			
			page="<ul><li><a class='no'>上一页</a></li>";
			if (prevpage<=0){prevpage=1};
			for(i=prevpage;i<=nextpage;i++)
			{
				if (i<=pgs)
				{
					if (i==nowpage)
					{
						page=page+"<li><a href='#' class='hover'>"+i+"</a></li>";
					}
					else
					{
						page=page+"<li><a href='#'>"+i+"</a></li>";
					}
				}
			}
			page=page+"<li><a class='no'>下一页</a></li></ul>";
			
			if(obj.Fn=="ly"){
				bookList(parseInt($(this).text()),obj.aid,obj.onePage);
			}else if(obj.Fn=="Action")
			{
				Action(parseInt($(this).text()),obj.onePage);
			}
			else if(obj.Fn=="View")
			{
				View(parseInt($(this).text()),obj.onePage);
			}
			else if(obj.Fn=="Industry")
			{
				Industry(parseInt($(this).text()),obj.onePage);
			};
			
			$(this).parents("div.pg").html(page);
		
			//alert(obj.onePage);
			
		return false;
	});
	
	$("div.pg li a.no").live("click",function(){
		
	//	alert($(this).parents("div.pg").find("a.hover").parent().nextAll("li").length);
		
		if($(this).text()=="上一页" && $(this).parents("div.pg").find("a.hover").parent().prevAll("li").length>1)
		{
			$(this).parents("div.pg").find("a.hover").parent().prev("li").find("a").click();
		}
		if($(this).text()=="下一页" && $(this).parents("div.pg").find("a.hover").parent().nextAll("li").length>1)
		{
			$(this).parents("div.pg").find("a.hover").parent().next("li").find("a").click();
		}
		//alert("a");
		return false;
	});
};

function ShowPage(obj){
	//alert(obj.All);
	if (obj.All>0)
	{
		var pgs=Math.ceil(obj.All/obj.page);
		
		page="<ul><li><a class='no'>上一页</a></li>";
		for(i=1;i<=obj.PageSize;i++)
		{
			if (i<=pgs)
			{
				if (i==1)
				{
					page=page+"<li><a href='#' class='hover'>"+i+"</a></li>";
				}
				else
				{
					page=page+"<li><a href='#'>"+i+"</a></li>";
				}
			}
		}
		page=page+"<li><a class='no'>下一页</a></li></ul>";
		
		$(obj.Box).attr("pgs",pgs);$(obj.Box).attr("All",obj.All);$(obj.Box).attr("page",obj.page);$(obj.Box).attr("fn",obj.Fn);$(obj.Box).attr("onePage",obj.page);
		$(obj.Box).html(page);
		pageClick({"Fn":obj.Fn,"aid":obj.aid,"onePage":obj.page});
		//alert(obj.Fn);
	}
};

$(function(){

$(".gFb ul.form li.sub input").click(function(){
	
		var Name = Trim($(this).parents(".form").find("input[name='Name']").val());
		var Title2 = $(this).parents(".form").find("input[name='Title2']").val();
		var Title = Trim($(this).parents(".form").find("input[name='Title']").val());
		var Content = Trim($(this).parents(".form").find("textarea[name='Content']").val());
		var typeid = Trim($(this).parents(".form").find("input[name='typeid']").val());
		var aid = $(this).parents(".form").find("input[name='aid']").val();
		if(Name==""){
			//$(this).find(".info").html("姓名必须填写！");
			$(this).parents(".form").find("input[name='Name']").focus();
			return false;
		};
		 if(Title==""){
			//$(this).find(".info").html("标题必须填写！");
			$(this).parents(".form").find("input[name='Title']").focus();
			return false;
		};
		 if(Content==""){
			//$(this).find(".info").html("内容必须填写！");
			$(this).parents(".form").find("textarea[name='Content']").focus();
			return false;
		};
		ciAjax({Url:"/CI.php?aid="+aid+"&type=addComment&Name="+escape(Name)+"&Title="+escape(Title2+"|"+Title)+"&Content="+escape(Content)+"&typeid="+typeid+"&t="+Math.random(),DataType:"html",Timeout:10000,BeforeSend:comment,Success:comment_OK,Error:CIErroe});
		
		return false;
});

$(".con form input.sub").click(function(){
		
		var Name = Trim($(this).parents("form").find("input[name='Name']").val());
		var Phone = Trim($(this).parents("form").find("input[name='Phone']").val());
		var Content = Trim($(this).parents("form").find("textarea[name='Content']").val());
		var Mail = Trim($(this).parents("form").find("input[name='Mail']").val());
		var Code = Trim($(this).parents("form").find("input[name='Code']").val());
		var MySite = Trim($(this).parents("form").find("input[name='MySite']").val());
		var MyLike = Trim($(this).parents("form").find("input[name='MyLike']:eq(0)").val())+","+Trim($(this).parents("form").find("input[name='MyLike']:eq(1)").val())+","+Trim($(this).parents("form").find("input[name='MyLike']:eq(2)").val());
		var Service=Trim($(this).parents("form").find("input[name='Service']").val());
		
		if(Name==""){
			$(this).parents("form").find(".info").html("姓名必须填写！");
			$(this).parents("form").find("input[name='Name']").focus();
			return false;
		}
		else if(Phone==""){
			$(this).parents("form").find(".info").html("电话必须填写！");
			$(this).parents("form").find("input[name='Phone']").focus();
			return false;
		}
		else if(Mail==""){
			$(this).parents("form").find(".info").html("电子邮件必须填写！");
			$(this).parents("form").find("input[name='Mail']").focus();
			return false;
		}
		else if(Content==""){
			$(this).parents("form").find(".info").html("需求必须填写！");
			$(this).parents("form").find("textarea[name='Content']").focus();
			return false;
		}else if(Code==""){
			$(this).parents("form").find(".info").html("验证码必须填写！");
			$(this).parents("form").find("input[name='Code']").focus();
			return false;
		};
		$(this).attr("disabled","disabled");
		ciAjax({Url:"/CI_mail.php?Name="+escape(Name)+"&Phone="+escape(Phone)+"&Content="+escape(Content)+"&Mail="+escape(Mail)+"&Service="+escape(Service)+"&MySite="+escape(MySite)+"&MyLike="+escape(MyLike)+"&Code="+escape(Code)+"&t="+Math.random(),DataType:"html",Timeout:10000,BeforeSend:Mail_Send,Success:mail,Error:CIErroe});
		//alert("a");
		return false;
	});
	
	$(".con form input[name='Code']").focus(function(){
		if($(this).parents("li").find("img").length<=0)
		{
			$(this).parent("span").parent("li").append('<img id="vdimgck" align="absmiddle" style="cursor: pointer;" alt="看不清？点击更换" src="/include/vdimgck.php"/>')
		};
		//alert($(this).parents("li").find("img").length);
	});
	
	$("#vdimgck").live("click",function(){
		$(this).attr("src","/include/vdimgck.php?id="+Math.random());
	});

});

function mail(a)
{
	if(a==1)
	{
		$(".con form input.sub").attr("disabled","");
		alert("提交成功！我们会马上跟您取的联系！您也可以电话联系我们！");
		$(".con form input:reset").click();
		$(".con form .info").html("");
		$("#vdimgck").remove();
	}
	else if(a==2){
		$(".con form .info").html("");
		$(".con form input.sub").attr("disabled","");
		alert("验证码错误！");
		$(".con form input[name='Code']").val("");
		$("img#vdimgck").attr("src","/include/vdimgck.php?id="+Math.random());
		$(".con form input[name='Code']").focus();
	};
};

function Mail_Send(){
	$(".con form .info").html("正在发送中！请稍后！");
};

function bookList(page,aid,onePage)
{
	ciAjax({Url:"/CI.php?aid="+aid+"&page="+page+"&type=comment&onePage="+onePage+"&t="+Math.random(),DataType:"html",Timeout:10000,BeforeSend:book_Send,Success:book_box,Error:CIErroe});
};

function Action(page,onePage){
	ciAjax({Url:"/CI.php?page="+page+"&type=NewsAction&onePage="+onePage+"&t="+Math.random(),DataType:"html",Timeout:10000,BeforeSend:book_Send,Success:NewsAction,Error:CIErroe});
};

function NewsAction(a){
	
	$(".nDl .Am .nLl .nlu").html(a);
};

function View(page,onePage){
	//alert(page);
	ciAjax({Url:"/CI.php?page="+page+"&type=NewsView&onePage="+onePage+"&t="+Math.random(),DataType:"html",Timeout:10000,BeforeSend:book_Send,Success:NewsView,Error:CIErroe});
}

function NewsView(a){
	$(".viw .cVw .vlt").html(a);
};

function Industry(page,onePage){
	ciAjax({Url:"/CI.php?page="+page+"&type=NewsIndustry&onePage="+onePage+"&t="+Math.random(),DataType:"html",Timeout:10000,BeforeSend:book_Send,Success:NewsIndustry,Error:CIErroe});
};

function NewsIndustry(a){
	$(".iNs .tet .txt").remove();
	$(".iNs .tet").prepend(a);
};