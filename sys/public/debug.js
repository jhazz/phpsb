//window.status='debug был запущен ';
window.status+=typeof P$.$.regModule;

P$.$.regModule('sys.debug',{

focusedControl:0,
onusing:function(params,ldr,p){
	P$.trace('Вызван sys.debug.onUsing()','Источник: '+ldr.name,1);
	},
// вызывается после того как все модули, от которых зависит данный модуль, загружены
initModule:function(){
/*	P$.$.trace=function(a,b,c){
		var t=P$.$.traceArray;
		if(t.length<300)t.push([a,b,c]);
//		P$.run('sys.debug.updateTraceView');
	};
	*/
	P$.trace('Вызван sys.debug.initModule()','Внимание!',2);
},
getTraceHTML:function(){ 
	var x,l,f,e,c,s,r='',t=P$.$.traceArray;
	if(!t.length)return "";
	while(t.length){
		e=t.shift();x=e[1];
		if(!x)x='';
		s=e[2];c='';
		f='#000000';l='#f0f0f0';
		if(s==1){c='[!]';l='#f8f8f0';}
		if(s==2){c='/!\\';f='#e00000';l='#fff0f0';}
	
		r+="<table width='100%' border=0 cellpadding=1 cellspacing=1><tr  bgcolor='"+l+"' valign='top'><td width='30' style='font-size:9px;color:"+f+";'>"+c+"</td><td width='300'><div style='overflow-y:auto;overflow-x:hidden;width:300;height:14;font-size:9px;'><b>"+e[0]+"</b></div></td><td style='font-size:9px;color:"+f+";'>"+x+"</td></tr></table>";
	}
	return r;
},
updateTraceView:function(){
	var e,s,t=P$.$.traceArray;
	if(this.traceView){
		s=this.getTraceHTML();
		if(!s)return;
		e=this.traceView.ownerDocument.createElement('div');
		e.innerHTML=s;
		this.traceView.appendChild(e);
		this.traceView.ownerDocument.parentWindow.scrollBy(0,10000);
	}else return '+';
},
//Control:function(className,initObj,parent,domcontainer)
putTraceView:function(a,to){

	this.traceView=to;
//	this.updateTraceView();
	
},
putPropertiesView:function(a,to){
//	var W=P$.$.sys.win,c=new W.Control('sys.XML',a,a.parent,to);
	this.propertiesView=to;
	
},
putXMLView:function(a,to){
//	var W=P$.$.sys.win,c=new W.Control('sys.XML',a,a.parent,to);
	
	this.XMLView=to;
	
},
updateXMLView:function(a,from){
	this.XMLView.innerHTML='XML View has been updated';
}


},'sys.win');

window.status+=' нормально отработал ';