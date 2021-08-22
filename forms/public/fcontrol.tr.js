PFD.create('PF.tr',{
constructHtml:function(mode){
	this.height=this.e[1].height;
	var _col=0,i,r="",deleted,m,ha=(this.height)?"height='"+this.height+"' ":"",color=PFD.colors.ordinal;
	m=mode;deleted=0;
	this._row=this.e[1]._row;
	this.table=this.parentCtrl;
	this.table.rows[this._row]=this;
	this.cols=[];
	if (!this.iNode){
		return "<tr><td colspan='3'>Данных нет "+this.iPath+"</td></tr>";
	}
	if (this.iNode.getAttribute('changed')) color=PFD.colors.changed;
	if (this.iNode.getAttribute('del')) {color=PFD.colors.deleted;m=0;deleted=1;}
	if (this.iNode.getAttribute('ins')) color=PFD.colors.inserted;
	
	for(i in this.e[2]){this.e[2][i][1]._col=_col;r+=PFD.construct(this.e[2][i],this.form,this.iPath,m,this,this.iNode,this.id);_col++;}
	if(this.e[1].head){return"<tr><td></td>"+r+"</tr>";}
	return "<tr "+ha+"onMouseOut='PFD.ctrlEvent(\"onMouseOut\",this);' onMouseOver='PFD.ctrlEvent(\"onMouseOver\",this,0);' valign='top' id='"+this.ctrlId+":tr' bgcolor='"+color+"'><td width='1' style='font-size:9px'>"
	+"<input type='checkbox' value='1'>"+this.e[1]._row+"<in put id='"+this.ctrlId+":db' class='button' type='button' onclick='PFD.ctrlEvent(\"onDelete\",this,"+deleted+")' value='"+((deleted)?"не удалять":"удалить")+"'/></td>"+r+"</tr>";
},
init:function(){
	this.tr=P$.find(this.ctrlId+':tr');
	PF.control.prototype.init.apply(this);

//	var c,id;for(id in this.childCtrls){c=this.childCtrls[id];if(c.init)c.init();}
},
childChanged:function(){
	this.iNode.setAttribute('changed',1);
	var color=PFD.colors.changed;
	if (this.iNode.getAttribute('ins')) color=PFD.colors.inserted;
	this.oldbg=this.tr.style.backgroundColor=color;
	this.parentCtrl.childChanged();
},
onDelete:function(undelete){
	n=this.iNode;
	if(!n){alert('No node for TR action');}
	pc=c.parentCtrl;
	if(undelete)n.removeAttribute('del'); 
	else {if (n.getAttribute('ins')) n.parentNode.removeChild(n); else n.setAttribute('del',1);}
	pc.childChanged();
	pc.regenerate();
},
onMouseOver:function(ev){
	this.oldbg=this.tr.style.backgroundColor;
	this.tr.style.backgroundColor='#f5f5f5';
},
onMouseOut:function(ev){
	this.tr.style.backgroundColor=this.oldbg;
}
});