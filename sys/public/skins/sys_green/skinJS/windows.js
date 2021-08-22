P$.$.regModule('skinJS.windows',{
onUsing:function(params,ldr,p){
	P$.trace('Вызван skinJS.windows.onUsing()','Источник: '+ldr.name);
	
	},
	// вызывается после того как все модули, от которых зависит данный модуль, загружены
initModule:function(){
	P$.trace('Вызван skinJS.windows.initModule()');
	
	P$.$.templates={
	standardWindow:{
		l:{div:{
		id:'{id}:w',style:{position:'absolute',zIndex:"{zIndex}",left:'{x}',top:'{y}'},
		$:"<table id='{id}:tab' width={w} height={h} cellpadding=0 cellspacing=0 border=0><tr height={t_h}><td colspan=3>"
	+"<table width=100% border=0 cellpadding=0 cellspacing=0><tr valign=top><td width={tl_w}><div id='{id}:bar' style='position:absolute;overflow:hidden;height:{t_h};width:{w-tr_w}'><table cellpadding=0 cellspacing=0 border=0 width=100%><tr><td id='{id}:lbuttons' nowrap></td><td width='100%' id='{id}:title' dragMode='move' hwnd='{id}' align='center' nowrap style='height:{t_h};cursor:move;font-family:verdana,arial,sans;font-size:12px;font-weight:bold;color:#ffffff;'>"
	+"{title}</td><td nowrap align='right' id='{id}:rbuttons'></td></tr></table></div><img src='{SkinURL}/sysw_01.gif' width={tl_w} height={t_h}></td><td width='100%' id='{id}:t' background='{SkinURL}/sysw_02.gif'>"
	+"</td><td width={tr_w}><img style='cursor:pointer' id='{id}|c' onClick='P$.run(\"sys.win.modalResult\",\"close\",\"{id}\")' width={tr_w} height={t_h} src='{SkinURL}/sysw_03.gif'></td></tr></table></td></tr>"
	+"<tr><td background='{SkinURL}/sysw_04.gif' width={l_w}><img src='{SkinURL}/sp.gif' width={l_w}></td><td width='100%' hwnd='{id}' id='{id}:text' align='center' bgcolor='#f8faff' style='cursor:default'>"
	+"{text}</td><td width={r_w} background='{SkinURL}/sysw_06.gif'><img src='{SkinURL}/sp.gif' width={r_w}></td></tr>"
	+"<tr height={b_h}><td colspan=3><table border=0 width='100%' cellpadding=0 cellspacing=0><tr><td width={bl_w}><img src='{SkinURL}/sysw_07.gif' width={bl_w} height={b_h}></td><td background='{SkinURL}/sysw_08.gif' width='100%'></td><td width={br_w}>"
	+"<img style='cursor:nw-resize;' hwnd='{id}' dragMode='sz_wh' src='{SkinURL}/sysw_09.gif'></td></tr></table>"
	+"</td></tr></table>"
	}},
		p:{tl_w:22,t_h:23,tr_w:22,l_w:8,r_w:8,b_h:13,bl_w:13,br_w:14,title:'Default title',text:'',
	  	onlayout:function(byuser){
	  	var w=this,f,a=w.e.w.style,l=w.tl_w+w.tr_w,m=w.l_w+w.r_w;
			if(w.opacity!=undefined){
			  if(w.opacity=='100') { 
			  	if(ie)a.filter='progid:DXImageTransform.Microsoft.Shadow(color=#888898, direction=135, strength=5)';else w.e.w.style.opacity='';
			  } else { 
			  	if(ie)a.filter='alpha(opacity='+w.opacity+')'; else w.e.w.style.opacity=w.opacity/100;
			  }
			}

	  	if(w.x<0)w.x=0;
			if(w.y<0)w.y=0;
			w.w=Int(w.w);
			if(w.w<l)w.w=l;
			if(w.h<40)w.h=40;
			if(w.e.w.offsetLeft!=w.x)a.left=w.x;
			if(w.e.w.offsetTop!=w.y)a.top=w.y;
			w.e.bar.style.width=w.w-w.tr_w;
			w.e.tab.style.width=w.w;
			w.e.tab.style.height=w.h;
			f=w.e.iframe;
			if(f)with(f.style){width=w.w-m;height=w.h-w.t_h-w.b_h;}
	  }},
	  e:'w,tab,bar,title,text,t,iframe,lbuttons,rbuttons'.split(',')
	},
	popWindow:{
		l:{
			div:{ 
				id:'{id}:w',
				style:{position:'absolute',zIndex:'{zIndex}',left:'{x}',top:'{y}'},
				$:"<table '{id}:tab' width={w} height={h} cellpadding=2 cellspacing=0><tr><td bgcolor='#606060'><div id='{id}:title'><i>{title}></i></div>{text}</td></tr></table>"
			}
		},
		p:{
			zIndex:100,
			title:'Popup default',text:'',
			onlayout:function(byuser){

			}
		},
		e:'w,title'.split(',')
	}};
	}

},'');