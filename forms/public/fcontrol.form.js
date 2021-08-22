PFD.create('PF.form',{
constructHtml:function(mode){
var r="<div id='"+this.ctrlId+"_container'><table cellspacing='0' cellpadding='10' border='0' width='100%'><tr><td bgcolor='#f0ede3'><h2>FORM '"+this.ctrlId+"' path='"+this.iPath+"'</h2>";
for(var i in this.e[2])r+=PFD.construct(this.e[2][i],this.form,this.iPath,mode,this,this.iNode);
return r+"</td></tr><tr><td bgcolor='#eee8e0'><input type='submit' value='Ok'></td></tr></table></div>";
},
childChanged:function(){PFD.updateFormMonitor(this.form);}
});