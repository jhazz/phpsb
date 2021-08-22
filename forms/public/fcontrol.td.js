PFD.create('PF.td',{
constructHtml:function(mode){
	var i,r="",m=mode,
	w=this.e[1].width,wa=(w)?"width:"+w+";":"",h=this.parentCtrl.height,ha=(h)?"height:"+h+";":"";
	this.parentCtrl.cols[this.e[1]._col]=this;
	for(i in this.e[2]){r+=PFD.construct(this.e[2][i],this.form,this.iPath,m,this,this.iNode,this.id);}
	return "<td onClick='PFD.ctrlEvent(\"click\",this,event)' width='"+w+"' id='"+this.ctrlId+":td'><div style='"+wa+ha+"overflow:hidden;' id='"+this.ctrlId+"'>"+r+"</div>"+this.e[1]._col+"</td>";	
},
init:function(){
	var c,id;
	this.table=this.parentCtrl.table;
	PF.control.prototype.init.apply(this);
	this.cell=P$.find(this.ctrlId+':td');
},
click:function(ev){this.focus();}
});