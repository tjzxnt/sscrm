// JavaScript Document
var _debug_ = true;

function Trim(str){  
   return str.replace(/(^\s*)|(\s*$)/g, "");  
}
   
function LTrim(str){  
   return str.replace(/(^\s*)/g, "");  
}

function RTrim(str){  
   return str.replace(/(\s*$)/g, "");  
} 

function LTrimToEnter(str){  
   return str.replace(/(^\n*)/g, "");  
}

function RTrimToEnter(str){  
   return str.replace(/(\n*$)/g, "");  
} 

function showErrorDialog(msg) {
	if(_debug_ && msg!=''){
		//alert('error:' + xml.responseText);
		$("<div title='错误信息'><div style='line-height:20px'>" + msg + "</div></div>").dialog({ width: '40%', height: 'auto', buttons: { '确定': function() { $(this).dialog('close');} }});
	}
}

function resizeImage(imgObj) {
	var argv=resizeImage.arguments;
	var argv_len=resizeImage.arguments.length;
	var maxWidth=(argv_len>1)?argv[1]:130;
	var maxHeight=(argv_len>2)?argv[2]:130;
	
	if(imgObj.width>maxWidth || imgObj.height>maxHeight){
		if(imgObj.width/maxWidth > imgObj.height/maxHeight) {
			imgObj.height=imgObj.height/(imgObj.width/maxWidth);
			imgObj.width = maxWidth; 
		}
		else{
			imgObj.width=imgObj.width/(imgObj.height/maxHeight);
			imgObj.height=maxHeight;
		}
	}
}

/****************
Block消息及通用对话框
****************/
function showBlockMsg(obj, message) {
	if(showBlockMsg.arguments.length >= 3) {
		switch(showBlockMsg.arguments[2]) {
			case "error":
				icon = "error.gif";
				break;
			case "info":
				icon = "info.gif";
				break;
			case "ok":
				icon = "ok.gif";
				break;
			default:
				icon = "";
				break;
		}
	}
	else {
		icon = "loadingAnimation.gif";
	}
	
	if(icon) {		
		message = "<h3><img src='/images/" + icon+ "' align='absmiddle' />&nbsp;&nbsp;" + message + "</h3>";
	}
	else {
		message = "<h3>" + message + "</h3>";
	}
	
	
	var defaultCss = { 
		border: '3px solid #ccc', 
		padding:'20px', 
		width: '50%',
		'font-size': '14px',
		'-webkit-border-radius': '10px',
		'-moz-border-radius': '10px'
	};
	
	if(showBlockMsg.arguments.length >= 4) {
		css = $.extend(defaultCss, showBlockMsg.arguments[3]);
	}
	else {
		css = defaultCss;
	}

	if(obj){
		$(obj).block({ 
			message: message,  
			css: css
		});
	}else{
		$.blockUI({ 
			message: message,  
			css: css
		});	
	}

}

function hideBlockMsg(obj) {
	if(obj){
		$(obj).unblock();
	}else{
		$.unblockUI();
		$(".blockUI").fadeOut("slow");
	}

}

function showConfirmDialog(options) {
	var defaults = {
		title: "请确认",
		width: '300px',
		height: '160',
		buttons: {
			"取消": function() {
				$(this).dialog("destroy");
				$(".confirmDialog").remove();
			},			
			"确定": function() {
				options.callback();
				$(this).dialog("destroy");
				$(".confirmDialog").remove();
			}
		}
	};

	var opts = $.extend(defaults, options);
	var confirmDialog = $("<div class='confirmDialog' title='" + opts.title + "'><p>" + opts.message + "</p></div>");

	confirmDialog.dialog({
		modal: true,
		buttons: opts.buttons,
		resizable: false,
		width: opts.width,
		height: opts.height,
		overlay: {
			backgroundColor:'#fff', 
	        opacity:        '0.6' 
		}
	});
	return confirmDialog;
}

function in_array(needle, haystack) {
	if(typeof needle == 'string' || typeof needle == 'number') {
		for(var i in haystack) {
			if(haystack[i] == needle) {
				return true
			}
		}
	}
	return false;
}

function remove_Element(needle, haystack){
	if(typeof needle == 'string' || typeof needle == 'number') {
		var arr = [];
		for(var i in haystack) {
			if(haystack[i] != needle) {
				arr.push(haystack[i]);
			}
		}
		return arr;
	}
}

function setMyHome() {
   if (document.all) {  
	 document.body.style.behavior='url(#default#homepage)';  
	 document.body.setHomePage('http://www.tjregong.com');  
	
   } else if (window.sidebar) {  
	 if(window.netscape) {  
		 try {    
			 netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");    
		 } catch (e) {    
			 alert( "该操作被浏览器拒绝，如果想启用该功能，请在地址栏内输入 about:config,然后将项 signed.applets.codebase_principal_support 值该为true" );    
		 } 
		 try {  
		 	var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components. interfaces.nsIPrefBranch);  
		 	prefs.setCharPref('browser.startup.homepage','http://www.tjregong.com');  
		 }catch (e) {  
		 }
	 }  
   }  
 }