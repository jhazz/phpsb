if(!P$){alert("Отладчик запускается только после запуска диспетчера процессов disp.js!");}
else (function(){
P$.extend((P$.$)?P$.$:P$,{
focusedControl:0,

systemInfo:{list:['Модули',"Окна","Контролы","Фреймы","Скины","P$.$"],mode:0,target:0,view:0,timer:0},
trace:function(a,b,c){ 
	var p=P$.$,t=p.traceArray;
	if(t.length<300)t.push([a,b,c]);
	if(!p.traceTimer){
		p.traceTimer=p.w.setTimeout(p.updateTraceView,300);
	}
},
updateTraceView:function(){
	var p=P$.$,t=p.traceArray,e,s,v=p.traceView;
	p.traceTimer=0;
	if(v){
		s=p.getTraceHTML();
		if(!s)return;
		e=v.ownerDocument.createElement('div');
		e.innerHTML=s;
		v.appendChild(e);
		v.scrollTop=v.scrollHeight;
	}
},
getTraceHTML:function(){ 
	var x,l,f,e,c,s,r='',t=P$.$.traceArray;
	if(!t.length)return "";
	while(t.length){
		e=t.shift();x=e[1];
		if(!x)x='';
		s=e[2];c='';
		f='#000000';l='#f0f0f0';
		if(s==1){c='[!]';l='#f8f8f0';}
		if(s==2){c='/!\\';f='#e00000';l='#fff0f0';}
		r+="<table width='100%' border=0 cellpadding=1 cellspacing=1><tr bgcolor='"+l+"' valign='top'><td width='30' style='font-size:9px;color:"+f+";'>"+c+"</td><td width='200'><div style='overflow-y:auto;overflow-x:hidden;width:200;height:14;font-size:9px;'><b>"+e[0]+"</b></div></td><td style='font-size:9px;color:"+f+";'>"+x+"</td></tr></table>";
	}
	return r;
},
putTraceView:function(to){
	P$.$.traceView=to;
	P$.$.updateTraceView();
},
dump:function(o){var i,s="",c=0;for(i in o){c++;if(c>100){s+="[...]";break;}s+="."+i+"="+o[i]+"\n"}return s;},

putPropertiesView:function(a,to){
	P$.$.propertiesView=to;
},
putSystemInfoMenuTo:function(to){
	var p=P$.$,si=p.systemInfo;
	si.menuView=to;
	p.updateSystemInfoMenu();
},
setSystemInfoMode:function(m){
	var p=P$.$,si=p.systemInfo;
	si.mode=m;
	p.updateSystemInfoMenu();
	p.doWatchSystemInfo();
},
updateSystemInfoMenu:function(){
	var p=P$.$,n,si=p.systemInfo,i,n,s='',s2;
	for(i in si.list){
		n=si.list[i];
		s+=((s)?"|":"")+((i==si.mode)?n:"<span class='m' onClick='P$.$.setSystemInfoMode("+i+");'>"+n+"</span>");
	}
	s+="<table width='100%'><tr><td>";
	if(!!si.obj){
		s+="<span class='m' onClick='P$.$.setSystemInfoMode(-1)'>"+si.objName+"</span>";
	}
	s2="<span class='hov' onclick='P$.$.watchStartStop()'>"+((si.stopped)?"[play]":"[stop]")+"</span>";
	if(si.stopped)s2+="&nbsp;<span class='hov' onclick='P$.$.doWatchSystemInfo()'>[refresh]</span>";
	s+="</td><td align='right'>"+s2+"</td></tr></table>";
	si.menuView.innerHTML=s;
},
watchStartStop:function(){
	var p=P$.$,si=p.systemInfo;
	si.stopped=!si.stopped;
	if(si.stopped){
		if(si.timer)p.w.clearInterval(si.timer);
		si.timer=0;
	}else if(!si.timer) si.timer=window.setInterval(p.doWatchSystemInfo,500);
	p.updateSystemInfoMenu();
},
watchControl:function(ctrlhw){
	var p=P$.$,si=p.systemInfo,a=ctrlhw.split(':');
	if(si.openpath)delete(si.openpath);
	if(a[0]=='frame'){
		si.obj=p.sys.win.frames[ctrlhw];
	}	else if(a[0]=='skin'){
		si.obj=p.skins[a[1]];
	}else si.obj=p.sys.win.allcomponents[ctrlhw];
	si.objName=(!!si.obj)?ctrlhw:ctrlhw+' не найден';
	si.mode=-1;
	p.updateSystemInfoMenu();
	p.doWatchSystemInfo();
},
expandWatchObj:function(path){
	var p=P$.$,si=p.systemInfo;
	if(!si.openpath)si.openpath={};
	si.openpath[path]=(si.openpath[path])?0:1;
	p.doWatchSystemInfo();
},
watchSystemInfoTo:function(to){
	if(!to){alert('Укажите HTML элемент, в который будет выводиться информация о процессах');return;}
	var p=P$.$,si=p.systemInfo;
	si.view=to;
	if(!si.timer)si.timer=window.setInterval(p.doWatchSystemInfo,500);
	p.doWatchSystemInfo();
},
doWatchSystemInfo:function(){
	if((!P$.$.sys)||(!P$.$.sys.win))return;
	var t,a,m,i,ss,W=P$.$.sys.win,si=P$.$.systemInfo,s='';

	if (si.mode==5){si.objName='P$.$';si.obj=P$.$;si.mode=-1;}
	if(!si.mode){
	s+="<h3>Модули</h3><table class='props' width='100%'>";
	for(i in P$.$.mods){
		ss='';
		m=P$.$.mods[i];
		if(m){
			ss+="<tr><td><b>"+i+"</b></td><td align='right'><a href='javascript:;P$.$.reload(\""+i+"\")'>reload</a></td></tr>"
			+"<tr><td>.loaded</td><td"+((!m.loaded)?" class='err'":"")+">"+m.loaded+"</td></tr>"
			+"<tr><td>.busy</td><td"+((m.busy)?" class='err'":"")+">"+m.busy+"</td></tr>"
			+"<tr><td>.initModuleCalled</td><td"+((!m.initModuleCalled)?" class='err'":"")+">"+m.initModuleCalled+"</td></tr>"
			+"<tr><td>.script</td><td>"+m.script+"</td></tr>";
			if(m.script){
				ss+="<tr><td>.script.readyState</td><td>"+m.script.readyState+"</td></tr>";
			}
			if(m.dependOn&&(!P$.isObjEmpty(m.dependOn))){
				ss+="<tr><td>dependOn (висит из-за):</td><td class='err'>";
				for(j in m.dependOn){
					ss+=j+"<br>";
				}
				ss+="</td></tr>";
			}
			
			if(m.dependents&&P$.isObjEmpty(m.dependents)){
				ss+="<tr><td>dependents:</td><td>";
				for(j in m.dependents){
					ss+=j+"<br>";
				}
				ss+="</td></tr>";
			}
		}
		s+=ss;
	} s+="</table>";
	}else if (si.mode==1){
		s+="<h3>Окна</h3><table class='props' width='100%'>";
		a=W.allwins;
		for(i in a){
			w=a[i];
			s+="<tr><td><b onClick='P$.$.watchControl(\""+w.hw+"\")' class='hov'>"+w.hw+"</b></td><td><b>"+"</b></td></tr>";
			if(w.components){
				ss="";for(c in w.components){ss+="<span class='hov' onClick='P$.$.watchControl(\""+c+"\")'>"+c+"</span> ";}
				s+="<tr><td>components:</td><td>"+ss+"</td></tr>";
			}
			if(w.wins){ss="";for(c in w.wins){ss+=c+" ";}
				s+="<tr><td>Wins:</td><td>"+ss+"</td></tr>";
			}
			s+="<tr><td>Skin:</td><td>"+w.skinName+"</td></tr>";
			s+="<tr><td>Pos:</td><td>"+((w.autopos)?"AUTO! ":"")+w.x+","+w.y+" ("+w.w+"x"+w.h+")</td></tr>";
			if(w.openerp){
				ss=w.openerp.Wn.hw;
				s+="<tr><td>Opener:</td><td><span class='hov' onClick='P$.$.watchControl(\""+ss+"\")'>"+ss+"</span></td></tr>";
			}
			if(w.parent){
				ss=w.parent.hw;
				s+="<tr><td>Parent:</td><td><span class='hov' onClick='P$.$.watchControl(\""+ss+"\")'>"+ss+"</span></td></tr>";
			}
			if(w.p){
				s+="<tr><td>P$._dispId:</td><td>"+w.p._dispId+"</td></tr>";
			}
			if(w.url){
				s+="<tr><td>url</td><td>"+w.url+"</td></tr>";
			}
			
		}
		s+="</table>"
	}else if (si.mode==2){
		s+="<h3>Контролы</h3><table class='props' width='100%' border=0 cellspacing=1>";
		a=W.allcomponents;
		for(i in a){
			w=a[i];
			s+="<tr><td><b class='hov' onClick='P$.$.watchControl(\""+i+"\")'>"+i+"</b></td><td><b>"+"</b></td></tr>";
		}
		s+="</table>"
	} else if (si.mode==3) {
		s+="<h3>Фреймы</h3><table class='props' width='100%' border=0 cellspacing=1>";
		a=W.frames;
		for(i in a){
			w=a[i];
			s+="<tr><td><b class='hov' onClick='P$.$.watchControl(\""+i+"\")'>"+i+"</b></td><td><b>"+"</b></td></tr>";
		}
		s+="</table>"
	} else if (si.mode==4) {
		s+="<h3>Скины</h3><table class='props' width='100%' border=0 cellspacing=1>";
		a=P$.$.skins;
		for(i in a){
			w=a[i];
			s+="<tr><td><b class='hov' onClick='P$.$.watchControl(\"skin:"+i+"\")'>"+i+"</b></td><td><b>"+"</b></td></tr>";
		}
		s+="</table>"
	}else if (si.mode==-1){
		
		function untag(s){return s.replace(/[<>\t]/g,function(k){return(k=="\t")?" ":(k=="<")?"&lt;":"&gt;"})}
		/*"*/
		
		function collectProps(a,path,level){
			var lcss,i,j,sp,pn,v,s='',z,c,sm='',e;
			
			lcss=""
			if(level>0)lcss=" class='level"+level+"'";

			try{
			for(i in a){
				sp='';for(j=0;j<level;j++)sp+='&nbsp;|&nbsp;';
				try{v=a[i];}catch(z){v=z.message}
				t=typeof v;
				cp=path+'.'+i;
				if(t=='string'){ 
					if(v.length>1000)v=v.substr(0,990)+"...";
					s+="<tr><td nowrap"+lcss+">"+sp+"&nbsp;&nbsp;"+i+"</td><td"+lcss+">"+untag(v)+"</td></tr>";
				}else if(t=='object'){
					e=(si.openpath)?si.openpath[cp]:0;
					pn=(e)?"<b>"+i+"</b>":i;
					s+="<tr><td nowrap"+lcss+">"+sp+"<span class='hov' title='"+cp+"' onClick='P$.$.expandWatchObj(\""+cp+"\")'>"+((e)?'&#8211;':'+')+"</span>"+pn+"</td><td"+lcss+">"+v+"</td></tr>";
					if(e){
						if(level<5)s+=collectProps(v,cp,level+1);
					}
				}else if(t=='function'){ 
					cp=path+'.[Methods]';
					e=(si.openpath)?si.openpath[cp]:0;
					if(!sm){
						sm="<tr><td nowrap"+lcss+">"+sp+"<span class='hov' title='"+cp+"' onClick='P$.$.expandWatchObj(\""+cp+"\")'>"+((e)?'-':'+')+"</span>[Methods]</td><td"+lcss+"></td></tr>";
					}
					if(e){
						sm+="<tr><td nowrap"+lcss+">"+sp+"&nbsp;&nbsp;&nbsp;&nbsp;"+i+"()</td><td"+lcss+"><pre>"+untag(v.toString())+"</pre></td></tr>";
					}
				}else s+="<tr><td nowrap"+lcss+">"+sp+"&nbsp;&nbsp;"+i+"</td><td"+lcss+">"+v+"</td></tr>";
			}
			}catch(z){s+="<tr><td"+lcss+"></td><td>"+z.message+"</td></tr>";}
			return sm+s;
		}
		s+="<h3>Объект "+si.objName+"</h3><table class='props' width='100%' cellpadding=0 cellspacing=1>";
		if(si.obj){
			s+= collectProps(si.obj,si.objName,0);
		}else{
			s+="<tr><td>Удален</td></tr>";
		}
		s+="</table>"
		
	}
	if(si.old!=s){si.old=s;P$.$.systemInfo.view.innerHTML=s+'Refresh @'+(new Date()).valueOf()+"<br/>"}
	//else delete s;
}


}
)})();
window.status='Debugger has been started';