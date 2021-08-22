$W.regModule($W.mods.sys.win,{
modalBg:0,
modalZ:10,
active:-1,
dragMode:0,
dragSX:0,
dragSY:0,
elements:{syswe1:16, syswe2:-28, syswe3:-28, syswf:0},
errorCallback:0,
resultFrom:0,
reservedParams:/(^w|h|url|Title|reloadOnOk|modalOkOnOk|closeOnOk|callback)/i,
timeout1:0,
popupCallerWindow:0,
popupClosed:0,
//bottomW:W,
nextHWND:1,
_mr_:0,
allwins:{},
modalWait:false,

initWin:function(w){
	$.on('mouseup',  this.mouseUp,  w.d);
	$.on('mousedown',this.mouseDown,w.d);
	$.on('mousemove',this.mouseMove,w.d);
	var e;
  $.on('resize',this.bodyOnResize,window);
  e=$.ce({div:{id:'focuscatcher',any:'more',className:'popframe',style:{position:'relative'}}});
	e=$.ce({div:{id:'Pop_container',
	  style:{position:'absolute',visibility:'hidden',left:0,top:0,zIndex:20000},
  	_:[{input:{id:'Pop_focus',type:'text',onBlur:'sys.windisp.prepareClosePopup',style:{width:10,height:10}}},
  		{div:{id:'Pop_inner',style:{position:'absolute'}}}]}});
	
},
mouseDown:function(e){
	var p,i,topW=0,topZ=0,obj=(ie)?e.srcElement:e.target,m=obj.attributes.dragMode,d=$W.mods.sys.windisp,hwnd;
	if(!m)return;
	m=d.dragMode=m.value;
	w=obj.attributes.hwnd.value;
	d.dragWnd=d.allwins[w];
	d.toFront(w);
	switch(m){
		case "move": d.dragSX=e.screenX-d.dragWnd.x; d.dragSY=e.screenY-d.dragWnd.y; break;
		case "sz_wh":d.dragSX=e.screenX-d.dragWnd.w; d.dragSY=e.screenY-d.dragWnd.h; break;
	}
	return false;
},

mouseUp:function(){
	var d=$W.mods.sys.windisp;
	d.dragMode=0;
	if (d.dragWid){
		var ff=$("syswff"+d.dragWid);
		ff.style.visibility='hidden';
	}
},

mouseMove:function(e){
	var d=$W.mods.sys.windisp,m=d.dragMode;
	if (!m) return;
	var p=d.dragWnd,dx=e.screenX-d.dragSX, dy=e.screenY-d.dragSY;
	switch(d.dragMode) {
		case "move": p.x=dx;p.y=dy;break;
		case "sz_wh":p.w=(dx>300)?dx:300;p.h=(dy>100)?dy:100;  break;
	}
	d.updateSize(p,true);
},

prepareClosePopup:function(){
	if (timeout1) window.clearTimeout(timeout1);
	timeout1=window.setTimeout(W.disp.closePopup,500);
},
closePopup:function(){
	this.timeout1=0;
	var s=$("Pop_container").style;
	s.visibility='hidden';s.left=s.top=0;
	this.popupClosed=true;
},
popMouseOver:function(td){
	td.oldb=td.style.backgroundColor;
	td.oldf=td.style.color;
	td.style.backgroundColor=W.style.ItemBgColorHover;
	td.style.color=W.style.ItemFontColorHover;
},
popMouseOut:function(td){
	td.style.backgroundColor=td.oldb;
	td.style.color=td.oldf;
},
openPopupMenu:function(el,items,useButton){
	var i,id,item,s="",caption,image;
	for (i in items){
		item=items[i];
		if (item.cap=='-'){s+="<tr><td colspan='2' bgcolor='#606060'></td></tr><tr><td colspan='2' bgcolor='#ffffff'></td></tr>";
			continue;
		}
		s+="<tr>";
		if (item.cap.charAt(0)=='?'){s+="<td colspan='2' class='pop_head' style='cursor:default'>"+item.cap.substr(1)+"</td>";continue;}
		if (item.img)s+="<td class='pop_cell'><img src='"+item.img+"'/></td>"; else s+="<td class='pop_cell'>&nbsp;</td>";
		s+="<td class='pop_cell' onMouseOver='W.disp.popMouseOver(this)' onMouseDown='W.disp.popupCallerWindow."+item.cb+"(\""+item.act+"\""+ ((item.cba)?",\""+item.cba+"\"":"")+")' onMouseOut='W.disp.popMouseOut(this)' style='cursor:pointer'>"+item.cap+"</td></tr>";
	}
	s="<table cellpadding='2' cellspacing='0' width='100%'>"+s+"</table>";
	this.openPopupUnder(el,s,useButton);
},
openPopupUnder:function(el,inner,useButton){
	var p=this.popupCallerWindow,x,y,addx,addy=0;
	if (el.screenX){
		x=el.screenX-window.top.screenLeft;
		y=el.screenY-window.top.screenTop; 
	}else{var xy=GetAbsXY(el);
		addx=el.offsetWidth;addy=el.offsetHeight;
		x=xy.x+addx-p.document.body.scrollLeft+p.screenLeft-p.top.screenLeft+p.top.document.body.scrollLeft;
		y=xy.y+addy-p.document.body.scrollTop +p.screenTop -p.top.screenTop +p.top.document.body.scrollTop;
		if (p.name.substring(0,5)=='syswf') addy-=22;
	}
	this.openPopup(x,y,inner,useButton,addy);
},
openPopup:function(x,y,inner,right,mirrorOpenDeltaY){
	var maxy=popupCallerWindow.top.document.body.offsetHeight;
	popupClosed=false;
	if (timeout1) window.clearTimeout(timeout1);
	var pcont=$("Pop_container");
	pcont.style.visibility='hidden';
	var p=$("Pop_inner");
	p.innerHTML=inner;
	if (y+p.offsetHeight>maxy) y=y-p.offsetHeight-mirrorOpenDeltaY;
	pcont.style.top=y;
	if (right) {x-=pcont.offsetWidth;}
	if(x<2)x=2;
	pcont.style.left=x;
	pcont.style.visibility='visible';
	var f=$("Pop_focus");
	f.focus();
},
bodyOnResize:function(){
	return;
	var m,e1,v,b=document.body;
	this.closePopup();
	for (var i in this.stack){
		m=this.stack[i];
		if (m.opened){
			e1=$("syswm"+m.wid);
			v=b.scrollWidth-2;
			e1.style.width=v;
			if (m.autopos) {m.x=(v-m.w)/2;} else {if ((m.x+m.w)>v) {m.x=(v-m.w); }}
			v=b.clientHeight-35;
			e1.style.height=b.scrollHeight-2;
			if (m.autopos) {
				if (m.h>v) {m.h=v; }
				m.y=(v-m.h)/2+b.scrollTop ;
			} else {if ((m.y+m.h-b.scrollTop)>v) {m.y=(v-m.h+b.scrollTop); } if (m.y<b.scrollTop) m.y=b.scrollTop;}
			this.updateSize(m);
		}
	}
},
updateSize:function(p,byuser){
	var el;
	if (byuser)p.autopos=false;
	var f=p.e.w;
	f.style.left=p.x;f.style.top=p.y;
	/*for (var e in this.elements){
		el=$(e+m.wid);
		if (el){
			try {
				el.style.width=m.w+this.elements[e];
				if (e=='syswf') el.style.height=m.h;
			}catch(err){}
		}
	}
	*/
/*	if (this.dragMode){
		var ff=$("syswff"+p.HWND);
		with (ff.style){
			width=wfloat.offsetWidth;
			height=wfloat.offsetHeight;
		}}*/
},
toFront:function(hwnd){
	var p,i,j,w,z,tz=0,w=window.W.disp.allwins[hwnd],s;
	if(!w)return;
	s=w.stackIn;
	st=(w.isTop)?s.topwins:s.wins;
	for (i in st){j=st[i],z=j.zIndex;if(z>tz)tz=z,tj=j;}
	if(tj!=w){tz+=2;w.e.w.style.zIndex= w.zIndex= tz;w.e.tit.innerHTML='z='+tz;}
	return w;
},
getWindowHtml:function(p) {
	var i=p.HWND,xy='';if(p.x){xy+='left:'+p.x+';top:'+p.y;}
	s=(p.url)?"<iframe onFocus='W.disp.toFront(\""+i+"\")' src='' frameborder='0' name='"+i+"_f' id='"+i+":f' width='"+p.w+"' height='"+p.h+"'></iframe>":p.text;
	
	return "<div id='"+i+":w' style='position:absolute; visibility:hid den;z-Index:"+p.zIndex+";"+xy+"'>"
	+"<table id='"+i+":a' border=0 cellpadding=0 cellspacing=0 width="+(p.w+16)+">"
	+"<tr><td><table border=0 width='100%' cellpadding=0 cellspacing=0><tr><td width=13><img src='"+SkinURL+"/sysw_01.gif'></td><td width='100%' background='"+SkinURL+"/sysw_02.gif'>"
	+"<div style='position:absolute;'><img hwnd='"+i+"' dragMode='move' src='"+SkinURL+"/sp.gif' id='"+i+":t' width='"+(p.w-28)+"' height='20' "
	+" style='cursor:move;' onSelectStart='rfalse(event)' onDragStart='return rfalse(event);'></div>"
	+"<div id='"+i+":tr' style='overflow: hidden; height:21; width:"+(p.w-28)+"'><table border=0 width='100%'><tr><td id='"+i+":tit' align='center' nowrap style='font-family:verdana,arial,sans; font-size:12px; font-weight:bold; color:#ffffff;'>"
	+p.title+"</td></tr></table></div></td><td width=15><a href='javascript:;' onClick='W.modalResult(\"cancel\")'><img border='0' src='"+SkinURL+"/sysw_03.gif'></td></tr></table></td></tr>"
	+"<tr><td><table border=0 width='100%' cellpadding=0 cellspacing=0><tr><td background='"+SkinURL+"/sysw_04.gif' width=8><img src='"+SkinURL+"/sp.gif' width='8'></td><td width='100%' align='center' bgcolor='#f0eaec'>"
	+p.HWND+"<br>"+s
	+"</td><td width=8 background='"+SkinURL+"/sysw_06.gif'><img src='"+SkinURL+"/sp.gif' width='8'></td></tr></table></td></tr>"
	+"<tr><td><table border=0 width='100%' cellpadding=0 cellspacing=0><tr><td width=13><img src='"+SkinURL+"/sysw_07.gif'></td><td width='100%' background='"+SkinURL+"/sysw_08.gif'></td><td width=14>"
	+"<img style='cursor:nw-resize;' hwnd='"+i+"' dragMode='sz_wh' onSelectStart='rfalse()' onDragStart='return rfalse(event)' src='"+SkinURL+"/sysw_09.gif'></td></tr></table>"
	+"</td></tr></table></div>";
},
openWin:function(aW,a){
	// p.inside  =1/0 - создавать новое окно внутри текущего window
	// p.isModal =1/0 - окно модальное
  // url,w,h,params,callback,callerWindowName
  // aW - текущее окно из которого вызывается новое
  //   глобальная W указывает на самое первое окно, относительно которого был создан W.disp
  
	alert ('HELLO!!!');
  var hw,url,st,stackIn,d,w=Int(a.w),h=Int(a.h),fr,i,z,mZ;
	window.status=' Wn.testid='+$Wn.testid;
	if (!w)a.w=w=400;
	if (!h)a.h=h=150;
	if(!a.title)a.title='Default title';
	hw="WND:"+(this.nextHWND++);
	stackIn=(a.inside)?$Wn : $W;
	d=stackIn.d.createElement("DIV");
	stackIn.d.getElementById("Windows").appendChild(d);
	if(a.x!=undefined)a.x=Int(a.x),a.y=Int(a.y),a.autopos=0;else a.autopos=1;
	d.innerHTML=this.getWindowHtml(a);
	this.allwins[hw]=a;
	
	if(a.isTop){st=stackIn.topwins;} else {st=stackIn.wins;}
	mZ=(a.isTop)?10000:1;
	for(i in st){z=st[i].zIndex;if(z>mZ)mZ=z;}
	$Wn.zIndex=mZ+2;
	st[hw]=$Wn;
	
	if(a.isModal){
		d=stackIn.modalFader;
		if(!d){
			d=stackIn.modalFader=stackIn.doc.createElement("DIV");
			d.id="modalFader";
			stackIn.doc.getElementById("Windows").appendChild(d);
			$.let(d.style,{backgroundColor:"#7D87A0",opacity:'0.3',filter:'alpha(opacity=30)',position:'absolute',top:0,left:0,width:800,height:500});
		}
		d.style.zIndex=mZ+1;
	}

	function dd(i){return stackIn.doc.getElementById(hw+':'+i);}
	$Wn.e={w:dd('w'),a:dd('a'),i:dd('f'),t:dd('t'),tit:dd('tit')};
	$Wn.e.tit.innerHTML='z='+$Wn.zIndex;
	
	$.let($Wn.e.w.style,{zIndex:p.zIndex,visibility:'visible'});
	fr=p.e.i.contentWindow.document;
	
	url=a.url;
	if (url){
		s=(url.indexOf ("?")!=-1)? "&":"?";
		if(!a.params)a.params={};
		if(a.isModal)a.params.call='modal';
		for (var k in a.params){
		if (!k.match(this.reservedParams)){url+=s+k+"="+a.params[k]; s="&";}}
		fr.open();
		fr.write(W.strWait);
		fr.close();
		p.e.i.src=url;
	}
	this.bodyOnResize();
	return p.HWND;
},
setTitle:function(s){
	if (this.activeWnd){
		var m=this.stack[this.active];
		$('syst'+m.wid).innerHTML=s;
	}
},
setSize:function(w,h){
	if (this.active!=-1){
		var v,b=document.body,m=this.stack[this.active];
		m.w=w;
		m.h=h;
		if (m.autopos){
			v=b.scrollWidth-2;
			m.x=(v-m.w)/2;
			v=b.clientHeight-10;
			if (m.h>(v-30)) {m.h=v-30;}
			m.y=(v-m.h)/2;
			m.y+=b.scrollTop ;
		}
		this.updateSize(m.wid);
	}
},
modalResult:function(mr){
	if (this.modalWait){
//		alert ('Too fast twice modal result calling');
		return;
	}
	_mr_=mr;
	this.modalWait=true;
	var m=this.stack[this.active];
	if (m){
		$("syswm"+m.wid).style.visibility='hidden';
		$("syswfl"+m.wid).style.visibility='hidden';
	}
	window.setTimeout("W.disp._modalResult()",300);
},
_hideit:function(){
	var m=this.stack[this.active];
	if (!m) return;
	m.opened=false;
	$("syswm"+m.wid).style.visibility='hidden';
	$("syswfl"+m.wid).style.visibility='hidden';
},
_modalResult:function() {
	var s,i,m,aw,mw,mr=_mr_,cancel=false;
	if(typeof(mr)=='boolean')if(mr==false)cancel=true;
	if(typeof(mr)=='string')if(mr=='cancel')cancel=true;
	this.closePopup();
	this._hideit();
	this.modalWait=false;
	this.errorCallback=false;
	if (this.active==-1){ return; }
	m=this.stack[this.active];
	this.resultFrom=m;
	if (m.opened){
		m.opened=false;
		$("syswm"+m.wid).style.visibility='hidden';
		$("syswfl"+m.wid).style.visibility='hidden';
	}

	this.active=-1;
	for (i in this.stack){mw=this.stack[i];if(mw.opened){this.active=i; aw=mw;}}
	if (this.active!=-1){
		$("syswm"+aw.wid).style.visibility='visible';
		$("syswfl"+aw.wid).style.visibility='visible';
	}

	if (this.active>0){
		var prevwid=this.stack[this.active-1].wid;
		var o=$("syswfl"+prevwid);
		if(o)o.style.visibility='visible';
	}

	if (!cancel){
		if (m.callback!=undefined){
			if (typeof(m.callback)!='string')  {
				alert ('Error in OpenDialog parameters: Callback should be a string but the '+typeof(m.callback)+' is');
				return;
			}
			if (m.callerWindowName){
				s=m.callerWindowName+"."+m.callback+"(mr);";
				try {eval(s);}catch(e){alert ('Cant execute callback function\n'+s);}
			}
			return;
		}

		if (m.params){
			if ((m.params.reloadOnOk)&&(!cancel)){
				if (m.callerWindowName){
					s=m.callerWindowName+".location.reload();";
					try{eval (s); return; } catch(e){ alert ('Cant reload opener\n'+s);}
				}
			}
			if ((m.params.closeOnOk)&&(mr=='ok')) { this.modalResult(false);}
			if ((m.params.modalOkOnOk)&&(mr=='ok')) {this.modalResult('ok');}
		}
	}
}
});
