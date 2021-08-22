P$.$.regModule('sys.baseSkins',{
using:function(params,ldr,p){
	P$.trace('Вызван sys.skins.using()','Источник: '+ldr.name);
	
	},
	// вызывается после того как все модули, от которых зависит данный модуль, загружены
onSkinLoad:function(){
	P$.trace('Вызван sys.skins.onSkinLoad()');
	
},
initModule:function(){
	P$.trace('Вызван sys.skins.initModule()');
//	P$.use('sys.skins','.skin_win','sys.skins.onSkinLoad');
	
	P$.$.skins={
standardWindow:{
		l:"<table id='{hw}:tab' width={w} height={h} cellpadding=0 cellspacing=0 border=0>\
		<tr height={t_h}><td colspan=3><table width=100% border=0 cellpadding=0 cellspacing=0>\
		  <tr valign=top><td style='background:url({SkinURL}/sysw_01.gif)'><div style='width:{tl_w}px;height:{t_h}px'></div></td>\
		  <td id='{hw}:t' background='{SkinURL}/sysw_02.gif' width='100%'><div id='{hw}:bar' style='position:absolute;overflow:hidden;width:{w-tr_w-tl_w}'><table cellpadding=0 cellspacing=0 height={t_h} width=100%>\
		    <tr><td id='{hw}:lbuttons' nowrap></td><td width='100%' id='{hw}:title' dragMode='move' align='center' nowrap style='height:{t_h};cursor:move;font-family:verdana,arial,sans;font-size:12px;font-weight:bold;color:#ffffff;'><div style='overflow:hidden;'>{title}</div></td>\
		    <td nowrap align='right' id='{hw}:rbuttons'></td>\
		    <td align='right' id='{hw}:close'></td></tr>\
		    </table></div></td><td style='cursor:ne-resize;background:url({SkinURL}/sysw_03_short.gif)' dragMode='sz_ne'><div style='width:{tr_w}px;'/></td></tr></table></td></tr>\
		    <tr valign=top id={hw}:mh>\
		    <td background='{SkinURL}/sysw_04.gif'><div style='width:{l_w}px'/></td>\
		    <td bgcolor='#a0a0a0'><div style='cursor:default;width:{w-r_w-l_w};height:{h-t_h-b_h}' id='{hw}:text'>{text}</div></td>\
		    <td background='{SkinURL}/sysw_06.gif'><div style='width:{r_w}px'/></td></tr><tr height={b_h}>\
		<td colspan=3>\
		<table border=0 width='100%' cellpadding=0 cellspacing=0>\
		<tr><td height={b_h} style='background:url({SkinURL}/sysw_07.gif)'><div style='width:{bl_w}px;'/></td>\
		<td background='{SkinURL}/sysw_08.gif' width='100%'></td>\
		<td style='cursor:se-resize;background:url({SkinURL}/sysw_09.gif)' dragMode='sz_se'><div style='width:{br_w}px;'/></td></tr></table></td></tr></table>",
 p:{
 	buttons:{ 
 		close:{slot:'close',skinName:'btnPng',skinImg:'sysw_close',w:15,h:15}
 	},
 	tl_w:22,
 	t_h:23,
 	tr_w:10,l_w:8,r_w:8,b_h:13,bl_w:13,br_w:14,title:'Default title',text:'',
	onfocus:function(){
		var w=this;
	  w.le.title.innerHTML='focused '+w.title;
	},
	onblur:function(){
		var w=this;
		w.le.title.innerHTML='blurred '+w.title;
	},
	onfadein:function(style){
		P$.$.run([['sys.win.animate',{start:0,duration:200, opacity:'100',x:'-50',y:'-50',w:'+100',h:'+100'}],
			['sys.win.animate',{start:200,duration:200,x:'+5',y:'+5',w:'-10',h:'-10',next:['sys.win.loadWin']}]],0,this);
	},
	onfadeout:function(style,mr){
		var f=this.le.iframe;
		if(f)f.parentNode.removeChild(f);
		P$.$.run([
		['sys.win.animate',{start:100,duration:100,opacity:0}],
		['sys.win.animate',{start:0,duration:200,x:'+5',w:'-10',y:'+5',h:'-10',next:['sys.win.closeWin']}]
		],0,this);
	},
	ondragover:function(w){
		
	},

	onlayout:function(byuser){
	  var _=this,c=_.domContainer,t=_.le.tab,f,a,l=_.tl_w+_.tr_w,m=_.l_w+_.r_w,iw=_.w-_.l_w-_.r_w,ih=_.h-_.t_h-_.b_h;
		if(!c)return;
	  if(_.opacity!=undefined){
			if(_.opacity=='100') { 
//				if(ie)w.style.filter='progid:DXImageTransform.Microsoft.Shadow(color=#888898, direction=135, strength=5)';else w.style.opacity='';
				if(ie)t.style.filter='';else t.style.opacity='';
					} else { 
					if(ie)t.style.filter='alpha(opacity='+_.opacity+')'; else t.style.opacity=_.opacity/100;
				}
			}
		
	  if(_.x<0)_.x=0;
		if(_.y<0)_.y=0;
		_.w=Int(_.w);
		if(_.w<l)_.w=l;
		//if(_.h<40)_.h=40;
		if(c.offsetLeft!=_.x)c.style.left=_.x+'px';
		if(c.offsetTop!=_.y)c.style.top=_.y+'px';
		_.le.bar.style.width=(_.w-_.tr_w-_.tl_w)+'px';
		t.style.width=_.w;
		t.style.height=_.h+'px';
		f=_.le.text.style;
		f.width=iw+'px';
		f.height=ih+'px';
		f=_.le.iframe;
		if(f)f.width=iw,f.height=ih;
	  }},
	  le:'w,tab,bar,title,text,t,iframe,lbuttons,rbuttons,close,mh'.split(',')
	},
	
	btnPng:{
		l:"<img width='{w}' height='{h}' id='{hw}:i' src='{SkinURL}/{skinImg}_n.png' onMouseOver=' this.src=\"{SkinURL}/{skinImg}_h.png\";' onMouseOut='this.src=\"{SkinURL}/{skinImg}_n.png\"'>",
		le:['i'],
		p:{
			onfree:function(){this.le.i.parentNode.removeChild(this.le.i)},
			onchangestate:function(){
				var _=this,s=_.skinImg+'_'+_.state+'.png';
				_.le.i.src=_.SkinURL+'/'+s;
			}
		}
	},
	popWindow:{
		l:"<div hw='{hw}' id='{hw}:w'>\
			<table border=1 id='{hw}:tab' width={w} height={h} cellpadding=0 cellspacing=0>\
			<tr><td bgcolor='#606060'><div id='{hw}:title' style='overflow:hide;width:{w};height:{t_h-2};text-align:center'>{title}</div></td></tr>\
			<tr><td bgcolor='#ffffff' id='{hw}:text' >{text}</td></tr></table></div>",
		p:{
			t_h:20,
			zIndex:100,
			title:'The Popup window',
			onlayout:function(byuser){
				var _=this,f,a=_.le.w.style,c=_.domContainer;
	 	 		if(_.x<0)_.x=0;
				if(_.y<0)_.y=0;
				_.w=Int(_.w);
				if(_.w<20)_.w=20;
				if(_.h<40)_.h=40;
				if(c.offsetLeft!=_.x)c.style.left=_.x;
				if(c.offsetTop!=_.y)c.style.top=_.y;
				f=_.le.iframe;
				if(f){f.style.width=_.w-10;f.style.height=_.h-_.t_h;}
			}
		},
		le:'w,tab,title,text,iframe'.split(',')
	}};
	}

},'');


