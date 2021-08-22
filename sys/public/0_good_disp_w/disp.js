document._pi_=new Array();
ie=document.all;
$=function(a,t){if(!t)t=document;return t.getElementById(a);};
$.a=function(a){alert(a);}
$.w=function(s,t){if(!t)t=document;t.write(s)};
$.on=function(e,h,t){e=e.toLowerCase();if(!t)t=document;if(t.attachEvent)t.attachEvent('on'+e,h);else t.addEventListener(e,h,false);}
$.let=function(d,s){if(!s)return;if(typeof(s)=='object'){for(var i in s)d[i]=s[i]}};
function rfalse(e){if(e)e.returnValue=false;return false;}
function var_dump(obj) {var s="";cnt=0; for (var i in obj) {cnt++; if (cnt>100) {s+="[...]";break;} s+="."+i+"="+obj[i]+"\n"} return s;}
function Int(v){v=parseInt(v);return(v)?v:0;}
function GetAbsXY(e){var p={x:0,y:0};do{p.x+=e.offsetLeft;p.y+=e.offsetTop; e=e.offsetParent;if(e==document.body)break;}while(e.offsetParent!=null)return p;}

$DISPn={
mods:{},
rt:0,
w:window,
d:document,
__dispid:Math.round(Math.random()*200),
init:function(){
	var a,r,p=this.w;
	$.w("<div id='Windows'>$DISPn</div>");
	window.setTimeout("$DISPn.callActsAll()",10);
	while(p!=a){a=p;p=p.parent;if(a.$DISP&&a.$DISP.rt){$DISP=this.rt=a.$DISP.rt;return 0;}}
	$DISP=this.rt=this;
	//$.on("ready",$DISP.callActsAll,document);
	
	return 1;
},
callActsAll:function(){alert(1);for(var i in $DISP.mods){$DISP.callActsCart($DISP.mods[i])}},
callActsCart:function(c){var i;for(i in c)$DISP.callActsMod(c[i])},
callActsMod:function(m){$DISP.callActs(m)},
callActs:function(_){ 
	var x,i;
  while(i=_.q.shift()){
  	x=_.module[i[0]];
  	if(x)x.call(_.module,i[1]);
  }
  _.busy=0;
},

action:function(act,args){
	var l=act.split('.'),cartn=l[0],modn=l[1],act=l[2],c=$DISP.mods[cartn],m;
	if(!c)c=$DISP.mods[cartn]={};
	m=c[modn];if(!m)m=c[modn]={module:0,q:[],cartn:cartn,modn:modn,g:0,busy:1,script:$DISP.include(PublicURL+"/"+cartn+"/"+modn+".js")};
	args.dn=$DISPn;
	if(m.busy)m.q.push([act,args]); else {m.module[act].call(m,args);}
},



regModule:function(_){
	var m=$DISP.mods[_.cartn][_.modn];
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
	return s;
}
};
if($DISPn.init()){$.w("<div id='mods'/>");}
$DISP.action("sys.win.initWin",$DISPn);