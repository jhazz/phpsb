PFD.Classes.group={
create:function(){return this;}
};
with(PFD.Classes.group.create){
prototype.constructHtml=function(mode){
var s="<div style='background-color:#e8e8e8; font-size:9px;'>"+path+"</div>",i;
for(var i in this.e[2])s+=PFD.construct(this.e[2][i],this.form,this.iPath,mode,this);
return s;}
prototype.childChanged=function(){this.parentCtrl.childChanged();}
}
