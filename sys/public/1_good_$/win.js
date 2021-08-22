// При вызове из модуля disp.js, который подключил win.js
// всегда будет видет $W, который подключил $.D в самом начале, в самом первом окне 
// При нажатии мышкой и прочие функции вызова непосредственно из окна должны искать 
// $W через $.Dn.$W, который инициализируется при initWin()

$.D.W={
iii:Math.round(Math.random()*200),
cartn:'sys',
modn:'win',
modalBg:0,
modalZ:10,
active:-1,
dragMode:0,
dragWnd:{},
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
inited:0,

ceTemplate:function(tmpl,a,p,d){
	a.SkinURL=SkinURL;
	var i,e,he,t=$.D.templates[tmpl];
	if(!t){alert('Template '+tmpl+' not found');return;}
	$.let(a,t.p,1);
	he=this.ce(t.l,p,d,a,a.id);
	for(i in t.e)e=t.e[i],a.e[e]=$(a.id+":"+e,d);
	a.tmplName=tmpl;
	a.tmpl=t;
	if(a.onlayout)a.onlayout(a);
	return e;
},
ce:function(i,p,d,ar,id){
	if(!ar)ar={};
	if(!d)d=document;
	var j,t,a,k,s,v,z,e,r=function(g){g=g+'';return g.replace($.REargs,function(j,i){with(ar){return eval('('+i+')');}})};
	
	for(t in i)break;a=i[t];
	e=d.createElement(t);
	if(!p)p=d.body; else if(typeof p=="string")p=$(p,d);
	for(k in a){
		s=a[k];
		if(k=='_'){for(v in s)this.ce(s[v],e,d,ar,id)}
		else if(k=='_id')e.setAttribute('id',id+':'+s);
		else if(k=='$')e.innerHTML=r(a[k]);
		else if((k=='style') && (typeof s=='object')){ 
			for(v in s){e.style[v]=r(s[v]);}
		}
		else {e.setAttribute(k,r(a[k]));}
	}
	p.appendChild(e);
	return e;
},

// d - $.Dn данного окна
initWin:function(args,d){
	var wf,e,w,h,W=$.D.W;
	$.on('mouseup',  W.mouseUp,  d.d);
	$.on('mousedown',W.mouseDown,d.d);
	$.on('mousemove',W.mouseMove,d.d);
	
	w=d.Wn={wins:{},topwins:{},testid:Math.round(Math.random()*200)};
	try{
		h=d.w.frameElement.attributes.hwnd.nodeValue; 
		W.ce({div:{id:'W',hwnd:h,$:h}},0,d.d);
		wf=$.D.W.allwins[h];
		if(wf)$.let(wf,w);else $.D.W.allwins[h]=wf=w;
		d.Wn=wf
		//alert('Source '+h+'\n'+$.dump($.D.W.allwins[h]));
		
	}catch(e){}
	
	$('Windows',d.d).innerHTML="$Wn.testid="+w.testid;
	if(!W.inited){
		W.inited=1;
	  $.on('resize',this.bodyOnResize,window.top);
		W.ce({div:{id:'Pop_container',
	  	style:{position:'absolute',visibility:'hidden',left:0,top:0,zIndex:20000},
  		_:[{input:{id:'Pop_focus',type:'text',onBlur:'sys.win.prepareClosePopup',style:{width:10,height:10}}},
  		{div:{id:'Pop_inner',style:{position:'absolute'}}}]}});
	}
},
mouseDown:function(e,v){
	var p,i,topW=0,topZ=0,obj=$.eventObj(e),m=obj.attributes.dragMode,h,W=$.D.W;
	h=obj.attributes.hwnd;
	if(!h){h=$('W',obj.ownerDocument);if(h)h=h.attributes.hwnd}
	if(h)h=h.value

	if(m){
		if(e.preventDefault)e.preventDefault();
		m=W.dragMode=m.value;
		W.dragWnd=W.allwins[h];
		W.toFront(h);
		switch(m){
		case "move": W.dragSX=e.screenX-W.dragWnd.x; W.dragSY=e.screenY-W.dragWnd.y; break;
		case "sz_wh":W.dragSX=e.screenX-W.dragWnd.w; W.dragSY=e.screenY-W.dragWnd.h; break;}
	}else{
		if(h)W.toFront(h);
	}
	return false; // отменить bubbling
},

mouseUp:function(){
	//var ff,hw=$W.dragWnd.HWND;
	$.D.W.dragMode=0;
	//$.off('mousemove')
//	if (h){ff=$("syswff"+h);ff.style.visibility='hidden';}
},

mouseMove:function(e){
	var W=$.D.W,m=W.dragMode,w=W.dragWnd,dx=e.screenX-W.dragSX,dy=e.screenY-W.dragSY;
	if (!m) return;
	switch(m) {
		case "move": w.x=dx;w.y=dy;break;
		case "sz_wh":w.w=dx;w.h=dy;break;
	}
	W.updateSize(w,true);
},
toFront:function(hw){
	var p,i,j,z,tj,tz=0,w=$.D.W.allwins[hw],s;
	if(!w)return;
	s=w.stackIn.Wn;
	st=(w.isTop)?s.topwins:s.wins;
	for (i in st){j=st[i],z=j.zIndex;if(z>tz)tz=z,tj=j;}
	if(tj!=w){tz+=2;w.e.w.style.zIndex= w.zIndex= tz;w.e.title.innerHTML='z='+tz;}
	return w;
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
	}else{var xy=$.absxy(el);
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
updateSize:function(w,byuser){
	if (byuser)w.autopos=false;
	if(w.onlayout){w.onlayout(w,byuser);return;}
	
	
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


openWin:function(a){
	// inside  =1/0 - создавать новое окно внутри текущего window
	// isModal =1/0 - окно модальное
  // url,w,h,params,callback,callerWindowName
  
  var s,dn=a.Dn,hw,u,st,stackIn,dv,df,w=Int(a.w),h=Int(a.h),fr,i,z,mZ,W=$.D.W;
	if (!w)a.w=w=400;
	if (!h)a.h=h=150;
	hw=a.id="WND:"+(W.nextHWND++);
	a.stackIn=(a.inside)?dn : $.D;
	a.autopos=1;
	if(a.x!=undefined)a.x=Int(a.x),a.y=Int(a.y),a.autopos=0;
	
	W.allwins[hw]=a;
	
	st=(a.isTop)?a.stackIn.Wn.topwins : a.stackIn.Wn.wins;
	mZ=(a.isTop)?10000:1;
	for(i in st){z=st[i].zIndex;if(z>mZ)mZ=z;}
	a.zIndex=mZ+2;
	st[hw]=a;
	
	if(a.isModal){
		df=a.stackIn.modalFader;
		if(!df)df=a.stackIn.modalFader=W.ce({'div':{id:'modalFader',style:{backgroundColor:"#7D87A0",opacity:'0.3',filter:'alpha(opacity=30)',position:'absolute',top:0,left:0,width:800,height:500}
			}},"Windows",a.stackIn.d);
		
		df.style.zIndex=mZ+1;
	}

	a.e={};
	if(a.url){a.text="<iframe hwnd='"+hw+"' src='' frameborder='0' name='"+hw+"_f' id='"+hw+":iframe' width='"+a.w+"' height='"+a.h+"'></iframe>";}
	dv=W.ceTemplate('standardWindow',a,"Windows",a.stackIn.d,a);
	a.e.title.innerHTML='z='+a.zIndex;
	a.e.w.onselectstart=a.e.w.ondragstart=$.no;
	$.let(a.e.w.style,{zIndex:a.zIndex,visibility:'visible'});
	
	u=a.url;
	if (u){
		fr=a.e.iframe.contentWindow.document;
		s=(u.indexOf ("?")!=-1)? "&":"?";
		if(!a.params)a.params={};
		a.params.rnd=Math.random();
		if(a.isModal)a.params.call='modal';
		for (var k in a.params){
		if (!k.match(W.reservedParams)){u+=s+k+"="+a.params[k]; s="&";}}
		fr.open();
		fr.write("Loading:<br>"+u/*W.strWait*/);
		fr.close();
		a.e.iframe.src=u;
	}
	W.bodyOnResize();
	return hw;
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
modalResult:function(mr,d){
	var w;
	if(mr.event){
		var a=$.eventObj(mr.event).attributes.id.value.split('|'),h=a[0];w=$.D.W.allwins[h];
	}else w=d.Wn;

	
//	alert($.dump(mr)+'\n--------\n'+$.dump(w));


/*	if (this.modalWait){
		alert ('Too fast twice modal result calling');
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
	*/
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

};

$.D.regModule($.D.W);
