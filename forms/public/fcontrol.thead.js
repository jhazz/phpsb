PFD.create('PF.thead',{
constructHtml:function(mode){
	var i,r="";
	for(i in this.e[2]){r+=PFD.construct(this.e[2][i],this.form,this.iPath,m,this,this.iNode,this.id);}
	return r;
}
});