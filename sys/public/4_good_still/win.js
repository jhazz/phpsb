P$.$.regModule('sys.win',{
REargs:/\{([^}]+)\}/g,
dragMode:0,
dragWnd:{},
drgA:0,
drgB:0,
drgC:0,
drgD:0,
reservedParams:/(^w|h|url|Title|reloadOnOk|modalOkOnOk|closeOnOk|callback)/i,
nextId:1,
allwins:{},
allcomponents:{},
componentClasses:{},
componentClassesGroup:{},
frames:{},
blurable:[],
leavable:[],
focused:0, // handle активного окна
inited:0,
slotId:1,
nparams:'x,y,w,h,opacity'.split(','),


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
	
	w.update();
	if(t!=1)return '+'; // + continue tween
	else{
		if(tw.title){w.le.title.innerHTML=tw.title;}
	}
},
easingEquation:function(t,b,c){
	return Math.round(c/2*(Math.sin(Math.PI*(t-0.5))+1)+b);
},

cet:function(tg,at,l,o,to){
	var s,i,j,c=to.ownerDocument.createElement(tg),W=this;
	if(at){
		for(i in at){
			s=at[i];
			if(i=='style')for(j in s)c.style[j]=s[j];else c.setAttribute(i,s);
		}
	}
	c.innerHTML=W.ce(l,o);
	to.appendChild(c);
	return c;
},

// l - layout
// o - with-object
ce:function(l,o){
	if(!o)o={};
	var x,W=this,s;
	return l.replace(W.REargs,function(j,i){with(o){try{return eval('('+i+')');}catch(x){return "{"+i+"}";}}});
},

using:function(params,ldr,p){P$.trace('Вызван sys.win.onusing()','Источник: '+ldr.name);},











initModule:function(){
	P$.trace('Вызван sys.win.initModule()');
	
	
	
	P$.$.registerClass({
		className:'Component',
		_static:{nextId:1},
		SkinURL:P$.$.C.SkinURL,
		// METHODS
		create:function(owner,c,ca){
			var _=this,W=P$.$.sys.win,i,e,t;
			if(typeof owner=='string')owner=P$.$.allcomponents[owner];
			_.components={},
			_.subscribers={},
			_.le={},
			_.owner=owner;
			if(!_.hw)_.hw=_.className+':'+(_._static.nextId++);
	
			if(!!ca){
				ca.id=_.hw+':container';
				ca.hw=_.hw;
				c=W.cet('div',ca,'',_,c);
			}
			_.domContainer=c;
			
			if(_.skinName){
				_.skin=t=P$.$.skins[_.skinName];
				if(t){
					P$.assign(_,t.p,1);
					c.innerHTML=W.ce(t.l,_);
					if(t.le)for(i in t.le){
						e=t.le[i],_.le[e]=P$.find(_.hw+":"+e,c.ownerDocument);
					}
					
				}
			}
		owner.components[_.hw]=W.allcomponents[_.hw]=_;
	},
	getClientSize:function(){var _=this;return[_.x,_.y,_.w,_.h,0,0]},
	update:function(byuser){
		var pa,_=this,d=_.owner.P$.d,b=d.body;
		if(byuser)_.autopos=0;
		if(_.autopos==1){//center
		pa=_.parent.getClientSize();
			_.x=(pa[2]-_.w)/2+pa[4];
			_.y=(pa[3]-_.h)/2+pa[5];
		}
		if(_.onlayout)_.onlayout(byuser);
	},
	
	free:function(e){
		var i,_=this,W=P$.$.sys.win,c;
		for(i in _.subscribers){if(_.subscribers[i].unsubscribe)_.subscribers[i].unsubscribe(_);}
		for(i in _.components)_.components[i].free();
		if(_.owner)delete(_.owner.components[_.hw]);
		if(_.onfree)_.onfree();
		delete W.allcomponents[_.hw];
		if(_.domContainer)_.domContainer.parentNode.removeChild(_.domContainer);
	},
	// вызывается при уничтожении w или если надо отсоединить от w подписчика на конкретный тип события
	unsubscribe:function(w,et){}
		
	});

	
	
	
P$.$.registerClass({
	className:'Window',
	w:400,h:150,
	autopos:1,
	visible:1,
	skinName:'standardWindow',
	create:function(){
		var P=P$.$,W=P.sys.win,_=this,ow=_.parent,st,it=_.isTop,i,z,mZ,u,s,df,k;

		wc=ow.defaultContainer; // .c - container at bottom 
		_.opacity=(_.fadeIn)?0:100;
		st=ow.wins;if(!st)st=ow.wins={};
		mZ=(it)?100000:1;
		for(i in st){if(st[i].isTop==it){z=st[i].zIndex;if(z>mZ)mZ=z;}}
		_.zIndex=mZ+2;
		_.inherited(ow,wc,{style:{display:'none',position:'absolute',zIndex:_.zIndex}});//,outline:'2px dashed #666'
		if(!_.skin){P$.trace('win.js:Отсутствует skin для окна',_.skinName);return;}
		
		if(_.isModal){
			df=ow.modalFader;
			if(!df)df=ow.modalFader=W.cet("div",
			  {id:'modalFader',style:{backgroundColor:'#7D87A0',opacity:'0.3',filter:'alpha(opacity=30)',position:'absolute',top:0,left:0,width:800,height:500}}
			  ,"",0,wc);
			df.style.zIndex=mZ+1;
			df.style.visibility='visible';
		}
	
		st[_.hw]=W.allwins[_.hw]=_;
		_.domContainer.ondragstart=P.no;
		_.le.title.onselectstart=P.no;
		if(u=_.url){
			_.le.text.innerHTML="<iframe hw='"+_.hw+"' src='' style='border:none;' frameborder='0' name='"+_.hw+"_f' id='"+_.hw+":iframe' width='"+_.w+"' height='"+_.h+"'>Your browser not support tag IFRAME</iframe>"; 
			_.le.iframe=P.find(_.hw+":iframe",ow.P$.d);
			_._framedoc=_.le.iframe.contentWindow.document;
			s=(u.indexOf ("?")!=-1)? "&":"?";
			if(!_.params)_.params={};
			_.params.rnd=Math.random();
			if(_.isModal)_.params.call='modal';
			for (var k in _.params){if (!k.match(W.reservedParams)){u+=s+k+"="+_.params[k]; s="&";}}
			_._url=u;
		}
		P$.assign(_.domContainer.style,{zIndex:_.zIndex,display:'block'});
	},
	parentresize:function(){this.update()},
	update:function(byuser){
		var _=this;
		_.inherited(byuser);
		if(_.isModal){
			var g,h,b,s,f=_.owner.modalFader;
			if(f)s=f.style;
			if(s.visibility=='visible'){
			b=_.owner.P$.d.body;
			s.width=b.scrollWidth-2;
			h=b.scrollHeight-2;
			g=b.clientHeight;
			s.height=(h>g)?h:g;
			}
		}			
	},
	free:function(){
		var _=this,W=P$.$.sys.win;
		if(_.owner)delete(_.owner.wins[_.hw]);
		delete W.allwins[_.hw];
		_.inherited();
	}	
},'Component');
	
},


// p - P$ данного окна из которого вызывается инициализация
// pp - повтор
//   p.__dispid - уникальный код
//   p.d - document окна 
//   p.w - window
//   p.$ - самый первый P$, в который загружаются все модули
initWin:function(a,b,p){
	var wf,e,h,c,w,W=P$.$.sys.win,s;
	p.on('mouseup',  W.mouseUp,  p.d);
	p.on('mousedown',W.mouseDown,p.d);
	p.on('mousemove',W.mouseMove,p.d);
//	d.w.onselectstart=d.w.ondragstart=P$.no;
	try{
		h=p.w.frameElement.attributes.hw.nodeValue;
		w=W.allwins[h];
		if((!w)||w.closing){ 
			// Когда окно уже закрыли, а данные только загрузились. Все бросаем, забываем про этот объект
			// alert('Неизвестное окно '+h+'. Я его не открывал');
			return;
		}
		w.P$=p;
	}catch(e){
		s='frame';
		h=s+':'+(W.nextId++);
		W.frames[h]=w={
			hw:h, 
			components:{},
			P$:p,
		//	wins:{},
			isFrame:1,
			getClientSize:function(){ 
				var _=this,b=_.P$.d.body;
				return[0,0,b.clientWidth,b.clientHeight,b.scrollLeft,b.scrollTop];
			}
		};
	}
	p.Wn=w;
	b=p.d.body;
	w.defaultContainer=W.cet('div',{id:'sys.win.defaultContainer',hw:h},"[sys.win.defaultContainer hw={hw}]",w,b);
	if(!W.inited){
		W.inited=1;
		P$.on('resize', W.bodyResize, window);
	}
	W.setFocus(h);
},
bodyResize:function(){
	var W=P$.$.sys.win;
	if(!W.resizeMode){
		W.resizeMode=1;
		P$.run('sys.win.bodyResizeUp');
	}
},
bodyResizeUp:function(a,z,p){
	var s,w,b,f,i,W=this;
	for(i in W.frames){
		w=W.frames[i];
		W.dispatchEvent({type:'parentresize',dir:2},w);
//		if(w.visible && w.autopos)W.updateWin(w);
	}
	this.resizeMode=0;
},
_closeAllBut:function(b,hw){
	var w,W=P$.$.sys.win;
	while(b.length){
		w=b[b.length-1];
		if(w.hw==hw)return;
		b.pop();
		W.modalResult('close',w);
	}
},
// detectHandle(startElement,anyAttributeName)
// определяет handle окна относительно объекта-события
// и может искать произвольный атрибут относительно события
detectHandle:function(o,b){
	var a,h,v,x,s=o;
	while(o!=null){ 
		a=o.attributes;
		if(a){
			if(!h&&(x=a.hw))h=x.value;
			if(b){if(!v&&(x=a[b]))v=x.value;if(v&&h)break;}else{if(h)break;}
		}
		o=o.parentNode;
	}
	try{if(!h)h=s.ownerDocument.parentWindow.window.frameElement.attributes.hw.value}catch(o){};
	if(!h&&(x=P$.find('sys.win.defaultContainer',s.ownerDocument)))h=x.attributes.hw.value;
	if(b)return[h,v];else return h;
},
mouseDown:function(e,v){
	var r,m,h,W=P$.$.sys.win,y,w;
	y=W.detectHandle(P$.eventObj(e),'dragMode');
	if(y){
		h=y[0];m=y[1];
		//if(r=e.preventDefault)r();
		W.dragMode=m;
		w=W.dragWnd=W.allwins[h];
		switch(m){
		case "move": W.drgA=e.screenX-w.x; W.drgB=e.screenY-w.y; break;
		case "sz_ne":W.drgA=e.screenX-w.w; W.drgB=e.screenY-w.y; W.drgD=w.h; W.drgC=e.screenY;break;
		case "sz_se":W.drgA=e.screenX-w.w; W.drgB=e.screenY-w.h; break;
		}
	}
	if(h)W.focus(h);
	if(W.blurable.length)W._closeAllBut(W.blurable,h);// закрыть все окна кроме того, в котором есть фокус
	return true; // отменить bubbling
},

mouseUp:function(){
	P$.$.sys.win.dragMode=0;
},

mouseMove:function(e){
	var h,p=P$.$,W=p.sys.win,m=W.dragMode,y,w=W.dragWnd,dx=e.screenX-W.drgA,dy=e.screenY-W.drgB,z;
	h=W.detectHandle(P$.eventObj(e));
	
	if(W.leavable.length){
		if(W.timerLeave){window.clearTimeout(W.timerLeave);}
		W.timerLeaveH=h;
		W.timerLeave=window.setTimeout(function(){ 
			window.clearTimeout(W.timerLeave);
			W._closeAllBut(W.leavable,W.timerLeaveH);
		},p.C.INTERVAL_CLOSE_ON_LEAVE||500);
	}
	
	if(h){y=W.allwins[h];if(y&&(z=y.onmousemove))z(e)}
	
	if (!m) return;
	window.status=m+' '+dx;
	switch(m) {
		case "move": w.x=dx;w.y=dy;if(y&&y.dragTarget&&y.ondragover)y.ondragover(w);break;
		case "sz_ne":w.w=dx;w.y=dy; w.h=W.drgC-e.screenY+W.drgD;  break;
		case "sz_se":w.w=dx;w.h=dy;break;
	}
	
	w.update(1)
},
focus:function(hw){
	var it,i,j,z,tj,tz=0,W=P$.$.sys.win,w=W.allwins[hw],s,ws;
	if((!w)||(w.isFrame))return;
	s=w.owner;
	it=w.isTop;
	ws=s.wins;
	for (i in ws){j=ws[i],z=j.zIndex;if((j.isTop==it)&&(z>tz))tz=z,tj=j;}
	if(tj!=w){ 
		tz+=2;
		w.domContainer.style.zIndex= w.zIndex= tz;
	}
	if(W.focused!=hw) W.setFocus(hw);
	return w;
},
setFocus:function(h){
	var b,W=this,f=W.focused,x,w=W.allwins[h];
	
	if(f && f!=h){
		b=W.allwins[f];
		if(b&&b.onblur)b.onblur();
		W.focused=0;
	}
	if(w){h=w.hw;W.focused=h;if(w.onfocus)w.onfocus()}
},
getWinParams:function(w){return{w:w.w,h:w.h,x:w.x,y:w.y,opacity:w.opacity}},
setWinParams:function(a,w,p){if(!w)w=p.Wn;P$.run('sys.win.animate',a,w,p);},
loadWin:function(a,w,p){
	var u=w._url||w.url;
	if(w._framedoc){
		with(w._framedoc){open();write('Please wait...');close();}
		w.le.iframe.style.visibility='visible';
		if(u)try{w.le.iframe.contentWindow.location.replace(u)}catch(e){};
	}
	if(w.callOnLoad){w.callOnLoad(w)};
},
/**
	params
	.inside  =1/0 - создавать новое окно внутри текущего window
	.isModal =1/0 - окно модальное
	.isTop   =1/0 - наверху
  .noWait =1 - показывать сразу же
  .url,w,h,params,callback
  .skinName
  .closeOnBlur
  .closeOnLeave
  .posAtElement = 1-below 2-right from srce
  .posAtClick =1-
  .reusable =1 - не удалять при закрытии
  .callOnResult - функция, вызываемая при закрытии окна
  
	srce - element, который вызвал окно
  p - контекстный P$ (диспетчер окна, откуда произошел запуск)
	
 */

openWin:function(w,srce,p){
	var W=this;
	if(!w.skinName)w.skinName='standardWindow';
	if(!P$.$.skins[w.skinName]){
		P$.trace('sys.win.openWin()','Отсутствует скин '+w.skinName);
		return;
	}

	w.parent=((w.inside)?p.Wn:P$.$.Wn);
	if(!w.parent)return '+';
	w.openerp=p;
	if(w.posAtElement)p.assign(w,p.absxy(srce));
	if(w.x!=undefined)w.x=Int(w.x),w.y=Int(w.y),w.autopos=0;
	if(W.leavable.length)W._closeAllBut(W.leavable,p.Wn.hw);	
	w=P$.$.createObject('Window',w);
	
	w.update();

	if(w.fadeIn&&w.onfadein)w.onfadein();else p.run('sys.win.loadWin',0,w);
	P$.run('sys.win.setButtonState',{buttonName:'close',state:'n',onclick:function(){P$.run("sys.win.modalResult","close",w)}},w);
	if(srce)srce.Wn=w;
	if(w.closeOnBlur)W.blurable.push(w);
	if(w.closeOnLeave)W.leavable.push(w);
	return w;
},

modalResult:function(a,w,p){
	p=p||P$;
	var q,f,g,W=this,w=(w==undefined)?p.Wn:(typeof w=='string')?W.allwins[w]:w;
	if(!w||!w.le||w.closing)return;
	w.closing=1;
	w.mr=a;
	
	if(w.fadeOut&&w.onfadeout)w.onfadeout();else p.run('sys.win.closeWin',0,w);
},

closeWin:function(a,w,p){
	if(w.reusable){w.le.w.style.visibility='hidden';return;}
	var W=this,r,o,s,f;

	o=w.openerp;
	if(o)r=o.Wn;
	if(w.isModal&&r) {
		// todo: если поверх модального будет открыто немодальное окно, 
		//   а в нем модальное, то закрытие последнего приведет к исчезновению блокера
		
		if(r.isModal){
			f=w.owner.modalFader.style;
			f.zIndex=r.zIndex-1;
			f.visibility='visible';
		}else{
			if(f=w.owner.modalFader)f.style.visibility='hidden';
		}
	}
	if(o&&r&&(!r.closing)&&r.le&&r.le.w){r.le.w.focus();W.setFocus(r.hw);}
	if(w.callOnResult)w.callOnResult(w);// user-defined callback

	if(w.mr=='ok'){
		if(w.reloadOnOk){o.w.location.reload();}
		if(w.closeOnOk)W.modalResult('close',r,p);
		if(w.modalOkOnOk)W.modalResult('ok',r,p);
	}
	W.dispatchEvent({type:'eventmodalresult',result:w.mr},w);
	w.free();
},
// a.type - event type на который подписывается a.subscriberControl
//   a.subscriberControl - контрол, который подписывается на событие
//   a.subscriberMethod - метод, который вызывается при событии 
// w - источник событий
//
// a.subscriberControl.subscribedFor(a) вызывается по завершении подписки (может быть асинхронной)
// a.subscriberControl.unsubscribe(w) вызывается при удалении объекта
subscribe:function(a,w,p){
	w=(!w)?p.Wn:(typeof w=='string')?(W.allwins[w]||W.allcomponents[w]):w;
	if(!w.subscribers)w.subscribers={};
	var t=a.subscriber,m=a.subscriberMethod,et=a.type,s=w.subscribers[et];
	if(!s)s=w.subscribers[et]=[];
	a.source=w;
	s[t.hw]=a;
	if(t.subscribedFor)t.subscribedFor(a);
},
// e - Event e.type - строковое обозначение типа события
// w - целевой контрол
// e.dir - направление распространения 1-родителю 2-детям 4-подписчикам
dispatchEvent:function(e,w){ 
	var i,et=e.type,k,W=this,s,ss,ea,er,d=e.dir;
	ss=w.subscribers;
	if(ss&&ss[et]&&(d&4)){ 
		s=ss[et];// если есть список подписчиков s на то, что событие e придет к контролу w
		for(k in s){ // идем по подписчикам
			try{s[k](e,w);}catch(er){alert('Ошибка обработчика подписавшегося на событие '+et)}
		} 
	}
	if(d){
		if(d&1)e.dir&=1,W.dispatchEvent(e,w.parent);// bubbling - избегаю циклов если вдруг захотели во все стороны пустить событие
		else if(d&2){// drilling
			e.dir=d=d&2;
			for(i in w.components)W.dispatchEvent(e,w.components[i]);
		}
	}
	if(w[et]) w[et](e);
	if(w['on'+et])w['on'+et](e);
	//return 1; // on не вызывается если уже 1
},
// a.bn - имя кнопки в макете
// w - окно или имя окна
setButtonState:function(a,w,p){
	if(!w.buttons)return;
	var W=this,w=(!w)?p.Wn:(typeof w=='string')?W.allwins[w]:w,b=w.buttons[a.buttonName];
	if(!b){P$.trace('Button '+a.buttonName+' not exists in layout template of window '+w.hw);return}
	b.onclick=a.onclick;
	
	if(!b.state){b.parent=w;P$.run('sys.button.put',b,w,w.owner.P$.d.getElementById(w.hw+':'+b.slot));}
	else {
		if(b.state!=a.state){
			P$.run('sys.button.changeState',b);
		}
	}
}
},'sys.baseSkins,sys.button'
);

// OnDragOver(Sender, Source: TObject; X, Y: Integer; State: TDragState; var Accept: Boolean) of object;
// Occurs when the user drags an object over a control.

// OnDragDrop(Sender, Source: TObject; X, Y: Integer) of object;
// Occurs when the user drops an object being dragged.

// OnEndDrag(Sender, Target: TObject; X, Y: Integer) of object;
// Occurs when the dragging of an object ends, either by dropping the object or by canceling the dragging.





// OnStartDock(Sender: TObject; var DragObject: TDragDockObject) of object;
// Occurs when the user begins to drag a control with a DragKind of dkDock.


// OnEndDock(Sender, Target: TObject; X, Y: Integer) of object;
// Occurs when the dragging of an object ends, either by docking the object or by canceling the dragging.
// The Sender is the object being dragged, Target is the object Sender is dragged to, and X and Y are screen
// coordinates in pixels.
// The OnEndDrag event is received by Sender. If the dragged object was dropped or docked and accepted
// by a control, the Target parameter of the OnEndDrag event is set to the object that accepted the sender.
// If the object was not dropped successfully, the value of Target is nil

// OnDockOver(Sender: TObject; Source: TDragDockObject; X, Y: Integer; State: TDragState; var Accept: Boolean) of object;
// Occurs when another control is dragged over the control.

// OnDockDrop(Sender: TObject; Source: TDragDockObject;  X, Y: Integer) of object;
// Occurs when another control is docked to the control.
