PFD.create('PF.text',
{
constructHtml:function(mode){
	this.mode=mode;
	var s,v="#UNDEF#",n=this.iNode,size=this.e[1].size;
	if (n!=undefined)	{v=PFD.getContent(n);}
	if (size) size=" size='"+size+"'"; else size="";
	s="";
//	if (mode & 1) {
		s="<div onKeyDown='return PFD.ctrlEvent(\"keyDown\",this,event);' class='pfds-input"+((mode & 1)?"' contentEditable":"-readonly' ") +size+" style='white-space:nowrap' id='"+this.ctrlId+":text' onKeyup='PFD.ctrlEvent(\"change\",this);' ref='"+this.iPath+"'>"+v+"</div><div id='"+this.ctrlId+":foot'></div>";
//	} else s="<b>"+v+"</b>";
	return s;
},
init:function(){
	this.text=P$.find(this.ctrlId+":text");
},
change:function(ev){
	this.setValue(this.text.innerHTML,1,0);
},
keyDown:function(ev){
	var t;
	if(ev.keyCode==13)return false;
	var tr,ta,r,c,f,cf=0,p=this.parentCtrl;
	c=p.e[1]._col;
	tr=p.parentCtrl;
	ta=tr.parentCtrl;
	r=tr._row;
	if(p.tagName=='forms:td'){
		;
		
		switch(event.keyCode){
		case 38:if(r>0)r--,cf=1;break;
		case 40:if(r<10)r++,cf=1;break;
		case 37: t=document.selection.createRange(); t.moveEnd(); t.select(); break;
		//case 37:if(c>0)c--,cf=1;break;
		//case 39:if(c<4)c++,cf=1;break;
		}
		if(cf)this.parentCtrl.table.rows[r].cols[c].focus();
	}
	//if(this.onKeyDown)return this.onKeyDown(ev);
},
focus:function(){
	this.text.setActive(); return 1;
},
setValue:function(v,dontDisplay,dontRefresh) {
	var old=PFD.getContent(this.iNode);
	if(old==v)return;
	PFD.setContent(this.iNode,v);
	this.iNode.setAttribute('changed',1);
	if (!dontDisplay) this.text.innerHTML=v;
	if (!dontRefresh) this.parentCtrl.childChanged();
},
regenerate:function(){
	this.text.innerHTML=this.constructHtml(this.mode);
}
});


/*
function forms_fcontrol_text(e,form,path,mode) {
	var tt,n,ctrlId=PFD.getNewCtrlId(form),v="",s,size=e[1].size;
	n=PFD.findInstanceNode(form.dom,path);
	if (n!=undefined)	v=n.text;
	if (size) size=" size='"+size+"'"; else size="";
	PFD.subscribeCtrlForData(forms_fcontrol_text_update,path,form,ctrlId);
	if (mode & 1) {
		s="<table width='1'><tr><td nowrap><input"+size+" id='"+ctrlId+"'  onchange='PFD.CC(this);' onkeyup='PFD.CC(this);' ref='"+path+"' type='text' value='"+v+"'/><br/><span style='font-size:9px'>"+path+"</span><span id='"+ctrlId+"_foot'></span></td></tr></table>";
	} else s="<b>"+v+"</b>";
	return s;
}


function forms_fcontrol_text_update(ctrlInfo,newValue,state) {
  //ctrlInfo[]:  0-form, 1-callback on setvalue 2-ctrlId 3-model node 4-instanceId 5-instancePath 6-control(init after any use)
  var input=document.getElementById(ctrlInfo[2]);
	var foot=document.getElementById(ctrlInfo[2]+"_foot");
	var m,i,s="";

	if (newValue!=undefined) {input.value=newValue;}
	
	if (state) {
		if(state.errors){
			input.style.borderColor='#f00000';
			for(i in state.errors){m=state.errors[i];s+="<table cellpadding='2' cellspacing='0'><tr bgcolor='#ff8080'><td><img src='"+sysPubURL+"/ico_error.gif'></td><td  style='font-size:9px'>"+m+"</td></tr></table>";}
		} else input.style.borderColor='#ffffff';
		if(state.warnings){for(i in state.warnings){m=state.warnings[i];s+="<table cellpadding='2' cellspacing='0'><tr bgcolor='#f0fff0'><td><img src='"+sysPubURL+"/ico_warning.gif'></td><td style='font-size:9px'>"+m+"</td></tr></table>";}}
	}
	foot.innerHTML=s;
}
*/
