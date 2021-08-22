
CPsbFormDispatcher.prototype.UpdateDroplist=function(id,first) {
	var field=PsbFormDispatcher.allFields[id];
	if (!field) return;

	var v=field.fValue.value;

	try {var vc=eval('ValueSet_'+field.ValueSetName+'["'+v+'"]'); field.DropImg.style.visibility='visible';}
	catch(e) {vc=undefined;field.DropImg.style.visibility='hidden'; return;}
	if (vc==undefined) vc=(field.NullCaption)?field.NullCaption:'----';
	if ((!field.Editable)||(field.DoEditValue))
	{
		field.fCaption.title=field.fCaption.innerHTML=vc;
	}

	if (!first)
	{
		dropFieldID=idname;
		dropFieldValue=v;
		if (field.OnChange) eval (field.OnChange);
	}
};

function DropDown_Set(idname,value) {
	var field=dropFields[idname];
	if (!field) return;
	try {var vc=eval('ValueSet_'+field.ValueSetName+'["'+value+'"]'); field.DropImg.style.visibility='visible';}
	catch(e) {vc=undefined;field.DropImg.style.visibility='hidden'; return;}
	dropFieldValue=value;
	dropFieldValCaption=vc;
	dropFieldID=idname;

	if (field.Editable && (!field.DoEditValue)) {value=vc;}
	else {if (field.fCaption) {field.fCaption.innerHTML=vc; field.fCaption.title=vc;}}
	field.fValue.value=value;

	if (field.OnSelect) {eval (field.OnSelect);}
	DropDown_Update(idname);
}

function DropDown_Open (srce,idname,dv)
{
	var field=dropFields[idname];
	if (!field) return;

	var v=field.fValue.value;
	var el=srce.srcElement;
	if (!el) {el=srce.target;}
	var s="",sel;
	var rc=0;
	try {var vs=eval('ValueSet_'+field.ValueSetName)} catch(e) {return;}

	if (field.NullCaption)
	{s="<option value=''"+(((v=="")||(v=="0"))?" selected":"")+">"+field.NullCaption+"</option>";
	rc++;
	}

	if (vs)
	{
		for (var i in vs)
		{
			if ((!field.DoEditValue)&&(field.Editable)) sel=(vs[i]==v)?"selected":""; else sel=(v==i)?"selected":"";
			s+="<option value='"+i+"' "+sel+">"+((dv!=undefined && dv!="")?((dv==i)?" * ":"&nbsp;&nbsp;&nbsp;"):"")+vs[i]+"</option>";
			rc++;
		}
	}
	if (rc>10) rc=10;
	if (rc<2) rc=2;

	if (s)
	{
		s="<select class='inputarea' size='"+rc+"' onChange='popupCallerWindow.DropDown_Set(\""+idname+"\",this.options[this.selectedIndex].value);' id='DropDownSelect'>"+s+"</select>";
		W.openPopupUnder(el,s);
	}
}
