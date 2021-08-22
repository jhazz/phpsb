PFD.create('PF.table',{
rows:[],
constructHtml:function (mode) {
	var w,h,sw=PFD.style.scrollBarWidth;
	this.limit=Int(this.e[1].limit);
	this.offset=Int(this.e[1].offset);
	this.total=Int(this.e[1].total);
	
	w=this.w=this.width=Int(this.e[1].width);
	h=this.h=this.height=Int(this.e[1].height);
	if(this.e[1].mode!=undefined)mode=Int(this.e[1].mode);
	this.mode=mode;
	if(!h){return "<table cellspacing='0' cellpadding='0' "+((w)?"width='"+w+"'":"")+" id='"+this.ctrlId+"'><tr><td><div style='width:100%' id='"+this.ctrlId+":table'>"+s+"</div></td></tr></table>";}
return "<table "+((w)?"width='"+w+"'":"")+" id='"+this.ctrlId+"'><tr><td width='100%'><div onMouseWheel='return PFD.ctrlEvent(\"onMouseWheel\",this,event)' id='"+this.ctrlId+":table' style='"+((w)?"width:"+(w-sw)+";":"")+"height:"+h+";overflow:hidden;overflow-x:auto;'>"
  +this.constructChildsHtml(mode)+"</div></td>"
	+"<td valign='top' width='"+sw+"'><div id='"
	+this.ctrlId+":sc' ctrlId='"+this.ctrlId+"' onScroll='return PFD.ctrlEvent(\"onScroll\",this)' style='padding:0;position:relative;height:"+h+";width:"+sw+";overflow-x:hidden;overflow-y:auto;' ><div id='"
	+this.ctrlId+":sf' style='height:1;width:1;'></div></div></td></tr>"
	+"<tr><td bgcolor='#f0f0ff' colspan='2'><table width='100%'><tr>"
	+ ((mode&1)?"<td style='font-size:9px' width='1' align='right'><a href='javascript:;' onClick='PFD.ctrlMethod(\"onInsertClick\",\""+this.ctrlId+"\")' title='"+this.iPath+"'>[+ Добавить]</a></td>":"")
	+"<td style='font-size:7px' id='"+this.ctrlId+":info'>"+this.iPath+"</td>"
	+"</tr></table></td></tr></table>";
},
init:function(){
	this.sc=P$.find(this.ctrlId+":sc");
	this.ctab=P$.find(this.ctrlId+":table");
	this.cin=P$.find(this.ctrlId+":info");
	this.sf=P$.find(this.ctrlId+":sf");
	PF.control.prototype.init.apply(this);
	if(!this.dontUpdateScroll) this.updateScrollbar();
},
onInsertClick:function(){
	var pn=PFD.getRefPathAndNode(this.form,this.iNode,this.iPath,this.ns,1,1); 
	pn[1].setAttribute('ins','1');
	this.parentCtrl.childChanged();
	this.regenerate();	
},
onScroll:function(ev){
	if(this.cancelScrollEvent){this.cancelScrollEvent=false;return;}
	if(this.delay){return;}
		//window.clearTimeout(this.delay);
	this.delay=window.setTimeout("PFD.ctrlMethod('actUpPosFromScroll','"+this.ctrlId+"');",300);
},
actUpPosFromScroll:function(){
  if(this.delay) window.clearTimeout(this.delay);
  this.delay=0;
  this.offset=Math.round((this.sc.scrollTop * this.limit) / this.height);
  this.dontUpdateScroll=true;
  this.regenerate();
	this.dontUpdateScroll=false;
},
actScrollBy:function(delta){
	if(this.total){
		var p=this.total-this.limit;
		this.offset+=delta;
		if(this.offset>p)this.offset=p;
		if(this.offset<0)this.offset=0;
	}else this.offset=0;
	this.cancelScrollEvent=true;
	this.regenerate();
},
onMouseWheel:function(ev){
	ev.cancelBubble=true;
	PFD.ctrlMethod('actScrollBy',this.ctrlId,-ev.wheelDelta/120);
	return true;
},
regenerate:function() {
	for(var id in this.childCtrls){PFD.removeControl(this.childCtrls[id]);}
	delete(this.childCtrls);this.childCtrls={};
	this.ctab.innerHTML=this.constructChildsHtml(this.mode);
	this.cin.innerHTML=this.iPath+" "+
		((this.loading)?"Идет загрузка":"["+this.offset+".."+(this.offset+this.limit-1)+"]("+this.total+")");
	this.init();
},
updateScrollbar:function(){
  var v="hidden",n=this.limit,o=this.offset,tot=this.total,h=this.height,fh;
  if((tot)&&(n>0)&&(n<tot)){
  	v="visible";
    this.sf.style.height=tot/n*h;
    this.sc.scrollTop=h*o/n;
  }
	this.sc.style.visibility=v;
},
constructChildsHtml:function(mode){
	var el,tr,scol="",ins,ns,i,j,nn,n,selCount,id,deleted,node,p,sel,s="",pn,thead,tfoot,_row;
	this.ns=this.e[1].nodeset;
	if (!this.ns) {PFD.msg('Не указан атрибут nodeset у FORMS:TABLE',"",1); return;}
	
//	ns=PFD.concatPath(this.iPath,ns);
//getRefPathAndNode:function(form,contextNode,contextPath,ref,doCreate,doInsert,id){
	pn=PFD.getRefPathAndNode(this.form,this.iNode,this.iPath,this.ns,0,0);
	
	if (!pn) {PFD.msg('FORMS:TABLE:constructChilds - Не нашел '+this.iPath,ns,1); return;}
	
//	r=PFD.extractPathElements(ns);
//	ins=this.form.instances[r[1]];
	
	/*
	if(!ins){PFD.msg("Unknown instance",r[1],1);return;}
	if(ins.paging){
		// 1. Проверяем есть ли искомые строки в загруженных страницах
		this.total=ins.total;
		if(ins.rows){
			n=this.limit;if(!n)n=5;
			nn=this.offset+n;
			if (this.total!=undefined){if(nn>this.total)nn=this.total;}
			for(i=this.offset;i<nn;i++) {
				node=ins.rows[i];
				if(node){
					id=node.getAttribute('id');
					if(!id){PFD.msg('id not found in element['+i+']',ns,1);continue;} //  не должно быть элементов без id
					for (j in this.e[2]){
						s+=PFD.construct(this.e[2][j],this.form,ns+'[@id='+id+']',mode,this,id,node); // устанавливаем alreadyFoundNode
					}
				}
			}
		} else{if(ins.loading)return "Идет загрузка"; else return "Данных нет";}
		//s="<tr><td>zzz</td></tr>";
	}else{
		*/
		
		sel=pn[1];
		try{sel=sel.parentNode.childNodes;}catch(e){PFD.msg("FORMS:TABLE.constructChildsHtml: "+e.message,pn[0]);}
		if (sel) {
			this.total=n=sel.length;
			if(this.limit){if(n>this.limit)n=this.limit;}
			nn=this.offset+n;
			if(nn>this.total)nn=this.total;
			for(j in this.e[2]){
				el=this.e[2][j];
				if(el[0]=='forms:tr'){tr=el;break;}
				if(el[0]=='forms:thead')thead=el;
				if(el[0]=='forms:tfoot')tfoot=el;
			}
			if(!tr){alert('Tag FORMS:TABLE should contain at least one FORMS:TR');return;}

			if(thead){
				for (j in thead[2]){//tr's
					if(thead[2][j][0]=='forms:tr'){
						thead[2][j][1]['head']=1;
						s+=PFD.construct(thead[2][j],this.form,pn[0],mode,this,pn[1],0); // НАОЩУПЬ. Последние аргументы неясно как поведут
					}
				}
			}
			_row=0;
			for(i=this.offset;i<nn;i++) {
				node=sel(i);
				if (node==null){alert('!');continue;}
				id=node.getAttribute('id');
				tr[1]._row=_row;
				if(!id){PFD.msg('id not found in element['+i+']',ns,1);continue;} //  не должно быть элементов без id
				s+=PFD.construct(tr,this.form,pn[0]+'[@id='+id+']',mode,this,node,id);
				_row++;
			}
			
			//delete(sel);
		} else {this.total=0; s+="<tr><td>Пусто</td></tr>";}
		
	//}
	
	return "<table border='1' cellspacing='0' cellpadding='0' width='100%'>"+scol+s+"</table>";
}
});
