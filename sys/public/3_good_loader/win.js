P$.$.regModule('sys.win',{
iii:Math.round(Math.random()*200),
modalBg:0,
modalZ:10,
active:-1,
dragMode:0,
dragWnd:{},
dragSX:0,
dragSY:0,
errorCallback:0,
resultFrom:0,
reservedParams:/(^w|h|url|Title|reloadOnOk|modalOkOnOk|closeOnOk|callback)/i,
timeout1:0,
popupCallerWindow:0,
popupClosed:0,
nextId:1,
_mr_:0,
allwins:{},
modalWait:false,
inited:0,
nparams:'x,y,w,h,opacity'.split(','),

Win:function(a){
	P$.assign(this,a);
	this.prototype={
	className:'sys.win.Win',
	show:function(){this.visible=true;w.e.w.style.visibility='visible'},
	hide:function(){this.visible=false;w.e.w.style.visibility='hidden'}
	};
},

animate:function(tw,w,p){
	var i,j,n,a,b,c,t,W=P$.$.sys.win;
	if(tw._stopT==undefined){ 
		var d=tw.duration||500;//default duration
		if(tw._startT==undefined)tw._startT=P$.$.time;
		tw._stopT=tw._startT+d;
		tw._tweenStart=W.getWinParams(w);
		for(i in W.nparams){ 
			n=W.nparams[i];
			a=tw[n];
			if(typeof a=='string'){ 
				b=a.charAt(0);
				if((b=='+')||(b=='-')||(b=='*'))a=tw._tweenStart[n]+a;
				tw[n]=eval(a);
			}
		}
	  tw._tweenEnd=W.getWinParams(tw);
	}
	
	t=(P$.$.time-tw._startT)/(tw._stopT-tw._startT);
	if(t>1)t=1;
	for(i in W.nparams){ 
		n=W.nparams[i];
		b=tw._tweenEnd[n];
		if(b!=undefined){
			a=tw._tweenStart[n];
			w[n]=this.easingEquation(t,a,b-a);
		}
	}
	W.updateWin(w);
	if(t!=1)return '+'; // + continue tween
	else{
		if(tw.title){w.e.title.innerHTML=tw.title;}
	}
},
easingEquation:function(t,b,c){
	return Math.round(c/2*(Math.sin(Math.PI*(t-0.5))+1)+b);
},

/**
 tmpl - template name
 a - args uses in template {..}
 p - target element id
 d - document
*/
ceTemplate:function(tmpl,a,p,d){
	a.SkinURL=SkinURL;
	var i,e,he,t=P$.$.templates[tmpl];
	if(!t){alert('Template '+tmpl+' not found');return;}
	P$.assign(a,t.p,1);
	he=this.ce(t.l,p,d,a,a.id);
	for(i in t.e)e=t.e[i],a.e[e]=P$.find(a.id+":"+e,d);
	a.tmplName=tmpl;
	a.tmpl=t;
	if(a.onlayout)a.onlayout(a);
	return e;
},
// c - elements в виде дерева, в котором '_': вложенное дерево '$': html
// t - куда прикреплять новые элементы, если пусто то body
// d - document в котором создавать элементы
// ar - используется при рекурсии
// id - ид верхнего уровня, потом к тэгу _id прибавляется этот ид
ce:function(e,t,d,ar,id){
	if(!ar)ar={};
	if(!d)d=P$.d;
	var c,g,a,k,s,v,z,x,r=function(g){g=g+'';return g.replace(P$.REargs,function(j,i){with(ar){try{return eval('('+i+')');}catch(x){return "{"+i+"}";}}})};
	
	for(g in e)break;
	a=e[g];
	c=d.createElement(g);
	if(!t)t=d.body; else if(typeof t=="string")t=P$.find(t,d);
	for(k in a){
		s=a[k];
		if(k=='_'){for(v in s)this.ce(s[v],c,d,ar,id)}
		else if(k=='_id')e.setAttribute('id',id+':'+s);
		else if(k=='$')c.innerHTML=r(a[k]);
		else if((k=='style') && (typeof s=='object')){
			for(v in s){c.style[v]=r(s[v]);}
		}
		else {c.setAttribute(k,r(a[k]));}
	}
	t.appendChild(c);
	return c;
},

onUsing:function(params,ldr,p){
	P$.trace('Вызван sys.win.onUsing()','Источник: '+ldr.name);
	},
	// вызывается после того как все модули, от которых зависит данный модуль, загружены
initModule:function(){
	P$.trace('Вызван sys.win.initModule()');

},

// p - P$ данного окна из которого вызывается инициализация
// pp - повтор
//   p.__dispid - уникальный код
//   p.d - document окна 
//   p.w - window
//   p.$ - самый нижний P$, в который загружаются все модули
initWin:function(args,w,p){
	var wf,e,h,c,W=this;
	p.on('mouseup',  W.mouseUp,  p.d);
	p.on('mousedown',W.mouseDown,p.d);
	p.on('mousemove',W.mouseMove,p.d);
	
//	d.w.onselectstart=d.w.ondragstart=P$.no;
	
	w=p.Wn=new W.Win({P$:p,wins:{},topwins:{},testid:Math.round(Math.random()*1000)});
	try{
		h=p.w.frameElement.attributes.hwnd.nodeValue;
		W.ce({div:{id:'W',hwnd:h}},0,p.d);
		wf=W.allwins[h];
		if(wf)p.assign(wf,w);else W.allwins[h]=wf=w;
		p.Wn=wf
		//alert('Source '+h+'\n'+P$.dump(P$.D.W.allwins[h]));
	}catch(e){}
	
	W.ce({div:{id:'WindowsContainer',$:"P$.testid="+w.testid}},0,p.d);
	
	if(!W.inited){
		W.inited=1;
		P$.on('resize', W.bodyResize, p.w);
		W.ce({div:{id:'PopContainer',
		style:{position:'absolute',visibility:'hidden',left:0,top:0,zIndex:20000},
		_:[{input:{id:'PopFocus',type:'text',onBlur:'sys.win.prepareClosePopup',style:{width:10,height:10}}},
			{div:{id:'PopInner',style:{position:'absolute'}}}]}});
	}
},
bodyResize:function(){
	window.status='+';
	var W=P$.$.sys.win;
	if(!W.resizeMode){
		W.resizeMode=1;
		P$.run('sys.win.bodyResizeUp');
	}
},
bodyResizeUp:function(a,z,p){
	var s,w,b,f,i,W=this;
	this.resizeMode=0;
	for(i in W.allwins){
		w=W.allwins[i];
		f=w.stackIn.modalFader;
		if(w.visible && w.autopos)W.updateWin(w);
		
		if(w.isModal && f){
			s=f.style;
			if(s.visibility=='visible')b=w.stackIn.P$.d.body,s.width=b.scrollWidth-2,s.height=b.scrollHeight-2;
		}
	}
},
mouseDown:function(e,v){
	var p,i,topW=0,topZ=0,obj=P$.eventObj(e),m=obj.attributes.dragMode,h,W=P$.$.sys.win;
	h=obj.attributes.hwnd;
	if(!h){h=P$.find('W',obj.ownerDocument);if(h)h=h.attributes.hwnd}
	if(h)h=h.value;

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
	return true; // отменить bubbling
},

mouseUp:function(){
	P$.$.sys.win.dragMode=0;
},

mouseMove:function(e){
	var W=P$.$.sys.win,m=W.dragMode,w=W.dragWnd,dx=e.screenX-W.dragSX,dy=e.screenY-W.dragSY;
	if (!m) return;
	switch(m) {
		case "move": w.x=dx;w.y=dy;break;
		case "sz_wh":w.w=dx;w.h=dy;break;
	}
	W.updateWin(w,true);
},
toFront:function(hw){
	var p,i,j,z,tj,tz=0,w=P$.$.sys.win.allwins[hw],s=w.stackIn;
	if(!w)return;
	st=(w.isTop)?s.topwins:s.wins;
	for (i in st){j=st[i],z=j.zIndex;if(z>tz)tz=z,tj=j;}
	if(tj!=w){tz+=2;w.e.w.style.zIndex= w.zIndex= tz;w.e.title.innerHTML='z='+tz;}
	return w;
},

getWinParams:function(w){return{w:w.w,h:w.h,x:w.x,y:w.y,opacity:w.opacity}},
setWinParams:function(a,w,p){if(!w)w=p.Wn;P$.run('sys.win.animate',a,w,p);},
updateWin:function(w,byuser){
	if(byuser)w.autopos=false;
	if(w.autopos){ 
		this.setCenteredXY(w,w.stackIn.P$.d);	
	}
	if(w.onlayout){
		w.onlayout(byuser);
	}
},
loadWin:function(a,w,p){
	var u=w._url||w.url;
	if(w._framedoc){
		with(w._framedoc){open();write('Please wait...');close();}
		w.e.iframe.style.visibility='visible';
		if(u)w.e.iframe.contentWindow.location.replace(u);
	}
},
setCenteredXY:function(w,d){
	var b=document.body;
	w.x=(b.clientWidth-w.w)/2+b.scrollLeft;
	w.y=(b.clientHeight-w.h)/2+b.scrollTop;
},
/**
	params
	.inside  =1/0 - создавать новое окно внутри текущего window
	.isModal =1/0 - окно модальное
	.isTop   =1/0 - наверху
  .noWait =1 - показывать сразу же
  .url,w,h,params,callback
  .template
  .closeOnBlur
  .closeOnLeave
  .posAtElement = 1-below 2-right from srce
  .posAtClick
  .static =1-pos after srce
  
	srce - element, который вызвал окно
  p - контекстный P$ (диспетчер окна, откуда произошел запуск)
	
 */
openWin:function(params,srce,p){
	var W=this,w=new W.Win(params),s,hw,u,st,std,stackIn,dv,df,fr,i,z,mZ,a;
	w.template=w.template||'standardWindow';
	w.w=Int(w.w)||400,w.h=Int(w.h)||150;
	w.id="WND:"+(W.nextId++);
	w.stackIn=(w.inside)?p.Wn : P$.$.Wn;
	w.autopos=1;
	w.openerp=p;
	w.visible=1;
	if(params.fadeIn){w.opacity=0;}
	if(params.posAtElement){
		a=P$.absxy(srce);
		w.x=a.x;w.y=a.y;
	}
	if(w.x!=undefined)w.x=Int(w.x),w.y=Int(w.y),w.autopos=0;
	else W.setCenteredXY(w,p.d);
	
	W.allwins[w.id]=w;
	st=(w.isTop)?w.stackIn.topwins : w.stackIn.wins;
	mZ=(w.isTop)?10000:1;
	for(i in st){z=st[i].zIndex;if(z>mZ)mZ=z;}
	w.zIndex=mZ+2;
	st[w.id]=w;
	std=w.stackIn.P$.d;
	
	if(w.isModal){
		df=w.stackIn.modalFader;
		if(!df)df=w.stackIn.modalFader=W.ce({'div':{id:'modalFader',style:{backgroundColor:"#7D87A0",opacity:'0.3',filter:'alpha(opacity=30)',position:'absolute',top:0,left:0,width:800,height:500}
			}},"WindowsContainer",std);
		df.style.zIndex=mZ+1;
		df.style.visibility='visible';
	}

	
	w.e={};
	if(w.url){w.text="<iframe hwnd='"+w.id+"' src='' style='visibility:hidden' frameborder='0' name='"+w.id+"_f' id='"+w.id+":iframe' width='"+w.w+"' height='"+w.h+"'>Your browser not support IFRAMEs</iframe>";}
	dv=W.ceTemplate(w.template,w,"WindowsContainer",std);
	if(dv){
	w.e.title.innerHTML='z='+w.zIndex;
	w.e.w.ondragstart=P$.no;
	P$.assign(w.e.w.style,{zIndex:w.zIndex,visibility:'visible'});
	
	u=w.url;
	if (u){
		w._framedoc=w.e.iframe.contentWindow.document;
		s=(u.indexOf ("?")!=-1)? "&":"?";
		if(!w.params)w.params={};
		w.params.rnd=Math.random();
		if(w.isModal)w.params.call='modal';
		for (var k in w.params){if (!k.match(W.reservedParams)){u+=s+k+"="+w.params[k]; s="&";}}
		w._url=u;
	}

	if(params.fadeIn){
		p.run('sys.win.animate',{start:0,duration:300, opacity:'100',x:'-50',y:'-50',w:'+100',h:'+100'},w);
		p.run('sys.win.animate',{start:300,duration:100,x:'+5',y:'+5',w:'-10',h:'-10',next:['sys.win.loadWin']},w);
	}else{
		p.run('sys.win.loadWin',0,w);
	}
	
	W.bodyResizeUp();
	if(srce)srce.Wn=w;
	return w;
	}
},

modalResult:function(a,w,p){
	var q,f,W=this,w=(w==undefined)?p.Wn:(typeof w=='string')?W.allwins[w]:w;
	if(!w||!w.e)return;
	f=w.e.iframe;
	if(f)f.parentNode.removeChild(f);
	w.e.text.innerHTML='';
	q=['sys.win._closing',{modalResult:a},w,p];
	if(w.fadeOut&&!w.tool){
		w.autopos=0;
		P$.run('sys.win.animate',{start:200,duration:100,opacity:0},w,p);
		P$.run('sys.win.animate',{start:0,duration:300,x:'+10',w:'-20',y:'+10',h:'-20',next:q},w,p);
	}else P$.run.apply(P$,q);
},

_closing:function(a,w,p){
	var W=this,ow;
	if(w.tool){w.e.w.style.visibility='hidden';return;}
	
	w.e.w.parentNode.removeChild(w.e.w);
	if(w.openerp){ow=w.openerp.Wn;w.openerp.w.focus();}
	if(w.isModal) {
		if(ow){
			// todo: если поверх модального будет открыто немодальное окно, 
			//   а в нем модальное, то закрытие последнего приведет к исчезновению блокера
			if(ow.isModal){
				w.stackIn.modalFader.style.zIndex=ow.zIndex-1;
				w.stackIn.modalFader.style.visibility='visible';
			}else{
				w.stackIn.modalFader.style.visibility='hidden';
			}
		}
	}
	
	if(a.modalResult=='ok'){
		if(w.reloadOnOk){w.opener.w.location.reload();}
		if(w.closeOnOk)W.modalResult('close',w.opener.Wn,p);
		if(w.modalOkOnOk)W.modalResult('ok',w.opener.Wn,p);
		return;
	}
	if(w.callback!=undefined)w.callback(a);
	if(w.isTop)delete(w.stackIn.topwins[w.id]);else delete(w.stackIn.wins[w.id]);
	delete W.allwins[w.id];
},










prepareClosePopup:function(){
	if (timeout1) window.clearTimeout(timeout1);
	timeout1=window.setTimeout(W.disp.closePopup,500);
},
closePopup:function(){
	this.timeout1=0;
	var s=P$.find("Pop_container").style;
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
openPopupMenu:function(el,items,opts){
	var i,id,item,s="",caption,image;
	for (i in items){
		item=items[i];
		if (item.cap=='-'){s+="<tr><td colspan='2' bgcolor='#606060'></td></tr><tr><td colspan='2' bgcolor='#ffffff'></td></tr>";
			continue;
		}
		s+="<tr>";
		if (item.cap.charAt(0)=='?'){s+="<td colspan='2' class='pop_head' style='cursor:default'>"+item.cap.substr(1)+"</td>";continue;}
		if (item.img)s+="<td class='pop_cell'><img src='"+item.img+"'/></td>"; else s+="<td class='pop_cell'>&nbsp;</td>";
		s+="<td class='pop_cell' onMouseOver='P$.popMouseOver(this)' onMouseDown='P$.popupCallerWindow."+item.cb+"(\""+item.act+"\""+ ((item.cba)?",\""+item.cba+"\"":"")+")' onMouseOut='W.disp.popMouseOut(this)' style='cursor:pointer'>"+item.cap+"</td></tr>";
	}
	s="<table cellpadding='2' cellspacing='0' width='100%'>"+s+"</table>";
	this.openPopupUnder(el,s,useButton);
},

openPopupUnder:function(e,inner,opts){
/*
	var p=this.popupCallerWindow,x,y,addx,addy=0;
	if (el.screenX){
		x=el.screenX-window.top.screenLeft;
		y=el.screenY-window.top.screenTop; 
	}else{var xy=P$.absxy(el);
		addx=el.offsetWidth;addy=el.offsetHeight;
		x=xy.x+addx-p.document.body.scrollLeft+p.screenLeft-p.top.screenLeft+p.top.document.body.scrollLeft;
		y=xy.y+addy-p.document.body.scrollTop +p.screenTop -p.top.screenTop +p.top.document.body.scrollTop;
	}
	*/
	var x,y,p;
	if(e.x)p=e; else{
		opts.srcElement=e;
		p=this.topxy(e);
	}
	x=p.x;y=p.y;

	var maxy=P$.$.d.body.offsetHeight;
	popupClosed=false;
	if (timeout1) window.clearTimeout(timeout1);
	var pcont=P$.$.find("Pop_container");
	pcont.style.visibility='hidden';
	var p=P$.$.find("Pop_inner");
	p.innerHTML=inner;
	if (y+p.offsetHeight>maxy) y=y-p.offsetHeight-mirrorOpenDeltaY;
	pcont.style.top=y;
	if (right) {x-=pcont.offsetWidth;}
	if(x<2)x=2;
	pcont.style.left=x;
	pcont.style.visibility='visible';
	var f=P$.find("Pop_focus");
	f.focus();
}



},'sys.templates,sys.buttons'
);

