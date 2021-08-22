PFD.create('PF.th',{
constructHtml:function(mode){
	var i,r="",m=mode,cs=this.e[1]['colspan'],rs=this.e[1]['rowspan'],csa=(cs)?"colspan=' "+cs+"'":"",
	rsa=(rs)?"rowspan='"+rs+"' ":"",w=this.e[1]['width'],wa=(w)?"width:"+w+";":"",
	h=Int(this.e[1]['height']);if(!h)h=this.parentCtrl.height;ha=(h)?"height:"+h+";":"";
	
	for(i in this.e[2]){r+=PFD.construct(this.e[2][i],this.form,this.iPath,m,this,this.iNode,this.id);}
	return "<td "+rsa+csa+" class='pfds-thead' width='"+w+"' id='"+this.ctrlId+":td'><div style='"+wa+ha+"overflow:hidden;' id='"+this.ctrlId+"'>"+r+"</div></td>";	
},
init:function(){
	PF.control.prototype.init.apply(this);
	this.cell=P$.find(this.ctrlId+':td');
}
});