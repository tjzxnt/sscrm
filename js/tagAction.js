// JavaScript Document
var WrapperArr = ['#LeftWrapper', '#RightWrapper', '#MonostichousBox', '#BiserialLeftBox', '#BiserialRightBox'];
var moduletype = null;
var tagId;
var tagObj;

function ShowHidehint(){
	$.each(WrapperArr, function(i, item){
		if($(item).css('display') == 'block'){
			if(!$(item).find('.side').get(0)) {
				if(!$(item).find('.main_cell').get(0)){
					$(item).find('.edit_add_hint').show();
				}else{
					$(item).find('.edit_add_hint').hide();
				}
			}else if(!$(item).find('.main_cell').get(0)){
				if(!$(item).find('.side').get(0)){
					$(item).find('.edit_add_hint').show();
				}else{
					$(item).find('.edit_add_hint').hide();
				}	
			}
		}
	})
}

function sortableMothed(sortable_o, connectWith){
	$(sortable_o).sortable({
		connectWith: connectWith,
		cursor: 'move',
		items : ".side, .main_cell",
		update: function(event, ui){
			$('.shade_mask').remove();
			ShowHidehint();
			var list = "";
			$.each($(".column"), function() {					
				list += $(this).attr('id') + ":";
				$.each($(this).children(".portlet"), function() {
					if($(this).attr('lang')){
						list += $(this).attr('lang') + "@";
					}
				})
				list += "|";
			})
			list = list.substr(0, list.length - 1);
			//console.log(list)
			$.ajax({
				url:'index.php?c=index&a=tagOrderSave&list=' + list + '&template_page_id=' + template_page_id,
				dataType: 'json',
				success: 
				function(json, status){
					
				},
				error:
				function(xml,status){
					alert(xml.responseText);
				}
			});			
		}
	});
}

function deleteModule(){
	var obj = $(this).parent().parent().parent().parent();
	var pobj = obj.parent();
	$('.shade_mask').remove();
	showConfirmDialog({
		title: "删除确认",
		message: "你确认要删除此模块吗？",
		callback: function(dialog) {
			$.ajax({
				url:'index.php?c=index&a=tagDelete&tagId=' + tagId,
				dataType: 'json',
				success: 
				function(json, status){
					if(json.result == 0){
						obj.remove();
						ShowHidehint();
					}
				},
				error:
				function(xml,status){
					alert(xml.responseText);
				}
			});	
		}
	});		
	return false;
}

function editModule() {
	var title = o.find("h2").html();
	if(title == null){
		title = '';	
	}
	$(".ui-dialog-title").html(title + '模块编辑');
	$('.shade_mask').remove();
	$("#operateDialog").html('').dialog("open");
	if(tagId && moduletype){
		ajaxTagShow();
	}
}

function ajaxTagShow(){
	$.ajax({
		url:'index.php?c=index&a=tagDetail&tagId=' + tagId + "&moduletype=" + moduletype,
		dataType: 'json',
		type: 'GET',
		beforeSend:
		function(){
			showBlockMsg($("#operateDialog").parent(), "读取中……", '');
		},
		success: 
		function(json, status){
			hideBlockMsg($("#operateDialog").parent());	
			$('#operateDialog').html(json.formHtml);
			switch(moduletype){
				case 'titleText':
				case 'htmlCode':
				case 'articleContent':
				case 'productContent':	
				case 'customerService':
				case 'comments':
					eval("tag" + moduletype + "('show', json.rs)");
					break;
				case 'productList':	
					eval("tag" + moduletype + "('bind', json.rs)");
				case 'articleList':
					eval("tag" + moduletype + "('show', json)");
					break;
				case 'text':
				case 'linksImg':
					eval("tag" + moduletype + "('bind', json.rs)");
					eval("tag" + moduletype + "('show', json.rs)");
					break;	
				case 'wordList':
				case 'singleImage':
				case 'multiImage':
				case 'leftofRight':
				case 'rightofLeft':
				case 'figureBelow':
				case 'figureAbove':
				case 'linksText':
				case 'Flash':
				case 'htmlEditor':
					eval("tag" + moduletype + "('bind')");
					eval("tag" + moduletype + "('show', json.rs)");
					break;				
			}
		},
		error:
		function(xml,status){
			hideBlockMsg($("#operateDialog").parent());	
			alert(xml.responseText);
		}
	});
}

function commonValidate(data, type, msg, reg){
	switch(type){
		case 1:	
			if(data == ''){
				errorMsg(msg);
				return false;
			}else{
				return true;
			}
			break;
		case 2:	
			if(!reg.test(data)){
				errorMsg(msg);
				return false;
			}else{
				return true;
			}
			break;			
	}
}

function errorMsg(msg){
	showBlockMsg($("#operateDialog").parent(), msg, 'error');
	window.setTimeout(function() {
		hideBlockMsg($("#operateDialog").parent());	
	}, 1000);
}

function tagEdit(data, validate){
	var flag = true;
	if(data && validate){
		for(var i in data){
			for(var j in validate){
				if(i == j){
					if(validate[j][2]){
						if(!commonValidate(data[i], validate[j][0], validate[j][1], validate[j][2])){
							flag = false;
							break;
						}
					}else{
						if(!commonValidate(data[i], validate[j][0], validate[j][1])){
							flag = false;
							break;
						}
					}
				}	
			}
			if(!flag){
				break
			}
		}
	}
	//console.log(data)
	if(flag){
		ajaxTagSave(data);
	}		
}

function ajaxTagSave(data){
	$("#tagForm").ajaxSubmit({
		dataType:  'json',
		beforeSubmit: 
			function(formData, jqForm, options){
				showBlockMsg($("#operateDialog").parent(), '');
				return true;
			},
		success: 
		function(json, status) {
			hideBlockMsg($("#operateDialog").parent());	
			if(json.result != 0){   
				showBlockMsg($("#operateDialog").parent(), json.msg, 'error');
				window.setTimeout(function() {
					hideBlockMsg($("#operateDialog").parent());	
				}, 2000);
			}
			else {
				showBlockMsg($("#operateDialog").parent(), json.msg, 'ok');
				window.setTimeout(function() {
					hideBlockMsg($("#operateDialog").parent());	
					$("#operateDialog").dialog("close");
					switch(moduletype){
						case 'titleText':
						case 'linksImg':
						case 'htmlEditor':
						case 'htmlCode':
						case 'articleList':
						case 'productList':	
						case 'articleContent':
						case 'productContent':
						case 'customerService':
						case 'comments':
							eval("tag" + moduletype + "('change', data)");
							break;	
						case 'text':
						case 'wordList':
						case 'linksText':
							eval("tag" + moduletype + "('change', data)");
							eval("tag" + moduletype + "('unbind')");
							break;
						case 'singleImage':
						case 'multiImage':
							if(json.url){
								data = { 'image_thumb': json.url};	
							}
							eval("tag" + moduletype + "('change', data)");
							break;
						case 'leftofRight':
						case 'rightofLeft':						
						case 'figureBelow':
						case 'figureAbove':
							if(json.url){
								data['image_thumb'] = json.url;	
							}
							eval("tag" + moduletype + "('change', data)");
							break;		
						case 'Flash':
							if(json.url){
								data = { 'flashSwf': json.url};	
							}
							eval("tag" + moduletype + "('change', data)");
							break;					
					}							
				}, 2000);
			}
			switch(moduletype){
				case 'linksText':
				case 'wordList':
				case 'leftofRight':
				case 'rightofLeft':				
				case 'figureBelow':	
				case 'figureAbove':				
				case 'text':
					eval("tag" + moduletype + "('unbind')");
					break;
			}
		},
		error:
		function(xml,status){
			hideBlockMsg($("#operateDialog").parent());	
			switch(moduletype){
				case 'linksText':
				case 'wordList':
				case 'leftofRight':
				case 'rightofLeft':				
				case 'figureBelow':	
				case 'figureAbove':				
				case 'text':
					eval("tag" + moduletype + "('unbind')");
					break;
			}						
			alert(xml.responseText);
		}
	});
}

function tagtitleText(type, data){
	switch(type){
		case 'show':
			if(data){
				$.each(data, function(i, item){	
					if(i == 'mTitle' && item == ''){
						$("#" + i).val('标题文本');	
					}else{
						$("#" + i).val(item);	
					}						
				});
			}
			$("#tagId").val(tagId);
			break;
		case 'validate':
			return { 'mTitle': { 0:1, 1:'请填写模块标题'}};
			break;
		case 'save':
			return { 'mTitle' : $("#mTitle").val(), 'mStyle' : $("#mStyle").val()};
			break;
		case 'change':
			if(data){
				$.each(data, function(i, item){
					switch(i){
						case 'mTitle':
							o.find("h2").html(item);
							break;										
						case 'mStyle':
							o.attr('class', item + ' portlet');
							break;								
					}
					
				})
			}
	}
}

function tagtext(type, data){
	switch(type){
		case 'bind':
			iColorPicker();
			var lh = 14;
			if(data){
				$.each(data, function(i, item){	
					if(i == 'LineHeight'){
						lh = item;	
					}
				});
			}
			$("#slider-range-lineheight").slider({
				range: "min",
				value: lh,
				min: 12,
				max: 30,
				animate: true, 
				slide: function(event, ui) {
					$("#LineHeight").val(ui.value);
				}
			});
			$("#LineHeight").val($("#slider-range-lineheight").slider("value"));			
			break;
		case 'unbind':
			$("#iColorPicker").hide();
			break;				
		case 'show':
			if(data){
				$.each(data, function(i, item){	
					if(i == 'Color'){
						$("#" + i).val(item);
						$("#Color").css('background', item);	
					}else{
						$("#" + i).val(item);	
					}					
				});
			}
			$("#tagId").val(tagId);
			break;
		case 'validate':
			return { 'content': { 0:1, 1:'请输入内容'}};
			break;
		case 'save':
			return { 'content' : $("#content").val(),'textAlign' : $("#textAlign").val(), 'Font' : $("#Font").val(), 'FontSize' : $("#FontSize").val(), 'FontStyle' : $("#FontStyle").val(), 'Color' : $("#Color").val(), 'LineHeight' : $("#LineHeight").val()};
			break;
		case 'change':
			if(data){
				$.each(data, function(i, item){
					switch(i){
						case 'content':
							o.find("p").html(item);
							break;						
						case 'textAlign':
							o.find("p").css(i, item);
							break;
						case 'Font':
							o.find("p").css('fontFamily', item);
							break;
						case 'FontStyle':
							if(item == 'italic'){
								o.find("p").css('fontStyle', item);
							}else if(item == 'bold'){
								o.find("p").css('fontWeight', item);
							}else if(item == ''){
								o.find("p").css('fontWeight', '');
								o.find("p").css('fontStyle', '');
							}
							break;
						case 'FontSize':
							if(item){
								o.find("p").css('fontSize', item + 'px');
							}else{
								o.find("p").css('fontSize', item + '');
							}
							break;										
						case 'Color':
							o.find("p").css('color', item);
							break;	
						case 'LineHeight':
							o.find("p").css('lineHeight', item + 'px');
							break;								
					}
					
				})
			}
	}
}

function tagwordList(type, data){
	switch(type){
		case 'bind':
			iColorPicker();			
			break;
		case 'unbind':
			$("#iColorPicker").hide();
			break;				
		case 'show':
			if(data){
				$.each(data, function(i, item){	
					if(i == 'wordlistprotype'){
						$("input[name='wordlistprotype']").val([item]);
					}else if(i == 'Color'){
						$("#" + i).val(item);
						$("#Color").css('background', item);	
					}else{
						$("#" + i).val(item);	
					}					
				});
			}
			$("#tagId").val(tagId);
			break;
		case 'validate':
			return { 'wordlistcontent': { 0:1, 1:'请输入列表内容'}};
			break;
		case 'save':
			return { 'wordlistprotype' : $("input[name='wordlistprotype']:checked").val(), 'textAlign' : $("#textAlign").val(), 'Font' : $("#Font").val(), 'FontSize' : $("#FontSize").val(), 'FontStyle' : $("#FontStyle").val(), 'Color' : $("#Color").val(), 'wordlistcontent' : $("#wordlistcontent").val()};
			break;
		case 'change':
			if(data){
				var wlprotypestr = '', wltextAlignstr = '', wlFontstr = '', wlFontStylestr = '', wlFontSizestr = '', wlColorstr = '', wlcontentstr = '';		
				$.each(data, function(i, item){
					switch(i){
						case 'wordlistprotype':
							wlprotypestr = '<ul class="list_text_style_1 ' + item +'">';
							break;							
						case 'textAlign':
							wltextAlignstr = 'text-align:' + item + ';';
							break;
						case 'Font':
							wlFontstr = 'font-family:' + item + ';';
							break;
						case 'FontStyle':
							if(item == 'italic'){
								wlFontStylestr = 'font-style:' + item + ';';
							}else if(item == 'bold'){
								wlFontStylestr = 'font-weight:' + item + ';';	
							}
							break;
						case 'FontSize':
							if(item){
								wlFontSizestr = 'font-size:' + item + 'px;';
							}else{
								wlFontSizestr = 'font-size:' + item + ';';
							}
							break;										
						case 'Color':
							wlColorstr = 'color:' + item + ';';
							break;
						case 'wordlistcontent':
							//var s = new String();
							//console.log(item)
							//item = s.ltrim(item);
							console.log()
							var content_arr = LTrimToEnter(RTrimToEnter(item)).split("\n");
							$.each(content_arr, function(k, v){
								wlcontentstr += '<li style="' + wltextAlignstr + wlFontstr + wlFontStylestr + wlFontSizestr +  wlColorstr + '">' + v + '</li>';						 
							});
							break;								
					}
					
				})
				var str = wlprotypestr + wlcontentstr + '</ul>';
				o.find('.context').html(str);
			}
	}
}

function tagsingleImage(type, data){
	switch(type){
		case 'bind':
			$("input[type=file].multi").MultiFile();
			break;			
		case 'show':
			if(data){
				$.each(data, function(i, item){
					if(i == 'image_thumb'){
						$("#singleimageDiv img").attr('src', item);
						
					}
				});
				$("#singleimageDiv").show();
			}
			$("#tagId").val(tagId);			
			break;
		case 'validate':
			return { 'img': { 0:1, 1:'请选择要上传的文件'}};
			break;
		case 'save':
			return { 'img': $("input[type=file].multi").val()};
			break;
		case 'change':
			if(data){
				var str = '';
				$.each(data, function(i, item){
					if(i == 'image_thumb'){
						str += '<img src="' + item + '" />';
					}
				})
				o.find(".context div").html(str);
			}
	}
}

function tagmultiImage(type, data){
	switch(type){
		case 'bind':
			$("input[type=file].multi").MultiFile();
			break;			
		case 'show':
			if(data){
				var str = '';
				$.each(data, function(i, item){
					if(i == 'image'){
						$("#img_str").val(item);						
					}else if(i == 'image_thumb'){
						$("#img_thumb_str").val(item);
						var image_thumb_arr = item.split(" ");
						$.each(image_thumb_arr, function(k, v){
							str += '<div class="PicList"><div class="PicBorderC"><img src="' + v + '" width="69" height="52" class="multiImagedel" lang="' + v.replace(/150_/g, "") + '"></div></div>';
						});
					}
				});
				str += '<br>(点击图片选中，确定后即可删除)'
				$("#multiimageDiv").html(str).show();
				var img_thumb_arr = $('#img_thumb_str').val().split(" ");
				var img_arr = $('#img_str').val().split(" ");				
				$('.multiImagedel').click(function(){
					if($(this).parent().hasClass('PicBorderC')){
						$(this).parent().removeClass('PicBorderC').addClass('PicBorderR');
						img_thumb_arr = remove_Element($(this).attr('src'), img_thumb_arr);
						img_arr = remove_Element($(this).attr('lang'), img_arr);	
					}else{
						$(this).parent().removeClass('PicBorderR').addClass('PicBorderC');					
						img_thumb_arr.push($(this).attr('src'));
						img_arr.push($(this).attr('lang'));
					}
					$('#img_thumb_str').val(img_thumb_arr.join(" "));
					$('#img_str').val(img_arr.join(" "));	
				})
			}
			$("#tagId").val(tagId);			
			break;
		case 'validate':
			if($('.multiImagedel').length == 0){
				return { 'img': { 0:1, 1:'请选择要上传的文件'}};
			}else{
				return false;	
			}
			break;
		case 'save':
			return { 'img': $("input[type=file].multi").val()};
			break;
		case 'change':
			if(data){
				var str = '';
				$.each(data, function(i, item){
					if(i == 'image_thumb'){
						var image_thumb_arr = item.split(" ");
						$.each(image_thumb_arr, function(k, v){
							str += '<div><img src="' + v + '" /></div>';
						});
					}
				})
				o.find(".multipicwrap").html(str);
			}
	}
}

function tagleftofRight(type, data){
	commonImageText(type, data);
}

function tagrightofLeft(type, data){
	commonTextImage(type, data);
}

function tagfigureAbove(type, data){
	commonTextImage(type, data);
}

function tagfigureBelow(type, data){
	commonImageText(type, data);
}

function commonTextImage(type, data){
	switch(type){
		case 'bind':
			iColorPicker();	
			$("input[type=file].multi").MultiFile();
			break;
		case 'unbind':
			$("#iColorPicker").hide();
			break;				
		case 'show':
			if(data){
				$.each(data, function(i, item){
					if(i == 'image'){
						$("#img_str").val(item);
					}else if(i == 'image_thumb'){
						if(item){
							$(".imagepreview").html('<img src="' + item + '" />').show();
							$("#img_thumb_str").val(item);
						}
					}else if(i == 'Color'){
						$("#" + i).val(item);
						$("#Color").css('background', item);	
					}else{
						$("#" + i).val(item);	
					}					
				});
			}
			//console.log(date)
			$("#tagId").val(tagId);
			break;
		case 'validate':
			return { 'content': { 0:1, 1:'请输入文字内容'}};
			break;
		case 'save':
			return { 'Font' : $("#Font").val(), 'FontSize' : $("#FontSize").val(), 'FontStyle' : $("#FontStyle").val(), 'Color' : $("#Color").val(), 'content' : $("#content").val()};
			break;
		case 'change':
			if(data){
				var lorimgstr = '', lorFontstr = '', lorFontStylestr = '', lorFontSizestr = '', lorColorstr = '', lorcontentstr = '';		
				$.each(data, function(i, item){
					switch(i){
						case 'image_thumb':
							lorimgstr = '<img src="' + item +'" />';
							break;
						case 'Font':
							lorFontstr = 'font-family:' + item + ';';
							break;
						case 'FontStyle':
							if(item == 'italic'){
								lorFontStylestr = 'font-style:' + item + ';';
							}else if(item == 'bold'){
								lorFontStylestr = 'font-weight:' + item + ';';	
							}
							break;
						case 'FontSize':
							if(item){
								lorFontSizestr = 'font-size:' + item + 'px;';
							}else{
								lorFontSizestr = 'font-size:' + item + ';';
							}
							break;										
						case 'Color':
							lorColorstr = 'color:' + item + ';';
							break;
						case 'content':
							lorcontentstr += '<div class="text" style="' + lorFontstr + lorFontStylestr + lorFontSizestr +  lorColorstr + '">' + item.replace(/\n/g, '<br>') + '</div>';						
							break;								
					}
					
				})
				var str = lorcontentstr + '<div class="img">' + lorimgstr + '</div>';
				o.find('.context .outbox').html(str);
				
			}
	}
}

function commonImageText(type, data){
	switch(type){
		case 'bind':
			iColorPicker();	
			$("input[type=file].multi").MultiFile();
			break;
		case 'unbind':
			$("#iColorPicker").hide();
			break;				
		case 'show':
			if(data){
				$.each(data, function(i, item){	
					if(i == 'image'){
						$("#img_str").val(item);
					}else if(i == 'image_thumb'){
						if(item){
							$(".imagepreview").html('<img src="' + item + '" />').show();
							$("#img_thumb_str").val(item);
						}
					}else if(i == 'Color'){
						$("#" + i).val(item);
						$("#Color").css('background', item);	
					}else{
						$("#" + i).val(item);	
					}					
				});
			}
			$("#tagId").val(tagId);
			break;
		case 'validate':
			return { 'content': { 0:1, 1:'请输入文字内容'}};
			break;
		case 'save':
			return { 'Font' : $("#Font").val(), 'FontSize' : $("#FontSize").val(), 'FontStyle' : $("#FontStyle").val(), 'Color' : $("#Color").val(), 'content' : $("#content").val()};
			break;
		case 'change':
			if(data){
				var lorimgstr = '', lorFontstr = '', lorFontStylestr = '', lorFontSizestr = '', lorColorstr = '', lorcontentstr = '';		
				$.each(data, function(i, item){
					switch(i){
						case 'image_thumb':
							lorimgstr = '<img src="' + item +'" />';
							break;
						case 'Font':
							lorFontstr = 'font-family:' + item + ';';
							break;
						case 'FontStyle':
							if(item == 'italic'){
								lorFontStylestr = 'font-style:' + item + ';';
							}else if(item == 'bold'){
								lorFontStylestr = 'font-weight:' + item + ';';	
							}
							break;
						case 'FontSize':
							if(item){
								lorFontSizestr = 'font-size:' + item + 'px;';
							}else{
								lorFontSizestr = 'font-size:' + item + ';';
							}
							break;										
						case 'Color':
							lorColorstr = 'color:' + item + ';';
							break;
						case 'content':
							lorcontentstr += '<div class="text" style="' + lorFontstr + lorFontStylestr + lorFontSizestr +  lorColorstr + '">' + item.replace(/\n/g, '<br>') + '</div>';						
							break;								
					}
					
				})
				var str = '<div class="img">' + lorimgstr + '</div>' + lorcontentstr;
				o.find('.context .outbox').html(str);
				
			}
	}	
}

function tagarticleList(type, data){
	switch(type){			
		case 'show':	
			if(data.columns_rs){
				var str = '';
				$.each(data.columns_rs, function(i, item){	
					str += "<option value='" + item.id + "'>" + item.name + "</option>";							
				});
				$('#column_id').html(str);
			}
			
			if(data.categories_rs){
				var str = '';
				$.each(data.categories_rs, function(i, item){	
					str += "<option value='" + item.object.id + "'>";
					if(item.grade == 1){
						 str += item.object.name;
					}else if(item.grade == 2){
						 str += "&nbsp;&nbsp;|--" + item.object.name;
					}
					str += "</option>";							
				});
				$('#category_id').html(str);
			}
			if(data.rs){
				setTimeout(function(){
					$.each(data.rs, function(i, item){
						$("#" + i).val(item);
					});
				}, 1000)
			}
			$("#tagId").val(tagId);
			break;
		case 'validate':
			return { 'titleNum': { 0:2, 1:'请输入正确的标题字数', 2:/^([1-9]{1})([0-9]{0,1})$/}, 'limit': { 0:2, 1:'请输入正确的文章条数', 2:/^([1-9]{1})([0-9]{0,1})$/}};
			break;			
		case 'save':
			return { 'column_id' : $("#column_id").val(), 'category_id' : $("#category_id").val(), 'titleNum' : $("#titleNum").val(), 'limit' : $("#limit").val(), 'time' : $("#time").val(), 'sortc' : $("#sortc").val(), 'sort' : $("#sort").val()};
			break;
		case 'change':
			if(data){
				$.ajax({
					url:'index.php?c=index&a=listChange&tagId=' + tagId + "&moduletype=" + moduletype,
					dataType: 'html',
					type: 'GET',
					success: 
					function(html, status){
						if(html){
							o.html(html);
						}
					},
					error:
					function(xml,status){	
						alert(xml.responseText);
					}
				});
			}
	}
}

function tagproductList(type, data){
	switch(type){	
		case 'bind':
			var w = 170;
			var h = 150;
			if(data){
				$.each(data, function(i, item){	
					if(i == 'width'){
						w = item;	
					}else if(i == 'height'){
						h = item;	
					}
				});
			}	
			$("#slider-range-width").slider({
				range: "min",
				value: w,
				min: 40,
				max: 170,
				animate: true, 
				slide: function(event, ui) {
					$("#width").val(ui.value);
				}
			});
			$("#width").val($("#slider-range-width").slider("value"));
			$("#slider-range-height").slider({
				range: "min",
				value: h,
				min: 40,
				max: 150,
				animate: true, 
				slide: function(event, ui) {
					$("#height").val(ui.value);
				}
			});
			$("#height").val($("#slider-range-height").slider("value"));
			break;		
		case 'show':	
			if(data.columns_rs){
				var str = '';
				$.each(data.columns_rs, function(i, item){	
					str += "<option value='" + item.id + "'>" + item.name + "</option>";							
				});
				$('#column_id').html(str);
			}
			
			if(data.categories_rs){
				var str = '';
				$.each(data.categories_rs, function(i, item){	
					str += "<option value='" + item.object.id + "'>";
					if(item.grade == 1){
						 str += item.object.name;
					}else if(item.grade == 2){
						 str += "&nbsp;&nbsp;|--" + item.object.name;
					}
					str += "</option>";							
				});
				$('#category_id').html(str);
			}
			if(data.rs){
				setTimeout(function(){
					$.each(data.rs, function(i, item){
						$("#" + i).val(item);
					});
				}, 1000);
			}
			$("#tagId").val(tagId);
			break;
		case 'validate':
			return { 'introductionNum': { 0:2, 1:'请输入正确的描述字数', 2:/^([1-9]{1})([0-9]{0,1})([0-9]{0,1})$/}, 'limit': { 0:2, 1:'请输入正确的产品数量', 2:/^([1-9]{1})([0-9]{0,1})$/}};
			break;			
		case 'save':
			return { 'column_id' : $("#column_id").val(), 'category_id' : $("#category_id").val(), 'introductionNum' : $("#introductionNum").val(), 'limit' : $("#limit").val(), 'priceUnit' : $("#priceUnit").val(), 'sortc' : $("#sortc").val(), 'sort' : $("#sort").val(), 'width' : $("#width").val(), 'height' : $("#height").val()};
			break;
		case 'change':
			if(data){
				$.ajax({
					url:'index.php?c=index&a=listChange&tagId=' + tagId + "&moduletype=" + moduletype,
					dataType: 'html',
					type: 'GET',
					success: 
					function(html, status){
						if(html){
							o.html(html);
						}
					},
					error:
					function(xml,status){	
						alert(xml.responseText);
					}
				});
			}
	}
}

function tagcomments(type, data){
	switch(type){			
		case 'show':
			if(data){
				$.each(data, function(i, item){
					$("#" + i).val(item);
				});
			}
			$("#tagId").val(tagId);
			break;
		case 'validate':
			return { 'limit': { 0:2, 1:'请输入正确的评论条数', 2:/^([1-9]{1})([0-9]{0,1})$/}};
			break;			
		case 'save':
			return { 'type' : $("#type").val(), 'limit' : $("#limit").val(), 'time' : $("#time").val()};
			break;
		case 'change':
		//console.log(o)
			if(data){
				$.ajax({
					url:'index.php?c=index&a=commentsChange&tagId=' + tagId,
					dataType: 'html',
					type: 'GET',
					success: 
					function(html, status){
						if(html){
							o.html(html);
						}
					},
					error:
					function(xml,status){	
						alert(xml.responseText);
					}
				});
			}
	}
}

function tagarticleContent(type, data){
	switch(type){
		case 'show':
			if(data){
				$.each(data, function(i, item){
					$("#" + i).val(item);
				});
			}
			$("#tagId").val(tagId);
			break;			
		case 'save':
			return { 'is_views' : $("#is_views").val(), 'is_posttime' : $("#is_posttime").val()};
			break;
		case 'change':
			if(data){
				var posttimestr = '', viewsstr = '';
				//console.log(data);
				$.each(data, function(i, item){
					switch(i){
						case 'is_posttime':
							if(item){
								posttimestr = '<span id="pub_date">发布时间 #文章发布时间# &nbsp;&nbsp;&nbsp;&nbsp;</span>';
							}
							break;
						case 'is_views':
							if(item){
								viewsstr = '<span id="media_name">浏览次数 #文章浏览次数#</span>';
							}
							break;
					}
					
				})
				var str = '<div class="blkContainerSblk"><h1>#文章标题#</h1><div class="artInfo">' + posttimestr + viewsstr + '</div><div class="blkContainerSblkCon">#文章内容#</div></div>';
				o.find('.context').html(str);
			}
	}
}

function tagproductContent(type, data){
	switch(type){
		case 'show':
			if(data){
				$.each(data, function(i, item){
					$("#" + i).val(item);
				});
			}
			$("#tagId").val(tagId);
			break;			
		case 'save':
			return { 'is_views' : $("#is_views").val(), 'priceUnit' : $("#priceUnit").val(), 'time' : $("#time").val()};
			break;
		case 'change':
			if(data){
				$.ajax({
					url:'index.php?c=index&a=contentChange&tagId=' + tagId + "&moduletype=" + moduletype,
					dataType: 'html',
					type: 'GET',
					success: 
					function(html, status){
						if(html){
							o.html(html);
						}
					},
					error:
					function(xml,status){	
						alert(xml.responseText);
					}
				});
			}
	}
}

function taglinksText(type, data){
	switch(type){
		case 'bind':
			iColorPicker();
			break;
		case 'unbind':
			$("#iColorPicker").hide();
			break;				
		case 'show':
			if(data){
				$.each(data, function(i, item){	
					if(i == 'Color'){
						$("#" + i).val(item);
						$("#Color").css('background', item);	
					}else{
						$("#" + i).val(item);	
					}					
				});
			}
			$("#tagId").val(tagId);
			break;
		case 'save':
			return { 'textAlign' : $("#textAlign").val(), 'Font' : $("#Font").val(), 'FontSize' : $("#FontSize").val(), 'FontStyle' : $("#FontStyle").val(), 'Color' : $("#Color").val()};
			break;
		case 'change':
			if(data){
				$.each(data, function(i, item){
					switch(i){
						case 'textAlign':
							o.find("ul li").css(i, item);
							break;
						case 'Font':
							o.find("ul li a").css('fontFamily', item);
							break;
						case 'FontStyle':
							if(item == 'italic'){
								o.find("li a").css('fontStyle', item);
							}else if(item == 'bold'){
								o.find("ul li a").css('fontWeight', item);
							}else if(item == ''){
								o.find("p").css('fontWeight', '');
								o.find("p").css('fontStyle', '');
							}
							break;
						case 'FontSize':
							if(item){
								o.find("ul li a").css('fontSize', item + 'px');
							}else{
								o.find("ul li a").css('fontSize', '');
							}
							break;										
						case 'Color':
							o.find("li a").css('color', item);
							break;	
					}
					
				})
			}
	}
}


function tagcustomerService(type, data){
	switch(type){	
		case 'show':
			if(data){
				$.each(data, function(i, item){	
					$("#" + i).val(item);					
				});
			}
			$("#tagId").val(tagId);
			break;
		case 'save':
			return { 'style' : $("#style").val(), 'position' : $("#position").val()};
			break;
		case 'change':
			if(data){
				$.each(data, function(i, item){
					switch(i){
						case 'position':
							o.css({ 'left':null, 'right':null}).css(item, 0);
							break;
						case 'style':
							o.find(".context").children(0).attr('class', item);
							break;
					}
					
				})
			}
	}
}

function taglinksImg(type, data){
	switch(type){
		case 'bind':
			var w = 180;
			var h = 80;
			if(data){
				$.each(data, function(i, item){	
					if(i == 'width'){
						w = item;	
					}else if(i == 'height'){
						h = item;	
					}
				});
			}	
			$("#slider-range-width").slider({
				range: "min",
				value: w,
				min: 40,
				max: 180,
				animate: true, 
				slide: function(event, ui) {
					$("#width").val(ui.value);
				}
			});
			$("#width").val($("#slider-range-width").slider("value"));
			$("#slider-range-height").slider({
				range: "min",
				value: h,
				min: 40,
				max: 80,
				animate: true, 
				slide: function(event, ui) {
					$("#height").val(ui.value);
				}
			});
			$("#height").val($("#slider-range-height").slider("value"));
			break;			
		case 'show':
			if(data){
				$.each(data, function(i, item){	
					$("#" + i).val(item);
				});
			}
			$("#tagId").val(tagId);			
			break;	
		case 'save':
			return { 'width' : $("#width").val(), 'height' : $("#height").val()};			
			break;
		case 'change':
			if(data){
				$.each(data, function(i, item){
					switch(i){									
						case 'width':
							o.find("ul li a img").css(i, item + 'px');
							break;
						case 'height':
							o.find("ul li a img").css(i, item + 'px');
							break;								
					}	
				});
			}
	}
}

function tagFlash(type, data){
	switch(type){
		case 'bind':
			$("input[type=file].multi").MultiFile();
			break;			
		case 'show':
			if(data){
				$.each(data, function(i, item){	
					if(i == 'flashSwf' && item != ''){
				        var str = '<object width="80%" align="middle" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000">';
						str += '<param value="sameDomain" name="allowScriptAccess">';
						str += '<param name="movie" value="' + item + '">';
						str += '<param name="wmode" value="transparent">';
						str += '<param name="quality" value="autohigh">';
						str += '<embed width="80%" align="middle" wmode="transparent" src=" ' + item + '" quality="high" swliveconnect="true" allowscriptaccess="sameDomain" type="application/x-shockwave-flash"></embed>';
						str += '</object>';
						$("#flashTestDiv").append(str).show();
					}
				});
			}
			$("input[type=hidden][name=tagId]").val(tagId);			
			break;
		case 'validate':
			if (flashFlag == 1){
				return { 'flashSwf': { 0:1, 1:'请选择Swf文件'}};
			}else if (flashFlag == 2){
				return { 'flashSwf': { 0:2, 1:'Swf文件链接地址错误', 2:/.swf$/i}};
			}
			break;
		case 'save':
			if (flashFlag == 1){
				return { 'flashSwf': $("input[type=file].multi").val()};
			}else if (flashFlag == 2){
				return { 'flashSwf': $("input[type=text][name=flashSwf]").val()};
			}
			break;
		case 'change':
			if(data){
				$.each(data, function(i, item){
					switch(i){
						case 'flashSwf':
							var str = '<object width="100%" align="middle" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000">';
							str += '<param value="sameDomain" name="allowScriptAccess">';
							str += '<param name="movie" value="' + item + '">';
							str += '<param name="wmode" value="transparent">';
							str += '<param name="quality" value="autohigh">';
							str += '<embed width="100%" align="middle" wmode="transparent" src=" ' + item + '" quality="high" swliveconnect="true" allowscriptaccess="sameDomain" type="application/x-shockwave-flash"></embed>';
							str += '</object>';
							o.find("#flashShowDiv").html(str);							
							break;							
					}
					
				})
			}
	}
}

function taghtmlEditor(type, data){
	switch(type){
		case 'bind':
			$('#content').xheditor({
				upLinkUrl:"upload.php",
				upLinkExt:"zip,rar,txt",
				upImgUrl:"upload.php",
				upImgExt:"jpg,jpeg,gif,png",
				upFlashUrl:"upload.php",
				upFlashExt:"swf",
				upMediaUrl:"upload.php",
				upMediaExt:"wmv,avi,wma,mp3,mid",
				shortcuts:{
					'ctrl+enter': function() {
						tagEdit(taghtmlEditor('save'), taghtmlEditor('validate')); 		
					}
				}
			});
			break;			
		case 'show':
			if(data){
				$.each(data, function(i, item){	
					$("#" + i).val(item);
				});
			}	
			$("#tagId").val(tagId);
			break;
		case 'validate':
				return { 'content': { 0:1, 1:'请输入内容'}};
			break;
		case 'save':
			return { 'content' : $("#content").val()};	
			break;
		case 'change':
			if(data){
				$.each(data, function(i, item){
					o.find(".context").html('').html(item);			
				})
			}
	}
}

function taghtmlCode(type, data){
	switch(type){
		case 'show':
			if(data){
				$.each(data, function(i, item){	
					$("#" + i).val(item);
				});
			}		
			$("#tagId").val(tagId);	
			break;
		case 'validate':
				return { 'jscode': { 0:1, 1:'请输入Js代码'}, 'jscode': { 0:2, 1:'请输入正确的Js代码', 2:/^(<script)[\s\S]*(<\/script>)$/i}};
			break;
		case 'save':
			return { 'jscode' : $("#jscode").val()};	
			break;
		case 'change':
			if(data){
				$.each(data, function(i, item){
					o.find(".context .jscode").hide();									  
					o.find(".context .addjs").show();
				})
			}
	}
}

function tagShadeMaskOn(){
	$("#smoothmenu").hide();
	var w = $(this).width();
	var h = $(this).height();
	o = $(this);
	moduletype = $(this).attr('id');
	if(moduletype == 'searchForm' && $.browser.msie && $.browser.version == '6.0'){
		$('#searchtype').hide();	
	}
	tagId = $(this).attr('lang');
	$('.shade_mask').remove();
	var className = $(this).attr('class');
	var str = '<div class="shade_mask" style="width:' + w + 'px; height:' + h + 'px;"><ul>';
	var reg3 = /noedit$/;
	if(!reg3.test(className)){
		str += '<li><a href="#nogo" class="edit_module" title="编辑模块"><img src="images/edit.gif" /></a></li>';
	}
	
	var reg1 = /^side/;
	var reg2 = /^main_cell/;	
	if(reg1.test(className) || reg2.test(className)){
		str += '<li><a href="#nogo" class="del_module" title="删除模块"><img src="images/x.gif" /></a></li>';
	}
	str += '</ul></div>';
	$(this).append(str);	
	$('.del_module').click(deleteModule);
	$('.edit_module').click(editModule);	
	
}

function tagShadeMaskOver(){
	$('.shade_mask').remove();
	if(moduletype == 'searchForm' && $.browser.msie && $.browser.version == '6.0'){
		$('#searchtype').show();	
	}
}


$(function() {

	sortableMothed("#LeftWrapper", "#MonostichousBox, #BiserialLeftBox, #BiserialRightBox, #RightWrapper");
	sortableMothed("#MonostichousBox", "#LeftWrapper, #RightWrapper, #BiserialLeftBox, #BiserialRightBox");
	sortableMothed("#RightWrapper", "#LeftWrapper, #MonostichousBox, #BiserialLeftBox, #BiserialRightBox");
	sortableMothed("#BiserialLeftBox", "#BiserialRightBox, #LeftWrapper, #MonostichousBox, #RightWrapper");
	sortableMothed("#BiserialRightBox", "#BiserialLeftBox, #LeftWrapper, #MonostichousBox, #RightWrapper");
		
	ShowHidehint();

	$('.side, .main_cell, #Banner').hover(tagShadeMaskOn, tagShadeMaskOver);
	
	$("#operateDialog").show()
	.dialog({
		modal: true,
		autoOpen: false,
		buttons: {
			"取消": function() {
				$(this).dialog("close");
				switch(moduletype){
					case 'linksText':
					case 'wordList':
					case 'leftofRight':
					case 'rightofLeft':				
					case 'figureBelow':	
					case 'figureAbove':				
					case 'text':
						eval("tag" + moduletype + "('unbind')");
						break;
				}
			},
			"确定": function() {
				if(tagId){
					switch(moduletype){
						case 'titleText':
						case 'text':
						case 'wordList':
						case 'singleImage':
						case 'multiImage':
						case 'leftofRight':			
						case 'figureBelow':
						case 'rightofLeft':						
						case 'figureAbove':
						case 'Flash':
						case 'htmlEditor':
						case 'htmlCode':
						case 'articleList':
						case 'productList':	
						case 'comments':
							eval("tagEdit(tag" + moduletype + "('save'), tag" + moduletype + "('validate'))");	
							break;
						case 'linksText':
						case 'linksImg':
						case 'articleContent':
						case 'productContent':
						case 'customerService':
							eval("tagEdit(tag" + moduletype + "('save'))");	
							break;
					}
				}
			}
		},
		width: '500px',
		height: '525',
		autoResize: false,
		resizable: false
	});
		
	$('.add_modul_btn').click(function(){
		tagObj = $(this).parent();
		
	})
	$('.tagAdd').click(function(){
		$('#smoothmenu').hide();
		var code = $(this).attr('href').substr(1);
		if(code != 'nogo'){
			var area = tagObj.parent().attr('id');
			$.ajax({
				url:'index.php?c=index&a=tagAdd&code=' + code + '&area=' + area + '&template_page_id=' + template_page_id,
				dataType: 'html',
				type: 'GET',
				success: 
				function(html, status){
					if(html){
						if(code == 'tag_customerservice'){
							$('body').append(html);
						}else{
							tagObj.next().after(html);
							tagObj.find('.edit_add_hint').hide();
						}
						$('.side, .main_cell').hover(tagShadeMaskOn, tagShadeMaskOver);
					}
				},
				error:
				function(xml,status){
					alert(xml.responseText);
				}
			});	
		}
	});
	
	$('.l_close').click(function(){
		$(this).hide();						 
		$("#LeftWrapper").hide();
		var w;
		if($("#RightWrapper").css('display') == 'none'){
			w = "1000px";
		}else{
			w = "795px";	
		}
		$("#MiddleWrapper").css('width', w);
		$("#MiddleWrapper").css('marginLeft', 0);
		$('.l_open').show();
	})
	.hover(
		function(){
			var w = $("#LeftWrapper").width();
			var h = $("#LeftWrapper").height();
			$("#LeftWrapper").append('<div class="ShowHide_Mask" style="width:' + w + 'px; height:' + h + 'px;"></div>');	
		},
		function(){
			$('.ShowHide_Mask').remove();
		}			
	);
	
	$('.r_close').click(function(){
		$(this).hide();
		$("#RightWrapper").hide();
		var w;
		if($("#LeftWrapper").css('display') == 'none'){
			w = "1000px";
		}else{
			w = "795px";	
		}
		$("#MiddleWrapper").css('width', w);
		$('.r_open').show();
	})
	.hover(
		function(){
			var w = $("#RightWrapper").width();
			var h = $("#RightWrapper").height();
			$("#RightWrapper").append('<div class="ShowHide_Mask" style="width:' + w + 'px; height:' + h + 'px;"></div>');	
		},
		function(){
			$('.ShowHide_Mask').remove();
		}			
	);
	
	$('.l_open').click(function(){
		$(this).hide();
		var w;
		if($("#RightWrapper").css('display') == 'none'){
			w = "785px";
		}else{
			w = "593px";	
		}
		$("#MiddleWrapper").css('width', w);
		$("#MiddleWrapper").css('marginLeft', '10px');
		$("#LeftWrapper").show();
		$('.l_close').show();
	});
	
	$('.r_open').click(function(){
		$(this).hide();
		var w;
		if($("#LeftWrapper").css('display') == 'none'){
			w = "785px";
		}else{
			w = "593px";	
		}
		$("#MiddleWrapper").css('width', w);
		$("#RightWrapper").show();
		$('.r_close').show();
	});
	
	$(".add_modul_btn").click(function(){
		var l = $(this).offset().left + 'px';
		var t = $(this).offset().top + 21 + 'px';
		$("#smoothmenu").css('left', l).css('top', t).show();							   
	})
			
	/* Show menu when #myDiv is clicked
	$(".LeftWrapper, .MonostichousBox, .BiserialLeftBox, .BiserialRightBox, .RightWrapper").contextMenu({
			menu: 'smoothmenu'
		}
		/*,
		function(action, el, pos) {
		alert(
			'Action: ' + action + '\n\n' +
			'Element ID: ' + $(el).attr('id') + '\n\n' + 
			'X: ' + pos.x + '  Y: ' + pos.y + ' (relative to element)\n\n' + 
			'X: ' + pos.docX + '  Y: ' + pos.docY+ ' (relative to document)'
			);
		}
	);
	*/
});
//document.body.oncontextmenu=function(){ return false;}
ddsmoothmenu.init({
	mainmenuid: "smoothmenu", //Menu DIV id
	orientation: 'v', //Horizontal or vertical menu: Set to "h" or "v"
	classname: 'ddsmoothmenu-v', //class added to menu's outer DIV
	//customtheme: ["#804000", "#482400"],
	contentsource: "markup" //"markup" or ["container_id", "path_to_menu_file"]
})