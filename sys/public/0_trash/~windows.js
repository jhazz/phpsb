W={
testid:Math.round(Math.random()*200),
stackin:0,
topwins:{},
wins:{},
nextZTop:100,
nextZ:1000,
modalFader:0,
disp:false,
doc:document,
win:window,
init:function(){
	var p=this.win,d,pp,s="",fnd=false;
	$.on('keypress',W.onKey);
	$.w("<div style='left:0;top:0' id='Windows'>window#"+this.testid+"</div>");
	while(p!=pp){
		pp=p;p=p.parent;
		if(pp.W&&pp.W.disp){
			d=this.disp=pp.W.disp;
			d.initEvents(this);
			return;
		}
	}
	$.w("<script src='"+sysPubURL+"/windows_ie.js'></script>");
},
onKey:function(e){
	if(ie)e=event;
	if (e.keyCode==27){
		if (!W.disp.popupClosed){W.disp.closePopup();return;}
		W.disp.modalResult("cancel");
	}
},
openPopupUnder:function(el,inner,rightAlign){
	W.disp.popupCallerWindow=window;
	W.disp.openPopupUnder(el,inner,rightAlign);
},
openPopupAt:function (x,y,inner,rightAlign) {
	W.disp.popupCallerWindow=window;
	W.disp.openPopup(x,y,inner,rightAlign);
},
setSize:function(w,h){W.disp.setSize(w,h)},
modalResult:function(mr){W.disp.modalResult(mr);},
setTitle:function(s){W.disp.setTitle(s)},
getCallerWindowPath:function(){
	var cw=window; pw=false, s="";
	while ((cw!=pw)&&(cw)){if (window.top==cw){if (s) s="."+s;s="window.top"+s;}else{if(s){s="."+s;}s=cw.name+s;}pw=cw;cw=cw.parent;}
	return s;
},
openWin:function(p){return this.disp.openWin(this,p)},
setErrorHandler:function(callback) {W.disp.errorCallback=callback; },
raiseError:function(error,details) {if (W.disp.errorCallback) {try{W.disp.errorCallback(error,details);} catch(e){}}}
};

//W.init();
alert('end');

// НИГДЕ НЕ ИСПОЛЬЗУЮ, НО НАДО ПОПРОБОВАТЬ!!!
/*
function GetAbsXYev(ev){
	return (ev.pageX)?[ev.pageX, ev.pageY]
  :[ev.clientX + document.body.scrollLeft + document.documentElement.scrollLeft,ev.clientY +  document.body.scrollTop  + document.documentElement.scrollTop];
}
*/

