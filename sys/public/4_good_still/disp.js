//
// PHPSB javacript framework library
// 
// Module: PROCESS DISPATCHER
// 
// 
document._pi_=new Array();
function Int(v){v=parseInt(v);return(v)?v:0;}
function GetAbsXY(e){var p={x:0,y:0};do{p.x+=e.offsetLeft;p.y+=e.offsetTop; e=e.offsetParent;if(e==document.body)break;}while(e.offsetParent!=null)return p;}
ie=!!document.all;

(function(){
var P;
this.P$={
C:{
	DESIGN_MODE:1,
	FRAME_INTERVAL:100, // 50ms=20fps
	INTERVAL_CLOSE_ON_LEAVE:200, // через сколько закрывать попап после того как с него увели мышкой
	SkinURL:SkinURL
},
classes:{},
skins:{},
$:0,
_p_rand:Math.round(Math.random()*1000),
find:function(a,t){if(!t)t=P$.d;return t.getElementById(a);},
findt:function(a,t){if(!t)t=P$.d;return t.getElementsByTagName(a);},
wr:function(s,t){if(!t)t=document;t.write(s)},
trace:function(a,b,c){var t=P$.$.traceArray;if(t.length<300)t.push([a,b,c]);},
on:function(e,h,t){e=e.toLowerCase();if(!t)t=document;if(t.attachEvent)t.attachEvent('on'+e,h);else t.addEventListener(e,h,true);},
assign:function(d,s,nonex){if(!s)return;if(typeof s=='object'){for(var i in s){if(nonex && (d[i]!=undefined))continue;d[i]=s[i]}}},

// o - properties (values and methods)
// xn - extendsClassName
registerClass:function(o,xn){
	var xa,cp,b,t,x,p=this,y,t,v,u,z,i,j,n=o.className,c=function(a){
		for(var i in a)this[i]=a[i];
		arguments.callee.prototype.inherited=function(){var c=arguments.callee.caller._;if(c)return c.apply(this,arguments)}
	};
	cp=c.prototype;
	if(!cp){alert('Bad web browser! Class Function has no .prototype as default')}
	// Copy base class properties
	if(xn){ 
		xa=xn.split(',');
		for(j in xa){ 
			xn=xa[j];x=p.classes[xn];
			if(x){b=x.prototype;for(i in b)cp[i]=b[i];}
		}
	}
	// Overwrite new class properties
	for(i in o){v=o[i];z=cp[i];t=typeof v;u=cp[i]=v;if(t=='function'&&z!=undefined)u._=z;}
	p.classes[n]=c;
},

copy:function(s){
	var r,i,l,t=typeof s;
	if(t=='object'){r=(s.length!=null)?[]:{};for(i in s)if(i!='prototype')r[i]=this.copy(s[i])}else r=s;
	return r
},
// n - class name
// o - object values
// a - argument array for .create()
createObject:function(n,o,a){var r,c=this.classes[n];if(c){r=new c(o);if(!a)a=[];r.create.apply(r,a);return r;}},
extend:function(d,s){
		if(!s)return;
		if(typeof s=='object'){ 
			for(var i in s){ 
				if((typeof s[i]=='function')||(i=='className')){
					if(!d._)d._={};d._[i]=d[i];
				}d[i]=s[i]}}},
no:function(e){if(e){e.returnValue=false;if(e.preventDefault)e.preventDefault();}return false;},
isObjEmpty:function(o){if(!o)return 1;for(var i in o){return 0}return 1},
absxy:function(e){ 
	var p={x:0,y:0};
	while(1){
		p.x+=e.offsetLeft;p.y+=e.offsetTop;if(!e.offsetParent){
			e=e.ownerDocument;e=e.parentWindow||e.defaultView;e=e.frameElement;
			if(!e)break;
		}else e=e.offsetParent;
	}
	return p;
},
int:function(v){v=parseInt(v);return(v)?v:0;},
eventObj:function(e){return (ie)?e.srcElement:e.target;},		
warning:function(text,info,type,buttons,title){window.alert(text+((!!info)?'\n\n'+info:''))},
init:function(){
	var a,r,p=this.w=window;
	this.d=document;
	while(p!=a){a=p;p=p.parent;if(a.P$ && a.P$.$){P=this.$=a.P$.$; this.trace=P.trace; return}}
	P=P$.$=this;
	P.mods={};
	P.traceArray=[];
	P.hTimer=P.time=0;
	P.timerSwitch=function(s){
	var t=P.hTimer,d;
	if(s){
		if(!t){
			P.time=(new Date()).valueOf();
			t=P.w.setInterval(P$.$.enterFrame,P.C.FRAME_INTERVAL);
		}
	}else if(t)P.w.clearInterval(t),t=0;
	P.hTimer=t;
};
	P.on('load',P.enterFrame,this.w);
},
enterFrame:function(){
	P.time=+new Date();
	var k,s=0,m,pa,r,e,fu,st,nx,nxe,j,iii,i=0,z;
	for(k in P.mods){ 
		m=P.mods[k];
		if(!m.busy){
			while(i<m.q.length){
				e=m.q[i];
				pa=e[1]; 
				nx=st=0;
				if((!m.module)||m.busy||(!m.loaded)){i++;continue;}
				fu=m.module[e[0]];
				if(typeof pa=='object')st=Int(pa._startT),nx=pa.next;
		
				if(fu&&st<P.time){ 
					r=fu.call(m.module,e[1],e[2],e[3]);
					if(r!='+'){// '+' - means repeat tween again
						if(r!=undefined)e[2]=r;
						m.q.splice(i--,1);
						nxe=[];
						if(nx){
							P.assign(nxe,nx);
							nxe[2]=nxe[2]||e[2];
							nxe[3]=nxe[3]||e[3];
							P.run.apply(m.module,nxe);
						}
					}
				}
				i++;
			}
		}
		s+=m.q.length;
	}
	P.timerSwitch(s);
},

// run(action:String, args:Object, targetObject:Object, localDispatcher:sys.disp)
run:function(act,a,o,p){
	var a1,o1,p1,i,b,c,d,e,f;
	if(!p)p=P$;
	if(act.pop){
		for(i in act){ 
		b=act[i],c=b[0],d=b[1]||a,e=b[2]||o,f=b[3]||p;
		p.run.call(p,c,d,e,f);
	}return}
	
	var l=act.split('.'),la=l.pop(),modn=l.join('.'),modp=l.join('/'),m=P.mods[modn],s;
	P.timerSwitch(1);
	if(p==undefined)p=P$;
	if(typeof params=='object'){
		if(a.start!=undefined){
			a._startT=P.time+a.start;
		}
	}
	s=PublicURL; if (!l[0])s=SkinURL;
	if(!m){ 
		m=P.mods[modn]={name:modn,module:0,q:[],busy:1};
		m.script=P.include(s+"/"+modp+".js",function(){P$.trace ('loaded '+m.name);loaded=m.name;})
	}
	m.q.push([la,a,o,p]);
},

//
// use(nameOfModule:String, namesOfUsingModules:StringList, actionOnReady:String, optional:Boolean)
use:function(n,u,o,i){
	if(!u)return;
	var y=1,e,a,f,g,l=P$.$.mods[n],v=u.split(",");
	while(v.length){
		e=v.shift();
		P$.run(e+'.using',{},l);
		f=P$.$.mods[e];
		if(i){
			alert('Загрузка опциональных (необязательных) модулей еще не разработана');
			f.optional=1;
			return;
		}
		// Если модуль, от которого зависит данный уже загружен, то ничего не делаем 
		if(!f.loaded){
			// если нижний не загружен, то ставим ему отметку, чтобы он меня потом дернул за .initModule()
			// тогда, КОГДА ВСЕ ОТ КОГО Я ЗАВИСИМ КРОМЕ НЕГО, БУДУТ ЗАГРУЖЕНЫ
			y=0;
			g=f.dependents;
			if(!g)g=f.dependents={};
			g[n]=l; // вектор от нижнего ВВЕРХ ко мне 

			g=l.dependOn;
			if(!g)g=l.dependOn={};
			g[e]=f;// вектор вниз от меня ставим всегда, чтобы знать кто от кого зависит
			if(o){
				a=f.actsOnInit;
				if(!a)a=f.actsOnInit=[];
				a.push(o);
			}
		}
	}
	if(y&&o)P$.run(o);
},

// d-dependOn от каких модулей зависит данный модуль через запятую
regModule:function(n,M,d){
//	alert(this.__dispid+' P$.regModule '+n);
	var i,l,m,e,f,g,q,s,h,a=n.split('.');
	q=M.cartName=a.shift();
	s=M.modName=a.join('.');
	if(!P$.$){alert('Dispatcher has not initialized!')}
	m=P$.$[q];
	if(!m)m=P$.$[q]={};
	l=P$.$.mods[n];
	if(!l){alert(n+' не найден среди модулей');debugger;l=P$.$.mods[n]={name:n,q:[]};}
	
	m[s]=l.module=M;
	l.loaded=1;
	l.busy=0;
	P$.$.use(n,d);
	
	// dependents - зависящие от меня (вверху)
	// dependon   - от кого я зависим (внизу)
	
	// обратная задача - я должен обработать всех, кто зависит от меня (наверху)
	// то есть, если это sys.skins, то я должен проверить все ли хорошо у sys.win и если он уже загрузил
	// sys.buttons то запустить ему sys.win.initModule()
	
	// q-name r-loader of module
	function initModAndDependents(q,r){ //
		var a,c,t,j,b,y=P$.isObjEmpty;
		// смотрим, пуста ли зависимость от нижних
		// загружены ли нижние модули
		if(y(r.dependOn)){
			// если ни от кого я не зависим - запускаю внутренний инициализатор модуля
			if(r.module.initModule) {r.initModuleCalled=1;r.module.initModule();delete r.module.initModule;}
			// он может изменить dependOn, вызвав use
			a=r.actsOnInit;
			if(a){while(a.length){c=a.shift();P$.$.run(c);}}
		}
		// если зависимость осталась и нижние еще не загружены - выходим
		if(!y(r.dependOn)){P$.$.trace('initModAndDependents()',q+' еще не готов');return;}
		
		// смотрю на верхних зависимых от меня
		b=r.dependents;
		if(!y(b)){
			for(j in b){
				t=b[j];// t-loader для q=sys.skins  j="sys.win", t будет {sys.win}
				if(!y(t.dependOn)){ 
					if(t.dependOn[q]){ 
						delete t.dependOn[q];
					}else alert('Странно! зависимость от меня ('+q+') в модуле '+j+' отсутствует');
				}
				initModAndDependents(j,t);
			}
		}
	}
	
	initModAndDependents(n,l);
},
reload:function(n){
	var s,l=P$.$.mods[n];
	if(!l){alert(n+' не найден среди модулей');return;}
	l.loaded=0;l.busy=1;
	s=l.script.src;
	l.script.src='';
	l.script.src=s+'?rnd='+Math.random();
},
include:function(f,c){
	var d=P.d,l=1,s=d.createElement("script"),p=P.findt('head',d)[0];
	s.onload=s.onreadystatechange=function(){
		if(l&&(!this.readyState||this.readyState=="loaded"||this.readyState=="complete")){
			l=0;if(!!c)c();
			this.onreadystatechange=this.onload="";
		}
	}
	p.appendChild(s);
	s.src=f;
	return s;
}
}
})();

P$.init();
P$.on("load",function(){P$.run("sys.win.initWin");},window);
