function checkAll(form){
	for(i=0;i<form.elements.length;i++){
		if(form.elements[i].type == 'checkbox'){
			form.elements[i].checked = true;
		}
	}
}

function reverse(form){
	for(i=0;i<form.elements.length;i++){
		if(form.elements[i].type == 'checkbox'){
			form.elements[i].checked = !form.elements[i].checked;
		}
	}
}

function v_dels(form){
	return v_check(form, '删除');
}

function v_check(form, action){
	/*if(arguments.length == 1) {
		action = "删除";
	}
	else {
		action = arguments[1];
	}*/
	
	c = 0;
	for(i=0;i<form.elements.length;i++){
		if(form.elements[i].type == 'checkbox'){
			c += form.elements[i].checked?1:0;
		}
		if(c){
			break;	
		}
	}
	if(c){
		return confirm('确定要将选中项' + action + '吗？');
	}
	else{
		alert('没有选中任何项!');
		return false;
	}
}