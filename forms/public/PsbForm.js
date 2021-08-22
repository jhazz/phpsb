PFD={
create:function(n,v,p){ 
		if(!p)p=PF.control;var i,c=function(){},a=c.prototype=new p();for(i in v)a[i]=v[i];a.name=n;eval(n+"=c;");
		return c;
	},
style:{scrollBarWidth:18},
Instance:function(f,m){
		var aa=m.attributes,l=aa.length,i,a;
		for(i=0;i<l;i++){a=aa.item(i);this[a.nodeName]=a.nodeValue;}
		this.modelInstance=m;
		this.form=f;
		this.dom=PFD.createDOM();
		if(this.src){
			this.loader=PFD.createDOMLoader();
			if(m.getAttribute('loadOnStart')){this.loadData();}
		} else {
			this.dom.appendChild(m.cloneNode(true));
		}[z1[]]
		return this;
	},

forms:{}, // [form].formId  .layoutId  .modelId  .dom  .lastCtrlId  .hasInit  .ctrlById  .instances .instancesHistory
loaders:{},
loadTimer:0,
model_js:{},
layout_js:{},
lastRowId:1,

	// CONSTANTS:
updateInterval:0,
colors:{deleted:'#ffe0e0',inserted:"#f8fff8",changed:"#ffffe0",defaults:"#e0e0e0",inschanged:"#30ff30",ordinal:'#f8feff'},
reInstance:/^(\w+):(.*)/,
evalre1:/(\w+)\s*\(/g,
evalre2:/\{([./a-zA-Z_]+)\}/g,
reDelId:/\[.*?\]\//g,
	// METHODS:
getContent:function(n){return(ie)?n.text:n.textContent;},
setContent:function(n,v){if(ie)n.text=v;else n.textContent=v;},
msg:function(s,notice,isError){
		//	return;
		notice=((notice)?"<br>"+notice:"");
		var t=P$.find("debugTable");
		var newRow=t.insertRow(t.rows.length-1);
		var cc=newRow.insertCell(0);
		newRow.style.backgroundColor="#"+((isError)?"fff0f0":"fffff8");
		cc.style.fontSize='9';
		cc.innerHTML=((isError)?"/!\\":"[_]");
		cc=newRow.insertCell(1);
		cc.style.fontSize='9';
		cc.innerHTML="<b>"+s+"</b>"+notice;
	},
createDOM:function(){var r=undefined;try {r=new ActiveXObject("Microsoft.XMLDOM");}catch (e){try {r=document.implementation.createDocument("", "", null);}catch(e){alert ('XML is not supported by your browser.');}}return r;},
createDOMLoader:function(){var r;try{r=new XMLHttpRequest}catch (e){try{r=new ActiveXObject("Msxml2.XMLHTTP");}catch(e){try {r=new ActiveXObject("Microsoft.XMLHTTP");}catch(e){alert ('XML is not supported by your browser.');}}}return r;},
concatPath:function(p1,p2){
		//	this.msg("Concat "+p1,p2);
		if((p2=="")||(p2==undefined))return p1;
		if(p2.charAt(0)=='/'){if(this.reInstance.test(p1)){var r=this.reInstance.exec(p1);return r[1]+":"+p2;}return p2;}
		if(this.reInstance.test(p2))return p2;
		if((p1.length==0)||(p1.charAt(p1.length-1)!='/')){p1+="/";}
		return p1+p2;},
rLen:function(r){return(ie)?r.length:r.snapshotLength;},
rItem:function(r,i){return(ie)?r.item(i):r.snapshotItem(i);},
getNewCtrlId:function(f){return "ctrl:"+f.formId+":"+(f.lastCtrlId++);},
getParentPath:function(path) {var p=path.lastIndexOf('/');if(p>0){return path.substr(0,p);}return '/';},
getNewRowId:function(){return "-"+(this.lastRowId++);},
removeEndSlash:function(s){if(s.charAt(s.length-1)=='/')s=s.substr(0,s.length-1);return s;},
setFirstSlash:function(p){if(p.charAt(0)!='/')p="/"+p;return p;},
extractPathElements:function(p){if(this.reInstance.test(p)){return this.reInstance.exec(p);}else{this.msg("extractPathElements:Путь не содержит идентификатора instance:",p);}},
findInstanceNode:function(f,path,multiple){
		var ins,d,r=this.extractPathElements(path);
		if(r) {
			ins=f.instances[r[1]];
			if(!ins){this.msg('Unknown instance'+r[1],path);return false;}
			d=ins.dom;
			if(!d){return false;}
			s=this.removeEndSlash(r[2]);
			if(!ins.src)s="/instance"+s;
			if (multiple) return this.findNodes(d,s);	else return this.findNode(d,s);
		}
	},
ctrlEvent:function(method,that,args){
		return this.ctrlMethod(method,that.getAttribute('id'),args);
	},
ctrlMethod:function(method,id,args){
		var r,x=id.split(':'),f=PFD.forms[x[1]];
		if(!f){alert('Form not found '+x[1]);return;}
		c=f.ctrlById["ctrl:"+x[1]+":"+x[2]];
		if(c){return c[method](args);} else {alert('Control '+id+' not found');}
	},
getInstanceSchemaNode:function(f,path) {
		var p,sc,n,instance,r=this.extractPathElements(path),instanceId;
		if (!r)return;
		instance=f.instances[r[1]];
		if(!instance){this.msg("getInstanceSchemaNode: Неизвестный экземпляр",r[1],1);return;}
		sc=instance.dom.firstChild.getAttribute('schema');
		path=this.setFirstSlash(r[2]);
		if (!sc){this.msg('getInstanceSchemaNode: Схема не определена для пути',path,1);return;}
		p="/model/schema[@id='"+sc+"']"+path.replace(this.reDelId,"/");
		return this.findNode(f.dom,p);
	},

findNodes:function(dom,path){try{return(ie)?dom.selectNodes(path):dom.evaluate(path,dom,null,7,null);}catch(e){alert(path+"\n\n"+var_dump(e));}},
findNode:function(dom,path,contextNode){
		if(ie) { 
			var n;
			if(contextNode!=undefined){
				try{n=contextNode.selectSingleNode(path);}catch(e){this.msg("findNode: "+e.message);}
			} else {
				n=dom.selectSingleNode(path);
			}
			return n;
			//return (contextNode!=undefined) ? contextNode.selectSingleNode(path) : dom.selectSingleNode(path);
		}
		var n=dom.evaluate(path,dom,contextNode,9,null);
		if(n)return n.singleNodeValue;
	},

getRefPathAndNode:function(form,contextNode,contextPath,ref,doCreate,doInsert,id){
		var cp,i,ins,sn,rc,rr,idom;
		rr=this.reInstance.test(ref);
		if (rr) {
			// ref==("instance:anypath" or "instance:/anypath")
			rr=this.reInstance.exec(ref);
			ins=rr[1];
			ref=rr[2];
			if(ref.charAt(0)!='/')ref="/"+ref;
			cp=ref;
			idom=form.instances[ins].dom;
			if(!idom)return false;
			pn=[ins+":"+ref,this.findNode(idom,ref)];
		} else {
			// ref==("../../some" OR "some" OR "/some" OR "")
			if(!this.reInstance.test(contextPath)){
				this.msg('getRefPathAndNode: contextPath не содержит указания на экземпляр данных',contextPath);
				return false;
			}
			
			if((!contextNode)&&(!doCreate)){
				this.msg('getRefPathAndNode: contextNode должен указывать на узел дерева экземпляра данных, когда используется относительная ссылка',ref);
				return false;
			}
			rc=this.reInstance.exec(contextPath);
			ins=rc[1];
			cp=rc[2];
			if((ref!="")&&(ref!=undefined)) {
				while(ref.substr(0,3)=='../'){
					ref=ref.substr(3);
					i=cp.lastIndexOf('/');
					if (i>0) {
						if (!contextNode.parentNode)break;
						cp=cp.substr(0,i);
						contextNode=contextNode.parentNode;
					}
				}
				if(ref.charAt(0)=='/'){
					cp=ref;
					pn=[ins+":"+ref,this.findNode(form.instances[ins].dom,ref)];
				} else {
					if(cp.charAt(cp.length-1)!='/') cp+="/";
					cp+=ref;
					pn=[ins+":"+cp,this.findNode(form.instances[ins].dom,ref,contextNode)];
				}
			} else {
				// ref==""
				//			alert ("Оставляю "+contextPath);
				pn=[contextPath,contextNode];
			}
		}
		if(doInsert || (doCreate && (!pn[1]))){
			pn[1]=this.makeNodeRecursive(form,ins,cp,form.instances[ins].dom);
		}
		return pn;
	},

makeNodeRecursive:function(form,insname,path,dom,newId){
		var schemaNode=this.getInstanceSchemaNode(form,insname+":"+path);
		if (!schemaNode) {this.msg('makeNodeRecursive: Для создания узла не найдена схема модели',path); return false;}
		var p=path.lastIndexOf('/'),ppath=(p>0)?path.substr(0,p):"/",pn=this.findNode(dom,ppath);
		if(!pn){if(ppath=='/'){this.msg('makeNodeRecursive: Странное поведение - не найден узел / в дереве');return false;}
			pn=this.makeNodeRecursive(form,insname,ppath,dom);
			if(!pn){this.msg('makeNodeRecursive: Странно. Узлы не создались для пути',insname+":"+ppath);return false;}
		}
		var cc,seq,child=dom.createElement(schemaNode.nodeName),init=schemaNode.getAttribute('init');
		pn.appendChild(child);
		if (init) {if(ie)child.text=init;else child.textContent=init;}
		seq=schemaNode.getAttribute('sequence');
		if (seq) {
			if (!newId) newId=this.getNewRowId();
			child.setAttribute('id',newId);
			path+="[@id="+newId+"]";
			cc=schemaNode.firstChild;
			while (cc) {this.makeNodeRecursive(form,insname,path+"/"+cc.nodeName,dom);cc=cc.nextSibling;}
		}
		return child;
	},

construct:function(e,form,iPath,mode,parentCtrl,parentNode,id){
		// e-js_layout element;  [0]-tagName [1]-attrs [2]-layout subelements
		// iPath - путь к данным c указанием имени экземпляра "inst1:/abc/def"
		// mode - 1:режим редактирования
		// parentCtrl - родительский контрол
		// parentNode - родительский узел (на который ссылается iPath)
		// id - идентификатор нового объекта
		if (typeof(e)!='object'){return e;}
		var tagName=e[0],n=parentNode,className,ref=e[1].ref,c,i,c,r,es,a=tagName.split(':'),pn;
		if (a.length==2){
			
			//getRefPathAndNode:function(form,contextNode,contextPath,ref,doCreate,doInsert,id)
			pn=this.getRefPathAndNode(form,parentNode,iPath,ref,mode,0,id);
			className=((a[0]=='forms')?"PF":a[0])+"."+a[1];
			eval("c=new "+className+"();");
			if (!c) {return "Не создан контрол для тэга "+tagName;}
			c.ctrlId=this.getNewCtrlId(form);
			c.form=form;
			c.e=e;
			c.iPath=pn[0];
			c.iNode=pn[1];
			c.parentCtrl=parentCtrl;
			c.id=id;
			c.className=className;
			c.tagName=tagName;
			c.childCtrls={};
			r=c.constructHtml(mode);
			if(parentCtrl){parentCtrl.childCtrls[c.ctrlId]=c;}
			c.form.ctrlById[c.ctrlId]=c;
			return r;
		} else {
			r="";
			for(i in e[2]){r+=this.construct(e[2][i],form,iPath,mode,parentCtrl,parentNode);}
			return r;
		}
	},
formInit:function(formId){
		var ins,iid,f=this.forms[formId],m=this.model_js[f.modelId];
		f.dom=this.createDOM();
		f.instances={};
		this._recurseBuildDOM(f.dom,f.dom,m); // Строим сначала общее дерево оригинала
		var n,i,sel=this.findNodes(f.dom,"/model/instance");
		n=this.rLen(sel);

		for(i=0;i<n;i++){ 
			ins=new PFD.Instance(f,this.rItem(sel,i));
			f.instances[ins.id]=ins;
		}
		this.refreshFeeds();
		return true;
	},

	//onreadystatechange:function(){PFD.refreshFeeds(1);},

resetAll:function(){for(var i in this.forms){this.formReset(i);}},
	_recurseBuildDOM:function(dom,p,m) {
		var a,i,attr,te,e=dom.createElement(m[0]);
		p.appendChild(e);
		for (a in m[1]){attr=dom.createAttribute(a);attr.value=m[1][a];e.setAttributeNode(attr);}
		if (m[2]!=''){te=dom.createTextNode(m[2]);e.appendChild(te);}
		if (m[3]!=undefined){for(i in m[3]) this._recurseBuildDOM(dom,e,m[3][i]);}
	},

updateFormMonitor:function(formId,instanceId){
		var ins,iname,d,s="",i,j,f=this.forms[formId],fm=P$.find("PsbFormMonitor_"+formId);
		if (fm==undefined) return;
		if(instanceId=='ctrls'){
			for(i in f.ctrlById){
				s+="<tr valign='top' bgcolor='#ffffff'><td class='pfds'><b>"+i+"</b><br>"+f.ctrlById[i].tagName+"</td><td colspan='2' class='pfds'>"+f.ctrlById[i].iPath+"<br>"+f.ctrlById[i].iNode.nodeName+"</td></tr>";
			}
			fm.innerHTML="Form controls:<table>"+s+"</table>";
			return;
		}
		if(instanceId){
			ins=f.instances[instanceId];
			iname="Instance '"+instanceId+"'";
			if(ins.dom){d=ins.dom;iname+=" loaded";} else {iname+="not loaded";}
		}else	{d=f.dom;iname="Main DOM";}
		if(d){fm.innerHTML=iname+"<table>"+this._getDataAsMonitorHTML(d,0,'#f8f8f8')+s+"</table>";}
		else{fm.innerHTML=iname+" is empty";}
	},
	// uses recursively by updateFormMonitor
	_getDataAsMonitorHTML:function(d,level,color) {
		var changed,insert,del,id,i,s,e,a,aa,l;
		aa=d.attributes;
		if(d.nodeType==1){
			changed=d.getAttribute('changed');
			del=d.getAttribute('del');
			id=d.getAttribute('id');
			ins=d.getAttribute('ins');
			if (changed==1) color=this.colors.changed;
			if (ins==1) {color=this.colors.inserted; if (changed==1) color=this.colors.inschanged;}
			if (del==1) color=this.colors.deleted;
			if (id==0) color=this.colors.defaults;
		}
		s="<tr valign='top' bgcolor='"+color+"'><td class='pfds'>";
		for (i=0;i<level;i++) s+="&nbsp;&nbsp;";
		s+="<b>"+d.nodeName+"</b></td><td class='pfds'><div style='width:100; overflow:hidden;'>";

		if(aa!=undefined){l=aa.length;if(aa.length!=0){for (i=0;i<l;i++) {a=aa.item(i);s+=" "+a.nodeName+"="+a.nodeValue;}}}
		s=s+"</div></td><td class='pfds'><div style='width:100; overflow:hidden;'>";e=d.firstChild;while(e){if(e.nodeType==3)s+=e.nodeValue;e=e.nextSibling;}s+="</div></td></tr>";
		if (changed==1) color=this.colors.ordinal;
		e=d.firstChild;while(e){if(e.nodeType==1)s+=this._getDataAsMonitorHTML(e,level+1,color);e=e.nextSibling;}
		return s;
	},

formReset:function(formId) {
		var c,i,f=this.forms[formId],fc=P$.find("PsbFormContainer_"+formId);
		if (fc==undefined){this.msg('Контейнер формы ['+formId+'] не найден');return;}
		if (f.hasInit==undefined) {if(this.formInit(formId))f.hasInit=true;}
		f.lastCtrlId=1;
		f.ctrlById={};
		fc.innerHTML="[FORM "+formId+" IS REFRESHING USING LAYOUT "+f.layoutId+"]";
		fc.innerHTML=this.construct(this.layout_js[f.layoutId],f,"",1);
		for(i in f.ctrlById){c=f.ctrlById[i];if(c.init)c.init();}
		this.updateFormMonitor(formId);
	},
	// construct:function(e,form,iPath,mode,parentCtrl,parentNode,id){

	/*
refreshControlsByPath:function(f,refreshPath){
	// Проход первый - помечаем контролы к обновлению или удалению
	
	var c,pp,pl=refreshPath.length,a=[];
	for(var p in f.ctrlByPath){f.ctrlByPath[p].mark=0;}
	for(var p in f.ctrlByPath){pp=p.substr(0,pl);if (pp==refreshPath){this.markRefresh(f.ctrlByPath[p],1);}}
	for(var p in f.ctrlByPath){c=f.ctrlByPath[p];if(c.mark==1){a.push(c);}}
	while(a.length){c=a.pop();this.msg("Regenerate "+c.ctrlId,c.dataPath);try{c.regenerate();}catch(e){this.msg("No regen");}}
},
*/
markRefresh:function(c,mark){
		if(c.mark==2)return;
		c.mark=mark;
		for (var i in c.childCtrls){
			this.markRefresh(c.childCtrls[i],2); // 2 - delete
		}
	},
removeControl:function(c){
		delete(c.form.ctrlById[c.ctrlId]);
		for (var id in c.childCtrls){this.removeControl(c.childCtrls[id]);}
		delete(c);
	},

stopLoad:function(ii){
		var a=ii.split("."),ins=this.forms[a[0]].instances[a[1]];
		//	this.unregisterLoader(ins);
		if(ins.loader)ins.loader.abort();
		//	if(ins.dom) ins.dom.loadXML("<stopped/>");
	},
registerLoader:function(ins){
		this.loaders[ins.form.formId+"."+ins.id]=ins;
		if(!this.loadTimer)this.loadTimer=window.setInterval("PFD.loadTimerCallback()",500);
	},
unregisterLoader:function(ins){
		delete this.loaders[ins.form.formId+"."+ins.id];
		if(!this.loaders.length){window.clearInterval(this.loadTimer);this.loadTimer=0; }	
	},
loadTimerCallback:function(){
		this.refreshFeeds();
	},
refreshFeeds:function(callSubscribers){
		var d,v,i,j,ins,f,s2,s="",c,t,e,ii,status,ok;
		for(i in this.forms){
			f=this.forms[i];
			//		s+="<tr><td><a href='javascript:;' onClick='PFD.updateFormMonitor(\""+i+"\",\"ctrlpaths\");'>"+i+"</td><td>Control paths</a></td></tr>";
			s+="<tr><td><a href='javascript:;' onClick='PFD.updateFormMonitor(\""+i+"\",\"ctrls\");'>"+i+"</td><td>Controls</a></td></tr>";
			s+="<tr><td><a href='javascript:;' onClick='PFD.updateFormMonitor(\""+i+"\");'>"+i+"</td><td>Main DOM</a></td></tr>";
			for(j in f.instances){
				ins=f.instances[j];
				d=(ins.loader)?ins.loader:ins.dom;
				t=d.readyState;
				s2=ins.src;
				ii=f.formId+"."+ins.id;
				c="#e0e0e0";
				if(s2){
					c=(t==3)?"#ff6600":(t==2)?"#00ff80":(t==1)?"#80a0ff":c;
					if(t==4){
						e=d.parseError;
						ok=true;
						if(e && e.errorCode){s2+="<br/>"+e.reason; c="#ff4040";ok=false;}
						if(d.status!=200){s2+="<br/>Load error:"+d.status;ok=false;}
						if(ins.loading)ins.loadComplete(ok);
					}else{
						s2+="<a href='javascript:;' onClick='PFD.stopLoad(\""+ii+"\");'>[Stop]</a>";
					}
				} else {s2="Model";}
				
				ii="<a href='javascript:;' onClick='PFD.updateFormMonitor(\""+i+"\",\""+j+"\");'>"+ii+"</a>";
				s+="<tr style='font-size:9px;' bgcolor='"+c+"'><td>"+ii+" ("+t+")</td><td>"+s2+"</td></tr>";
				
			}
		}
		v=P$.find("PsbFeedMonitor_"+f.formId);
		v.innerHTML="<table width='300'>"+s+"</table>";
	},
xml:function(n){
		var s="",a,i,l,r;
		if(n.nodeType==1){
			r=n.attributes;l=r.length;s+="<"+n.nodeName;for(i=0;i<l;i++){a=r[i];s+=" "+a.nodeName+'="'+a.nodeValue+'"';}
			r=n.childNodes;l=r.length;if(l){s+=">";for(i=0;i<l;i++){s+=this.xml(r[i]);}s+="</"+n.nodeName+">";}else s+="/>";}else s+=n.nodeValue;
		return s;
	}
};

P$.on('load',function(){PFD.resetAll();});


// Class 'PFD.Instance' - экземпляр данных
// Свойства:
//	.id            - instanceId из описания модели
//	.modelInstance - узел объявления этого экземпляра в модели
//	.loader        - XHTTPLoader
//	.src           - откуда загружать
//	.reqNodeset    - дерево, отправляемое при загрузке данных (необязательно)
//	.dom           - если не используется страничный режим загрузки (paging), то содержит все данные 
//	.paging        - признак использования постраничной загрузки
//	.pageSize      - размер страницы в строках
//	.pages[]       - страницы
//	УДАЛЕНО .sequences[][] - загружаемые "последовательности" строк типа Instance.sequences['basket/row'][0..n]
//      которые содержат в себе вложенные Instance со своими dom и вложенными sequence
//  .rows{} - агруженные данные строк параметр_хэша: ид строки, значение: ее данные
//	.form          - форма
// Методы:
//	.loadData(pos) - загружает страницу из src с позиции pos, если это постраничная загрузка
P$.assign(PFD.Instance.prototype,{
loadData:function(pos){
		if(PFD.loaders[this.form.formId+"."+this.id]!=undefined)return;
		var src="";
		if(this.pageSize){src+="pageSize="+this.pageSize+((from)?"&pos="+pos:"");}
		src=this.src+((src)?"?"+src:"");
		
		if(this.reqNodeset){with(this.loader){open("POST",src,true);
				setRequestHeader("Content-Type", "text/plain; charset=utf-8");
				this.loading=true;
				send(PFD.xml(PFD.findInstanceNode(this.form,this.reqNodeset)));}
		}else{with(this.loader){open("GET",src,true);this.loading=true;send(null);}}
		PFD.registerLoader(this);
	},
loadComplete:function(ok){
		var s,nn,n,dp,z,y,d=this.loader,from,to,i,j,p1,p2,pp,rsn;
		PFD.unregisterLoader(this);
		this.loading=false;
		try{
			if (ie){z=PFD.createDOM();if(!z.loadXML(d.responseText)){PFD.msg(d.responseText,"",1);ok=false;}}
			else {dp=new DOMParser();z=dp.parseFromString(d.responseText,"text/xml");delete dp;}
		}catch(e){ok=false;}
		
		if(this.paging){
			rsn=PFD.findNode(z,"/rowset");
			if(!rsn){PFD.msg("Instance("+this.id+").loadComplete: Не найден узел /rowset",this.src,1);return;}
			this.total=Int(rsn.getAttribute("total"));
			// ВНИМАНИЕ: Общее число строк равно тому количеству, которое я узнал сейчас при загрузке страницы

			if(!this.rows){
				this.rows={};
				this.pages=[];
				y=this.dom.createElement("rowset");
				this.dom.appendChild(y);
			}else{y=this.dom.firstChild;}
			
			from=Int(rsn.getAttribute("from"));
			to=Int(rsn.getAttribute("to"));
			PFD.msg("Instance("+this.id+").loadComplete: ["+from+"..."+to+"] / "+this.total);
			n=rsn.firstChild;
			while(n){
				nn=n.cloneNode(true);
				this.rows[n.getAttribute("id")]=y.appendChild(nn);
				//PFD.msg("--[@"+nn.nodeName+"="+n.getAttribute("id")+"] ok");
				n=n.nextSibling;
			}
			pp=this.pages;
			pp.push([from,to]);
			// Склейка пересекающихся страниц
			n=1;
			while(n){
				n=0;
				for(i in pp){
					if(n)break;
					p1=pp[i];
					for(j in pp)if(i!=j){
						p2=pp[j];
						if((p2[0]>=p1[0])&&(p2[0]<p1[1])){
							p1[0]=p2[0];
							delete(pp[j]);
							n=1;
							break;
						}
					}
				}
			}
			s="";
			for(i in pp){p1=pp[i];s=s+"["+p1[0]+'..'+p1[1]+"]";}
			PFD.msg("Instance("+this.id+").loadComplete: All pages are: "+s+" / "+this.total);
		}else{
			if(this.dom){delete(this.dom);}
			this.dom=z;
		}
		
		//	PFD.refreshControlsByPath(this.form,this.id+":");
	}
});// assign




PF={Control:function(){}};
PF.Control.prototype={
init:function(){for(id in this.childCtrls){c=this.childCtrls[id];if(c.init)c.init();}},
childChanged:function(){this.parentCtrl.childChanged();},
focus:function(){for(var c in this.childCtrls)if(this.childCtrls[c].focus())return 1;}
};




/* BENT

//   converts '/foo/bar' to '5:/foo/bar' if '5' is a first instance in model

CPFD.prototype.calcChangedForms=function(){
	var f,a,instance,schema,ipath;
	// сначала делаем calc
	
	for(var i in this.changedInstances){
		if(this.changedInstances[i]){
			a=i.split(",");//a[0]-formId a[1]-instanceId
			f=this.forms[a[0]];
			instance=this.findNode(f.dom,'/model/instance[@id='+a[1]+"]");
			ipath=a[1]+":";
			schema=this.findNode(f.dom,'/model/schema[@id='+instance.getAttribute('schema')+']');
			this.calcInstanceBySchema(f,schema,ipath);
			this.changedInstances[i]=0;
		}
	}
	for(var i in this.changedForms){
		if (this.changedForms[i]) {f=this.forms[i];this.updateFormMonitor(f);this.changedForms[i]=0;}}
}

CPFD.prototype.updateControlByIpath=function(form,ipath,value,state){
	var n;
	//if (value==undefined){n=this.findInstanceNode(ipath);if(!n)return false;value=n.text;}
	var info=this.data2ctrl[form.formId][ipath];
// 0-form, 1-callback on setvalue 2-ctrlId 3-model node 4-instanceId 5-instancePath 6-control(init after any use)
//  if (state) alert (var_dump(state));
if (info) {if (info[1]!=undefined){info[1](info,value,state);}}else{alert('Control for '+ipath+' not found');}
}
CPFD.prototype.calcInstanceBySchema=function(form,schema,ipath) {
	var n,c,cc,s,id;
	
	n=this.findInstanceNode(form.dom,ipath);
	if (schema.getAttribute('sequence')) {
		c=schema.firstChild;
		while(c){
			s=this.concatPath(ipath,c.nodeName);
			this.calcInstanceBySchema(form,c,s);
			c=c.nextSibling;
		}	
		return;
	} else if (schema.getAttribute('array')) {
		c=schema.firstChild; // row sequence
		if (!n) return;
		n=n.firstChild;
		if (!n) return;
		while (n) {
			id=n.getAttribute('id');
			this.calcInstanceBySchema(form,c,this.concatPath(ipath,c.nodeName+'[@id='+id+']'));
			n=n.nextSibling;
		}
		return;
	} 
	
	var r,c,state,calculate,type,needUpdate=false,n=this.findInstanceNode(form.dom,ipath);
	if (!n) {n=this.buildInstanceNode(form.dom,ipath);}
s=undefined;
	type=schema.getAttribute('type'); 
	calculate=schema.getAttribute('calculate'); 
	if (calculate) {s=this.eval(form,schema,ipath,calculate,type);if (n.text!=s) {needUpdate=true;n.text=s;}}
	// check for errors
	c=schema.firstChild;
	state={};
	while(c) {
		if (c.nodeName=='error') {
			needUpdate=1;
			r=this.eval(form,schema,ipath,c.getAttribute('if'),"boolean");
			if(r){if(!state.errors)state.errors=[];state.errors.push(c.text);}
		}
		if (c.nodeName=='warning') {
			needUpdate=1;
			r=this.eval(form,schema,ipath,c.getAttribute('if'),"boolean");
			if(r){if(!state.warnings)state.warnings=[];state.warnings.push(c.text);}
		}
		if (c.nodeName=='hide') {
			needUpdate=1;
			r=this.eval(form,schema,ipath,c.getAttribute('if'),"boolean");
			if(r){state.hide=1;}
		}
		if (c.nodeName=='disable') {
			needUpdate=1;
			r=this.eval(form,schema,ipath,c.getAttribute('if'),"boolean");
			if(r){state.disable=1;}
		}
		c=c.nextSibling;
	}
	if (needUpdate) this.updateControlByIpath(form,ipath,s,state);
}

CPFD.prototype.eval=function(form,schema,ipath,s,type) {
	var parent_ipath=ipath;
	var p=ipath.lastIndexOf('/');
	if (p>0) parent_ipath=ipath.substr(0,p);
s=s.replace(this.evalre1,"PFD.eval_$1\(form,schema,parent_ipath,");
s=s.replace(this.evalre2,"PFD.eval_path(form,schema,parent_ipath,'$1',type)");
return eval(s);	
}
CPFD.prototype.eval_path=function(form,schema,ipath,s,type){
	var n=this.findInstanceNode(form.dom,this.concatPath(ipath,s));
	if (type=='float') {if (n) return parseFloat(n.text); else return 0;}
	if (type=='integer') {if (n) return parseInt(n.text); else return 0;}
	if (type=='boolean') {if (n) return n.text; else return false;}
//	if (type=='integer') {if (n) return parseInt(n.text); else return 0;}
	if (n) return n.text; else return "";
}

CPFD.prototype.userChangedForm=function(formId,instanceId) {
	if (this.updateInterval) window.clearTimeout(this.updateInterval);
	this.changedForms[formId]=1;
	this.changedInstances[formId+","+instanceId]=1;
	this.updateInterval=window.setTimeout("PFD.calcChangedForms()",500);
}

CPFD.prototype.CC=function(control){	
	var mid,info,p,id=control.getAttribute('id');//,ref=control.getAttribute('ref');
	var a=id.split(":"); // ctrl_formId_formCtrlId
	
	info=this.ctrl2data[a[1]][id];
// 0-form, 1-callback on setvalue 2-ctrlId 3-model node 4-instanceId 5-instancePath 6-control(init after any use)
if (!info) {	alert ("Элемент "+a+" не подписан на данные");return false;}
info[3].setAttribute('changed',1);
var pc=info[3].parentNode;
	if (pc){//pc.setAttribute('changed',1);
	}

	info[3].text=control.value;
	this.userChangedForm(info[0].formId,info[4]); // formId,instanceId
}

*/

/*buildInstanceNode:function (f,path,newId) {
	var schemaNode=this.getInstanceSchemaNode(f,path); //
	var r=this.extractPathElements(path);
	
if (!schemaNode) {this.msg('buildInstanceNode: Схема не найдена для пути к экземпляру данных',path); return;}
	var pPath=this.getParentPath(path);
	var pNode=this.findInstanceNode(f,pPath);
	if (!pNode) {
		pNode=this.buildInstanceNode(f,pPath);
		if (!pNode) {this.msg("buildInstanceNode: Родитель не создался",pPath,1);}
	}
	
	var child=f.instances[r[1]].dom.createElement(schemaNode.nodeName),init=schemaNode.getAttribute('init');
	pNode.appendChild(child);
	if (init) {if(document.all)child.text=init;else child.textContent=init;}
	var seq=schemaNode.getAttribute('sequence');
	if (seq) {
		if (!newId) newId=PFD.getNewRowId();
		child.setAttribute('id',newId);
		path+="[@id="+newId+"]";
		var cc=schemaNode.firstChild;
		while (cc) {this.buildInstanceNode(f,this.concatPath(path,cc.nodeName));cc=cc.nextSibling;}
	}
	return child;
},
*/

