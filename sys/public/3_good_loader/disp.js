document._pi_=new Array();
function Int(v){v=parseInt(v);return(v)?v:0;}
function GetAbsXY(e){var p={x:0,y:0};do{p.x+=e.offsetLeft;p.y+=e.offsetTop; e=e.offsetParent;if(e==document.body)break;}while(e.offsetParent!=null)return p;}
ie=document.all;

(function(){
var P;
this.P$={
FRAME_INTERVAL:20,// 50ms=20fps
templates:{},
mods:{},
$:0,
w:window,
d:document,
__dispid:Math.round(Math.random()*200),
REargs:/\{([./a-zA-Z_0-9\*\+\-]+)\}/g,
find:function(a,t){if(!t)t=P$.d;return t.getElementById(a);},
findt:function(a,t){if(!t)t=P$.d;return t.getElementsByTagName(a);},
wr:function(s,t){if(!t)t=document;t.write(s)},
traceArray:[],
trace:function(a,b){
	var t=P$.$.traceArray;if(t.length<300)t.push([a,b]);else t.push(['Перегрузка буфера трасировки'])
},
getTraceHTML:function(){var e,r='',t=P$.$.traceArray;while(t.length){e=t.shift();r+="<tr><td style='font-size:9px;'><b>"+e[0]+"</b>"+((!!e[1])?"<br/>"+e[1]:"")+"</td></tr>";}return "<table cellspacing=1 cellpadding=0>"+r+"</table>"},
on:function(e,h,t){e=e.toLowerCase();if(!t)t=document;if(t.attachEvent)t.attachEvent('on'+e,h);else t.addEventListener(e,h,true);},
assign:function(d,s,nonex){if(!s)return;if(typeof s=='object'){for(var i in s){if(nonex && (d[i]!=undefined))continue;d[i]=s[i]}}},
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
dump:function(o){var i,s="",c=0;for(i in o){c++;if(c>100){s+="[...]";break;}s+="."+i+"="+o[i]+"\n"}return s;},
int:function(v){v=parseInt(v);return(v)?v:0;},
eventObj:function(e){return (ie)?e.srcElement:e.target;},		
warning:function(text,info,type,buttons,title){window.alert(text+((!!info)?'\n\n'+info:''))},
init:function(){
	var a,r,p=this.w;
	while(p!=a){a=p;p=p.parent;if(a.P$ && a.P$.$){P=this.$=a.P$.$;return 0;}}
	// ROOT LOADER = P$.$
	P=P$.$=this;
	P.on('load',P.enterFrame,this.w);
	return 1;
},
hTimer:0,
time:0,
timerSwitch:function(s){
	var t=P.hTimer,d;
	if(s){
		if(!t){
			P.time=(new Date()).valueOf();
			t=P.w.setInterval(P$.$.enterFrame,P.FRAME_INTERVAL);
//			P.find('timerInfo').innerHTML+='timer on<br>';
		}
	}else{
		if(t)P.w.clearInterval(t),t=0; //,P.find('timerInfo').innerHTML+='timer off<br>';
	}
	P.hTimer=t;
},
enterFrame:function(){
	P.time=(new Date()).valueOf();
	var i,s=0,m;
	for(i in P.mods){m=P.mods[i];if(!m.busy)P.enterFrameMod(m);s+=m.q.length;}
//	P.find('timerInfo').innerHTML+=s+'<br>';
	P.timerSwitch(s);
},

enterFrameMod:function(m){ 
	var pa,r,e,fu,st,nx,nxe,j,iii,i=0;
	while(i<m.q.length){
		e=m.q[i];
		pa=e[1]; 
		nx=st=0;
		if(!m.module){
			i++;
			//debugger;
			continue;
		}
		fu=m.module[e[0]];
		if(typeof pa=='object')st=Int(pa._startT),nx=pa.next;
		
		if(fu&&st<P.time){ 
			r=fu.call(m.module,e[1],e[2],e[3]);
			if(r!='+'){// '+' - means repeat tween again
				if(r!=undefined)e[2]=r;
				m.q.splice(i--,1);
				if(nx){
					if(typeof nx[0]!='string'){ // если всесто строки action лежит массив, значит надо запустить несколько действий
						for(j=0;j<nx.length;j++){nxe=nx[j];if(!nxe[2])nxe[2]=e[2];if(!nxe[3])nxe[3]=e[3];P.run.apply(m.module,nx[j]);}
					}else{if(!nx[2])nx[2]=e[2]; if(!nx[3])nx[3]=e[3]; P.run.apply(m.module,nx);}
				}
			}
		}
		i++;
	}
	return m.q.length;
},

//
// run(action:String, args:Object, targetObject:Object, localDispatcher:sys.disp)
run:function(act,a,o,p){
	if(!p)p=this;
	var l=act.split('.'),la=l.pop(),modn=l.join('.'),modp=l.join('/'),m=P.mods[modn];
	P.timerSwitch(1);
	if(p==undefined)p=P$;
	if(typeof params=='object'){
		if(a.start!=undefined){
			a._startT=P.time+a.start;
		}
	}
	if(!m)m=P.mods[modn]={name:modn,module:0,q:[],busy:1,script:P.include(PublicURL+"/"+modp+".js")};
	m.q.push([la,a,o,p]);
},

//
// use(nameOfModule:String, namesOfUsingModules:StringList, actionOnReady:String)
use:function(n,u,o){
	if(!u)return;
	var y=1,e,a,f,g,l=P$.$.mods[n],v=u.split(",");
	while(v.length){
		e=v.shift();
		P$.run(e+'.onUsing',{},l);
		// Если модуль, от которого зависит данный уже загружен, то ничего не делаем 
		f=P$.$.mods[e];
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
	var i,l,m,e,f,g,q,s,h,a=n.split('.');
	q=M.cartName=a.shift();
	s=M.modName=a.join('.');
	if(!P){alert('Dispatcher has not initialized!')}
	m=P[q];
	if(!m)m=P[q]={};
	l=P.mods[n];
	if(!l)l=P.mods[n]={name:n,q:[]};
	m[s]=l.module=M;
	l.loaded=1;
	l.busy=0;
	P.use(n,d);
	
	// dependents - зависящие от меня (вверху)
	// dependon   - от кого я зависим (внизу)
	
	// обратная задача - я должен обработать всех, кто зависит от меня (наверху)
	// то есть, если это sys.templates, то я должен проверить все ли хорошо у sys.win и если он уже загрузил
	// sys.buttons то запустить ему sys.win.initModule()
	
	// q-name r-loader of module
	function initModAndDependents(q,r){ //
		var a,c,t,j,b,y=P$.isObjEmpty;
		// смотрим, пуста ли зависимость от нижних
		// загружены ли нижние модули
		if(y(r.dependOn)){
			// если ни от кого я не зависим - запускаю внутренний инициализатор модуля
			if(r.module.initModule) {r.module.initModule();delete r.module.initModule;}
			// он может изменить dependOn, вызвав use
			a=r.actsOnInit;
			if(a){while(a.length){c=a.shift();P$.run(c);}}
		}
		// если зависимость осталась и нижние еще не загружены - выходим
		if(!y(r.dependOn))return;
		
		// смотрю на верхних зависимых от меня
		b=r.dependents;
		if(!y(b)){
			for(j in b){
				t=b[j];// t-loader для q=sys.templates  j="sys.win", t будет {sys.win}
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
include:function(f,c){
	var d=P.d,l=1,s=d.createElement("script"),p=P.findt('head',d)[0];
	s.onload=s.onreadystatechange=function(){
		if(l&&(!this.readyState||this.readyState=="loaded"||this.readyState=="complete")){
			l=0;if(c)c();
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
P$.on("load",function(){P$.run("sys.win.initWin")},P$.w);
