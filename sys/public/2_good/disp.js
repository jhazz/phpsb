document._pi_=new Array();
ie=document.all;
function Int(v){v=parseInt(v);return(v)?v:0;}
function GetAbsXY(e){var p={x:0,y:0};do{p.x+=e.offsetLeft;p.y+=e.offsetTop; e=e.offsetParent;if(e==document.body)break;}while(e.offsetParent!=null)return p;}

P$=function(a,t){if(!t)t=P$.d;return t.getElementById(a);};
P$.a=function(a){alert(a);}
P$.wr=function(s,t){if(!t)t=document;t.write(s)};
P$.on=function(e,h,t,opts){e=e.toLowerCase();if(!t)t=document;if(t.attachEvent)t.attachEvent('on'+e,h);else t.addEventListener(e,h,true);}
P$.let=function(d,s,nonex){if(!s)return;if(typeof(s)=='object'){for(var i in s){if(nonex && (d[i]!=undefined))continue;d[i]=s[i]}}};
P$.absxy=function(e){var p={x:0,y:0};do{p.x+=e.offsetLeft;p.y+=e.offsetTop; e=e.offsetParent;if(e==document.body)break;}while(e.offsetParent!=null)return p;}
P$.no=function(e){if(e){e.returnValue=false;if(e.preventDefault)e.preventDefault();}return false;}
P$.dump=function(o){var s="";cnt=0; for (var i in o) {cnt++; if (cnt>100) {s+="[...]";break;} s+="."+i+"="+o[i]+"\n"} return s;}
P$.int=function(v){v=parseInt(v);return(v)?v:0;}
P$.REargs=/\{([./a-zA-Z_0-9\*\+\-]+)\}/g;
P$.eventObj=function(e){return (ie)?e.srcElement:e.target;}
P$.templates={};
P$.mods={};
P$.$=0;
P$.w=window;
P$.d=document;
P$.__dispid=Math.round(Math.random()*200);
P$.init=function(){
	var a,r,p=this.w;
	P$.wr("<div id='Windows'>P$.Dn</div>");
	while(p!=a){a=p;p=p.parent;if(a.P$ && a.P$.$){this.$=a.P$.$;return 0;}}
	// no parents:
	P$.$=this;
	P$.on('load',P$.callActsAll,this.w);
	return 1;
};
P$.callActsAll=function(){for(var i in P$.mods)P$.callActs(P$.mods[i])};
P$.callActs=function(_){ 
	var x,i;
	// Надо сделать обработку копии очереди, а не самой очереди, чтобы можно было отложить
	// обработку отдельных событий и позволить добавлять события изнутри события
	
  while(i=_.q.shift()){
  	x=_.module[i[0]];
  	if(x)x.call(_.module,i[1],i[2]);
  }
  _.busy=0;
};

P$.run=function(act,args){
	var l=act.split('.'),modn=l[0]+'.'+l[1],act=l[2],m=P$.$.mods[modn];
	if(!m)m=P$.$.mods[modn]={module:0,q:[],busy:1,script:P$.include(PublicURL+"/"+l[0]+"/"+l[1]+".js")};
	if(m.busy)m.q.push([act,args,P$]); else m.module[act].call(m,args,P$);
};

P$.regModule=function(n,_){
	var l,m,a=n.split('.');_.cartn=a[0];_.modn=a[1];
	if(!P$.$){alert('Dispatcher has not initialized!')}
	m=P$.$[_.cartn];
	if(!m)m=P$.$[_.cartn]={};
	m[_.modn]=_;
	
	l=P$.$.mods[n];
	if(l){l.module=m[_.modn];l.loaded=l.busy=1;}
	else alert('disp.js: Module loader not inited: '+n);
};

P$.include=function(f,c){
	var r=this.$,d=r.d,l=1,s=d.createElement("script"),p=r('mods',d);
	s.onload=s.onreadystatechange=function(){
		if(l&&(!this.readyState||this.readyState=="loaded"||this.readyState=="complete")){
			l=0;if(c)c();
			this.onreadystatechange=this.onload=null;
		}
	}
	p.appendChild(s);
	s.src=f;
	return s;
};


if(P$.init()){
	P$.$.templates={
	standardWindow:{
	l:{div:{
		id:'{id}:w',style:{position:'absolute',zIndex:"{zIndex}",left:'{x}',top:'{y}'},
		$:"<table id='{id}:tab' width={w} height={h} cellpadding=0 cellspacing=0 border=0><tr height={t_h}><td colspan=3>"
	+"<table width=100% border=0 cellpadding=0 cellspacing=0><tr valign=top><td width={tl_w}><div id='{id}:bar' style='position:absolute;overflow:hidden;height:{t_h};width:{w-tr_w}'><table cellpadding=0 cellspacing=0 border=0 width=100%><tr><td id='{id}:lbuttons' nowrap></td><td width='100%' id='{id}:title' dragMode='move' hwnd='{id}' align='center' nowrap style='height:{t_h};cursor:move;font-family:verdana,arial,sans;font-size:12px;font-weight:bold;color:#ffffff;'>"
	+"{title}</td><td nowrap align='right' id='{id}:rbuttons'></td></tr></table></div><img src='{SkinURL}/sysw_01.gif' width={tl_w} height={t_h}></td><td width='100%' id='{id}:t' background='{SkinURL}/sysw_02.gif'>"
	+"</td><td width={tr_w}><img style='cursor:pointer' id='{id}|c' onClick='P$.Dn.call(\"sys.win.modalResult\",{close:1,event:event})' width={tr_w} height={t_h} src='{SkinURL}/sysw_03.gif'></td></tr></table></td></tr>"
	+"<tr><td background='{SkinURL}/sysw_04.gif' width={l_w}><img src='{SkinURL}/sp.gif' width={l_w}></td><td width='100%' hwnd='{id}' id='{id}:text' align='center' bgcolor='#f0eaec'>"
	+"{text}</td><td width={r_w} background='{SkinURL}/sysw_06.gif'><img src='{SkinURL}/sp.gif' width={r_w}></td></tr>"
	+"<tr height={b_h}><td colspan=3><table border=0 width='100%' cellpadding=0 cellspacing=0><tr><td width={bl_w}><img src='{SkinURL}/sysw_07.gif' width={bl_w} height={b_h}></td><td background='{SkinURL}/sysw_08.gif' width='100%'></td><td width={br_w}>"
	+"<img style='cursor:nw-resize;' hwnd='{id}' dragMode='sz_wh' src='{SkinURL}/sysw_09.gif'></td></tr></table>"
	+"</td></tr></table>"
	}}
	,p:{tl_w:22,t_h:23,tr_w:22,l_w:8,r_w:8,b_h:13,bl_w:13,br_w:14,title:'Default title',text:'Please wait...',
	  onlayout:function(w,byuser){var _,l=w.tl_w+w.tr_w,m=w.l_w+w.r_w;
			if(w.x<0)w.x=0;
			if(w.y<0)w.y=0;
			w.w=Int(w.w);
			if(w.w<l)w.w=l;
			if(w.h<40)w.h=40;
			
	  	if(w.e.w.style.left!=w.x)w.e.w.style.left=w.x;
			if(w.e.w.style.top!=w.y)w.e.w.style.top=w.y;
			w.e.bar.style.width=w.w-w.tr_w;
			w.e.tab.style.width=w.w;
			w.e.tab.style.height=w.h;
			window.status=w.w;
			_=w.e.iframe;
			if(_)with(_.style){width=w.w-m;height=w.h-w.t_h-w.b_h;}
	  }}
	,e:['w','tab','bar','title','text','t','iframe','lbuttons','rbuttons']
	}
  };
  P$.wr("<div id='mods'/>");
}
});
P$.run("sys.win.initWin");
