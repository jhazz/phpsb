document._pi_=new Array();
ie=document.all;
$=function(a,t){if(!t)t=document;return t.getElementById(a);};
$.a=function(a){alert(a);}
$.w=function(s,t){if(!t)t=document;t.write(s)};
$.on=function(e,h,t,opts){e=e.toLowerCase();if(!t)t=document;if(t.attachEvent)t.attachEvent('on'+e,h);else t.addEventListener(e,h,true);}
$.let=function(d,s,nonex){if(!s)return;if(typeof(s)=='object'){for(var i in s){if(nonex && (d[i]!=undefined))continue;d[i]=s[i]}}};
$.absxy=function(e){var p={x:0,y:0};do{p.x+=e.offsetLeft;p.y+=e.offsetTop; e=e.offsetParent;if(e==document.body)break;}while(e.offsetParent!=null)return p;}
$.no=function(e){if(e){e.returnValue=false;if(e.preventDefault)e.preventDefault();}return false;}
$.dump=function(o){var s="";cnt=0; for (var i in o) {cnt++; if (cnt>100) {s+="[...]";break;} s+="."+i+"="+o[i]+"\n"} return s;}

$.int=function(v){v=parseInt(v);return(v)?v:0;}
$.REargs=/\{([./a-zA-Z_0-9\*\+\-]+)\}/g;
$.eventObj=function(e){return (ie)?e.srcElement:e.target;}
function Int(v){v=parseInt(v);return(v)?v:0;}
function GetAbsXY(e){var p={x:0,y:0};do{p.x+=e.offsetLeft;p.y+=e.offsetTop; e=e.offsetParent;if(e==document.body)break;}while(e.offsetParent!=null)return p;}
$.Dn={
templates:{},
	/*
	standardWindow:{
		div:{
		_id:'w',style:{position:'absolute',zIndex:"{zIndex}",left:'{x}',top:'{y}'},
		_:[{table:{_id:'a',border:0,celpadding:0,cellspacing:0,width:'{w}',height:'{h}',_:[{tbody:{_:[{tr:{_:[{td:{width:20,_:[{img:{src:SkinURL+"/sysw_01.gif"}}]}},{td:{background:SkinURL+'/sysw_02.gif',$:'PROVERKA'}}] }}]}}]
		}},{p:{$:'zzzz'}}]
		}
	}*/
mods:{},
rt:0,
w:window,
d:document,
__dispid:Math.round(Math.random()*200),
init:function(){
	var a,r,p=this.w;
	$.w("<div id='Windows'>$.Dn</div>");
	while(p!=a){a=p;p=p.parent;if(a.$&&a.$.D&&a.$.D.rt){$.D=this.rt=a.$.D.rt;return 0;}}
	$.D=this.rt=this;
	$.on('load',$.D.callActsAll,window);
	return 1;
},
callActsAll:function(){for(var i in $.D.mods){$.D.callActsCart($.D.mods[i])}},
callActsCart:function(c){var i;for(i in c)$.D.callActsMod(c[i])},
callActsMod:function(m){$.D.callActs(m)},
callActs:function(_){ 
	var x,i;
  while(i=_.q.shift()){
  	x=_.module[i[0]];
  	if(x)x.call(_.module,i[1],i[2]);
  }
  _.busy=0;
},

call:function(act,args){
	var l=act.split('.'),cartn=l[0],modn=l[1],act=l[2],c=$.D.mods[cartn],m;
	if(!c)c=$.D.mods[cartn]={};
	m=c[modn];if(!m)m=c[modn]={module:0,q:[],cartn:cartn,modn:modn,g:0,busy:1,script:$.D.include(PublicURL+"/"+cartn+"/"+modn+".js")};
	if(m.busy)m.q.push([act,args,$.Dn]); else m.module[act].call(m,args,$.Dn);
},

regModule:function(_){
	var m=$.D.mods[_.cartn][_.modn];
	m.module=_;
	m.loaded=m.busy=1;
},

include:function(f,c){
	var d=this.rt.d,l=1,s=d.createElement("script"),p=$('mods',d);
	s.onload=s.onreadystatechange=function(){
		if(l&&(!this.readyState||this.readyState=="loaded"||this.readyState=="complete")){
			l=0;if(c)c();
			this.onreadystatechange=this.onload=null;
		}
	}
	p.appendChild(s);
	s.src=f;
}
};
if($.Dn.init()){
	$.D.templates={
	standardWindow:{
	l:{div:{
		id:'{id}:w',style:{position:'absolute',zIndex:"{zIndex}",left:'{x}',top:'{y}'},
		$:"<table id='{id}:tab' width={w} height={h} cellpadding=0 cellspacing=0 border=0><tr height={t_h}><td colspan=3>"
	+"<table width=100% border=0 cellpadding=0 cellspacing=0><tr valign=top><td width={tl_w}><div id='{id}:bar' style='position:absolute;overflow:hidden;height:{t_h};width:{w-tr_w}'><table cellpadding=0 cellspacing=0 border=0 width=100%><tr><td id='{id}:lbuttons' nowrap></td><td width='100%' id='{id}:title' dragMode='move' hwnd='{id}' align='center' nowrap style='height:{t_h};cursor:move;font-family:verdana,arial,sans;font-size:12px;font-weight:bold;color:#ffffff;'>"
	+"{title}</td><td nowrap align='right' id='{id}:rbuttons'></td></tr></table></div><img src='{SkinURL}/sysw_01.gif' width={tl_w} height={t_h}></td><td width='100%' id='{id}:t' background='{SkinURL}/sysw_02.gif'>"
	+"</td><td width={tr_w}><img style='cursor:pointer' id='{id}|c' onClick='$.Dn.call(\"sys.win.modalResult\",{close:1,event:event})' width={tr_w} height={t_h} src='{SkinURL}/sysw_03.gif'></td></tr></table></td></tr>"
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
  $.w("<div id='mods'/>");
}
$.Dn.call("sys.win.initWin");
