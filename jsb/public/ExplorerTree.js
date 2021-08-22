//
//  (c)2004 SibDesign Center. JhAZZ
//

var NavTrees=new Array(),NavTree_Items=new Array();

var NavTree_MAXLEVELS=5;
var NavTree_TIMEOUT_OFF=300;
var tmp;
var NavTree_hItem;
var NavTreeTimer;
var ExtEvent;

var CurrentDUID=false;
var BrowserViewMode=0; // 1-date, 2-layout
var ExpandedItems;
var DragMode=false,DragPot,DragItem=false;
var MyStatus;
var OpenedModal_action,OpenedModal_params;
//var OpenedDialog,OpenedDialog_action,OpenedModal_params,CheckDialogTimer;
var EditMode;

// -- Global browser dependent functions --

function Int(v) {return v.valueOf();}

function PopupBrowserEdit(el){
  var s="";
  if ((Layouts)&&(!NoLayouts))
    {
    s+="?"+STRINGS.CAPTION_CHANGE_LAYOUT_TO+"|";
    for (i in Layouts) s+="!chlay!"+i+"!"+Layouts[i]+"|";
    s+="-|";
    } else {s="";}
  OpenActionsPopupMenu(el,s+"recycle|hide|unhide",true);
}
function PopupBrowserContexts(el){
  if (!ContextLocked) {
    for (i in Contexts) {if(s!="") s+="|"; s+="!chctx!"+i+"!"+Contexts[i];}
  }
  if (EditMode) {if (s) s+="|-|"; s+="insaft"}
	OpenActionsPopupMenu(el,s,true);
  
}

function PopupBrowserView(el){
  var s="view0|view1|view2";
  if (!ShowHidden) {s+="|-|showhid";} else {s+="|-|hidehid";}
	OpenActionsPopupMenu(el,s,true);
}

function EditTreeItem (DUID,useButton){
  var item0=NavTree_Items[DUID];
  if (!item0) {return;}
  CurrentDUID=DUID;
	OpenActionsPopupMenu((useButton)?item0.scritem:ExtEvent,"?"+item0.Caption
	+"|ren|-|insbef|insaft|insinsd|-|"+((item0._at)?"de_attpage":"attpage")
	+"|"+((item0._v)?"de_virtpage":"virtpage")+"|-|dup|sethome",useButton);
}

function OpenActionsPopupMenu(el,actionsList,useButton){
	var items=[],a,i,al=actionsList.split("|");

	for (i in al) {
		a=al[i];
		if (a=="-") {
			items.push({cap:"-"});
		}else{
			if (a.charAt(0)=="?"){// подзаголовок
				cap=a.substr(1);
				items.push({cap:"?<b>"+cap+"</b>"});
			}else{
				if (a.charAt(0)=="!"){
					p=a.split ("!");
					items.push({cap:p[3],cb:"DoAction",cba:p[2],act:p[1]});
				}else{
					items.push({cap:TActsC[a],cb:"DoAction",act:a});
				}
			}
		}
	}

	P$.openPopupMenu(el,items,useButton);
	ExtEvent=false;
	
}

function CreatePopupmenu (Acts)
  {
  if (!Acts) return;
  function PopupObject(id2) {this.l=P$.find(id2+"_l"); this.c=P$.find(id2+"_c"); this.items=new Array();}
  var cap,act,id;
  id="TNTPopup:"+Popups.length;
  var s="<div id='"+id+"_l' style='z-index:20000; position:absolute; visibility:hidden;' align='right'>\
<table border=0 cellspacing=0 cellpadding=1>\
<tr><td nowrap class='pop_frame'><table><tr><td class='pop_head' id='"+id+"_c'></td></tr></table>\
<table width='100%' border=0 cellspacing=0 cellpadding=0><tr><td class='pop_cell' nowrap>\
<table width='100%' border=0 cellspacing=0 cellpadding=3>";
  al=Acts.split("|");
  for (i in al)
    {
    a=al[i];
    if (a=="-") s+="<tr><td><table cellspacing='0' width='100%'><tr><td bgcolor='#888888'></td></tr></table></td></tr>";
    else
      {
      if (a.charAt(0)=="?")
        {cap=a.substr(1);s+="<tr><td class='jsb_tiny'><b>"+cap+"</b></td></tr>";}
      else
        {
        if (a.charAt(0)=="!")
          {p=a.split ("!");act="DoAction(\""+p[1]+"\",\""+p[2]+"\")";cap=p[3];}
        else
          {
          act="DoAction(\""+a+"\")"; cap=TActsC[a];}
        s+="<tr><td id='"+id+":"+i+"' onMouseOver='NPopup_Over(this);' onMouseOut='NPopup_Out();' style='cursor:hand' class='jsb_menuitem' onclick='"+act+"; return false;' nowrap>"+cap+"</td></tr>";
        }
      }
    }
  s+="</table></td></tr></table></td></tr></table></div>";
  document.write (s);

  newPopup=new PopupObject(id);
  for (i in al) {newPopup.items[i]=P$.find(id+":"+i);}
  Popups[Popups.length]=newPopup;
  return newPopup;
  }


function SetViewMode (newmode)
  {
  if (newmode!=BrowserViewMode)
    {
    BrowserViewMode=newmode;
    for (i=0;i<NavTrees.length;i++)
      {
      tree=NavTrees[i];
      for (j=0;j<tree.Items.length;j++)
        {
        item0=tree.Items[j];
        s="???";
        switch (newmode)
          {
          case 0: s=item0._date; break;
          case 2: s=item0._vc; break;
          case 1: if (item0.ContextID=='layouts')
            {s="[tmpl]"; }
             else
              {s=item0._lay;
              if (s) {s=Layouts[s];}
              }
            break;
          }
        if (item0.info) {item0.info.innerHTML=s;}
        }
      }
    }
  return false;
  }

function InitEditor(aEditMode)
  {
  document.write ("<div id='MyStatus'></div>\
     <div onMouseOver='event.returnValue = false;' id='dragpotlayer' style='visibility:hidden;position:absolute;'></div>");
  DragPot=P$.find("dragpotlayer");
  MyStatus=P$.find("MyStatus");
  document.onmousedown=HTree_MouseDown;
  document.onmousemove=HTree_MouseOver;
  document.onkeypress=HTree_OnKeyPressed;
  document.onmouseup=HTree_MouseUp;
  document.oncontextmenu=HTree_ContextMenu;
  if (ie)
    {
    document.onselectstart=function () {event.returnValue = false;}
    document.ondragstart=function() {event.returnValue = false;};
    }
  }

function encode_vars(arr)
  {
  var v,s="",s1;
  var re=/\&|=|\?|\:/g;
  for (v in arr) {
    if (arr[v]==undefined) continue;
    s1=arr[v].toString();
    s+=((s)?"&":"")+v+"="+s1.replace (re,function (str) {return "%"+str.charCodeAt(0).toString(16);});
    }
  return s;
  }

function ActionDialog(url,action,w,h,x,y,params)
  {
  OpenedModal_action=action;
  OpenedModal_params=params;
  url+="?action="+action;
  if (params==undefined) params={};
  params.url=url;
  params.w=w; params.h=h; params.callback="ActionCallback";
  W.openModal(params);
  }

function ActionCallback(mr)
  {
  if (!mr) return;
  if (typeof(mr)=="string")
    {
    var p=mr.indexOf("\n");
    if (p!=-1) mr=mr.substr(0,p);
    }
  DoAction ("do_"+OpenedModal_action,mr);
  }
function DoAction (act,param)
  {
  function oneof(act,v) {return (("|"+v+"|").indexOf("|"+act+"|")!=-1);}
  if (oneof(act,"view0|view1|view2")) {SetViewMode(Number(act.substr(4,1))); return;}
  var item0=false,itemid=0,itemctx=SysContext;
  if (CurrentDUID) {item0=NavTree_Items[CurrentDUID]; itemid=item0.ID; itemctx=item0.ContextID;}

  if (oneof (act,"attpage|virtpage"))
    {
    ActionDialog(SelectPageURL,act,400,500,200,100,{ContextSelectable:1, SysContext:itemctx,JSBPageID:itemid});
    return;
    }

  if (oneof(act,"ren|insbef|insaft|insinsd|dup"))
    {
    ActionDialog(EditPageURL,act,500,400,200,100,{SysContext:itemctx,JSBPageID:itemid});
    return;
    }

  var telist=elist=clist=tclist="";
  for (i=0;i<NavTrees.length;i++)
    {
    tree=NavTrees[i];
    elist=clist="";
    for (j=0;j<tree.Items.length;j++)
      {item0=tree.Items[j];

      if (item0.Checked)
        {
        if (clist=="") {clist=tree.RootContext+":";} else {clist=clist+",";}
        clist+=String(item0.ID);
        }

      if (item0.hasChild)
        {obj=item0.poplayer;
        if ((obj)&&(obj.style.display=="block"))
          {
          if (elist=="") {elist=tree.RootContext+":";} else {elist=elist+",";}
          elist+=String(item0.ID);
          }
        }
      }
    if (elist) {telist+=elist+"|";}
    if (clist) {tclist+=clist+"|";}
    }

    PassVars={action:act,JSBPageID:itemid,
    SysContext:itemctx,
    ShowHidden:ShowHidden,
    EditMode:EditMode,
    BrowserViewMode:BrowserViewMode,
    NoLayouts:NoLayouts,
    ContextSelectable:ContextSelectable,
    ContextLocked:ContextLocked};
  if (telist) {PassVars["ExpandedItems"]=telist;}
  if (tclist) {PassVars["Checked"]=tclist;}
  if (param) {if (typeof (param)=="object") for (k in param) {PassVars[k]=param[k];} else {PassVars["Parameter"]=param;}}

  if (oneof(act,"sethome|do_insinsd|do_insaft|do_insbef|do_ren|do_dup|putbef|putaft|putinsd|recycle|unhide|hide|chlay|do_attpage|do_virtpage|de_attpage|de_virtpage"))
    {
    if (oneof(act,"unhide|hide|chlay|recycle"))
      {
      if (!tclist)
        {
        alert (STRINGS.MSG_NO_SELECTED_PAGES);
        }
      }
    PassVars['action']=act;
    if (act.substr(0,3)=="do_")
      {
      for (k in OpenedModal_params) {PassVars[k]=OpenedModal_params[k];}
      }
    if (act.substr(0,3)=="put")
      {
       PassVars['JSBPageID1']=DragItem.ID;
       PassVars['JSBPageID2']=itemid;
      }
    window.parent.frames['transaction'].location.href=TransactURL+"?"+encode_vars(PassVars);
    return;
    }

  if (oneof(act,"showhid|hidehid|chctx"))
    {
    if (act=="showhid") {PassVars["ShowHidden"]=1;}
    if (act=="hidehid") {PassVars["ShowHidden"]=0;}
    if (act=="chctx") {PassVars["SysContext"]=param;}
    location.href=MyURL+"?rand="+Math.random()+"&"+encode_vars(PassVars);
    }
  return false;
  }


function expand (DUID)
  {
  var item0=NavTree_Items[DUID];
  var obj=item0.poplayer;
  var src;
  var s=obj.style.display;
  var img=item0.plus;
  if (s=="none") {s="block"; src=item0.style.tree_m_src;} else {s="none";src=item0.style.tree_p_src;}
  obj.style.display=s;
  img.src=src;
  }

function set_check_state(MyParent,newstate,Level)
  {
  function redraw_check(item)
    {
    var bgColor,chimg;
    if (item.Checked) {bgColor=item.style.selBgColor; chimg=item.style.tree_chkon;}
      else {bgColor=item.style.nBgColor; chimg=item.style.tree_chkoff; if (!bgColor) {bgColor="";} }
    if (item.scritem)
      {
      item.scritem.style.backgroundColor=bgColor;
      item.alink.style.color=item.style.nFontColor;
      item.check.src=chimg;
      }
    }
  var i,item0;
  if (!Level) {Level=0;}
  if (Level>NavTree_MAXLEVELS) {return;}

  MyParent.Checked=newstate;
  redraw_check(MyParent);

  if (MyParent.hasChild)
    {
    for (i=0;i<MyParent.Childs.length;i++)
      {
      item0=MyParent.Childs[i];
      if (item0.readonly) {continue;}
      item0.Checked=newstate;
      redraw_check(item0);
      if (item0.hasChild)
        {
        set_check_state(item0,newstate,Level+1)
        }
      }
    }
  }

function checkout (DUID)
  {
  var item0=NavTree_Items[DUID];
  if ((item0.Parent)&&(item0.Parent.Checked)) {return;}
  set_check_state (item0,(!item0.Checked));
  }

function AttachDummy()
  {
  this._atdummy=1;
  }
function TNavTreeStyle(AStyleName,MenuLevel)
  {
  // menulevel==0 - Top level of menu

  var p=ImagesURL;
  this.Vertical=true;
  this.Font='Arial,Verdana,Helvetica';
  this.FontSize="10px";
  this.FontDecoration='none';
  this.FontWeight='bold';
  this.Spacer=p+"sp.gif";

  this.CellPadding=2;
  this.SepPic=p+"horline.gif";
  this.BgColor=false; 
  this.nBgColor=false; 
  this.hBgColor='#F07F0A';
  this.parent_hBgColor='#f0e0ff';
  this.nFontColor='black';
  this.hFontColor='white';
  this.nFontColorHidden='#b080b0';
  this.selBgColor='#fff0e0';

  this.tree_itemh=20;
  this.tree_itemw=20;
  this.tree_m=p+'tree_m.gif';
  this.tree_p=p+'tree_p.gif';
  this.tree_h=p+'tree_h.gif';
  this.tree_h2=p+'tree_h2.gif';
  this.tree_v=p+'tree_v.gif';
  this.tree_f=p+'tree_f.gif';
  this.tree_fref=p+'tree_fref.gif';
  this.tree_pref=p+'tree_ref.gif';
  this.tree_page=p+'tree_page.gif';
  this.tree_action=p+'tree_action.gif';
  this.tree_chkon=p+'tree_chkon.gif';
  this.tree_chkoff=p+'tree_chkoff.gif';
  this.tree_hidpage=p+'tree_page-hid.gif';
  this.home=p+'jsb_home.gif';
  this.sp=p+'sp.gif';
  this.wh=" border=0 width='"+this.tree_itemw+"' height='"+this.tree_itemh+"' align='absmiddle' ";

  this.FontStyleText=((this.Font)?'font-family:'+this.Font+';':'')+
  ((this.FontSize)?'font-size:'+this.FontSize+';':'')+
  ((this.FontWeight)?'font-weight:'+this.FontWeight+';':'')+
  ((this.FontDecoration)?'text-decoration:'+this.FontDecoration+';':'');

  this.FontStyleTextHidden=this.FontStyleText+((this.nFontColorHidden)?'color:'+this.nFontColorHidden+';':'');
  this.FontStyleText+=((this.nFontColor)?'color:'+this.nFontColor+';':'');

  }

function ParseImgName (tObj,name1,name2,namen)
  {
  var i,s,sa,v,a=ParseImgName.arguments;
  for (i=1;i<a.length;i++)
    {
    v=a[i]; s=eval("tObj."+v); if (!s) {continue;}
    sa=s.split (",");
    eval("tObj."+v+"_img="+((sa[0])?"\" border=0 src='"+sa[0]+"' "+((sa[1])?" width="+sa[1]+" height="+sa[2]:"")+"\"":"\"\"")+";");
    eval("tObj."+v+"_src='"+((sa[0])?sa[0]:"")+"';");
    if (sa[1]){eval("tObj."+v+"_w="+sa[1]+"; tObj."+v+"_h="+sa[2]+";");}
    }

  }

function TNavTree(Name,Data,RootContext,RootID,TargetURL,TargetFrame,UseQueryMethod)
  {
  var sarr,s,j,i,k,id,ctx0,v,vv,item0,ctx1,PrevContext;

  this.TargetURL=TargetURL;
  this.TargetFrame=TargetFrame;
  this.UseQueryMethod=UseQueryMethod;
  this.Items=new Array();
  this.Built=false;
  this.Name=Name;
  this.Build=TNavTree_Build;
  this.DUID="TNT:"+NavTrees.length;
  this.RootContext=RootContext;
  this.root=new TNavTreeItem(this,RootContext+":root:"+RootID+":NavTree_"+Name);

  PrevContext="";
  for (i=0;i<Data.length;i++) {
    s=Data[i]; sarr=s.split ("|");
    for (j=0;j<sarr.length;j++)
      {
      tmp=new TNavTreeItem (this,sarr[j],PrevContext);
      if (tmp.LocalContextID) {PrevContext=tmp.LocalContextID;}
      }
    }

  s="";
  for (i in this.Items)
    {
    item0=this.Items[i];
    item0.hasChild=false;
    if ((item0.ID==0) && (item0.ID==item0.ParentID))continue;
    
		if (item0.ID==item0.ParentID) {
    	alert ("Self referenced item '"+item0.Caption+"' !\n"+item0.ID+"=>"+item0.ParentID); 
    	continue;
		}
    ctx0=item0.ContextID; 
    id=item0.ID;
   
    if (item0._at)
      {
      sarr=item0._at.split("/"); ctx0=sarr[0]; id=sarr[1];
      var item1=new AttachDummy();
      item0.hasChild=true;
      item1.Parent=item0;
      item0.Childs[item0.Childs.length]=item1;
      }

    for (j in this.Items)
      {
      item1=this.Items[j];
      ctx1=item1.ContextID;
      if ((ctx0==ctx1)&&(Number(item1.ParentID)==Number(id)))
        {
        item0.hasChild=true;
        item1.Parent=item0;
        item0.Childs[item0.Childs.length]=item1;
        }
      }
    }
  document.write (s);

  // Load visibility states
  if (ExpandedItems)
    {
    trs=ExpandedItems.split ("|");
    for (i=0;i<trs.length;i++)
      {
      if (trs[i]=="") {break;}
      ctxids=trs[i].split(":");
      ContextID=ctxids[0];
      PIDs=ctxids[1].split (",");
      for (j=0;j<PIDs.length;j++)
        {
        PageID=PIDs[j];
        for (k=0;k<this.Items.length;k++)
          {item0=this.Items[k]; if ((item0.ContextID==ContextID)&&(item0.ID==PageID)){item0.Expanded=true; break;}}
        }
      }
    }
  NavTrees[NavTrees.length]=this;
  }

function TNavTreeItem(ANavTree,Args,PrevContext)
  {
  // Args="11:2222:3333:My title@u=foo.html@t=_blank@i=tri.gif"
  // Main arg:
  // 11- context, 2222-parent id, 3333-menu item id, 'My title'- caption
  //
  // Additional args:
  // _u - URL
  // _t - target
  // _i - image
  // _hi - hover image
  // _at - attach tree [ctx:rootid]
  // _v  - virtual substitution
  var sarr; var p; var i; var pids;
  this.Highlight=TNavTreeItem_Highlight;
  sarr=Args.split ('@',100);
  if (sarr.length>1)
    {
    for (i=1;i<sarr.length;i++)
      {
      p=sarr[i].split ('=',2);
      eval ("this._"+p[0]+"=\""+p[1]+"\";");
      }
    }
  ParseImgName (this,"_i","_hi");
  if (sarr[0])
    {
    pids=sarr[0].split (":",4);
    this.ContextID=pids[0]; this.ParentID=pids[1]; this.ID=pids[2]; this.Caption=pids[3];
    this.MUID=pids[0]+":"+pids[1]+":"+pids[2];
    if (this.ContextID=="") {this.ContextID=PrevContext;} else {this.LocalContextID=this.ContextID;}
    } else {return false;}
  this.Level=0;
  this.NavTree=ANavTree;
  this.Childs=new Array();
  this.Checked=false;
  i=ANavTree.Items.length;
  this.DUID=ANavTree.DUID+"_"+i;
  NavTree_Items[this.DUID]=this;
  ANavTree.Items[i]=this;
  return this;
  }


function TNavTree_Build(StyleName,Level,MyParent,readonly,LastFlags)
  {
  var bgcolor;
  if (!LastFlags) {LastFlags=new Array();}

  if (!Level) {Level=0;}
  if (Level>NavTree_MAXLEVELS) {return;}
  if (!MyParent) {MyParent=this.root;}
  var i,j,s,item0,style,s2,ico;

  tmp=new TNavTreeStyle(StyleName,Level);
  style=tmp;

  ParseImgName (style,"tree_p","tree_m","tree_v","tree_h","tree_h2","tree_action","home");

  // Open poplayer
  if (Level)
    {
    if (MyParent.Expanded) {s="block";} else {s="none";}
    document.write ("<div id='"+MyParent.DUID+"poplayer' style='display:"+s+"'>");
    }

  document.write ("<table width='100%' border=0 cellpadding=0 cellspacing=0>");

  for (i=0;i<MyParent.Childs.length;i++)
    {
    item0=MyParent.Childs[i];
    item0.style=style;
    item0.readonly=readonly;


    if (item0._atdummy)
      {
      s="<tr><td valign='top'>";
      if (Level>0)
        {
        s2="";
        for (j=0;j<Level;j++)
          {
          s2=s2+"<img src='"+(((LastFlags[j]))?style.sp:style.tree_v)+"' "+style.wh+">";
          }
        s+=s2+"<img src='"+style.tree_fref+"' "+style.wh+">";
        }
      s+="<i class='jsb_tab'>&nbsp;"+MyParent._atinf+"</i></td></tr>";
      readonly=true;
      document.writeln(s);
      continue;
      }

    if (i==(MyParent.Childs.length-1)) {LastFlags[Level]=true;} else {LastFlags[Level]=false;}

    bgcolor=((style.nBgColor)?" bgcolor='"+style.nBgColor+"'":"");
    if ((JSBPageID==item0.ID)&&(SysContext==item0.ContextID)) bgcolor=" bgcolor='#fff0d0'";
    s="<tr valign='top' id='"+item0.DUID+":scritem' onMouseOver='NavTree_MouseOver(\""+item0.DUID+"\")' onMouseOut='NavTree_MouseOut(\""+item0.DUID+"\")'>"+
      "<td style='cursor:hand' "+bgcolor+">"+
      "<table width='100%' border=0 cellspacing=0 cellpadding=0>"+
      "<tr valign='top'><td class='jsb_tab'>";

    if (Level>0)
      {
      s2="";
      for (j=0;j<Level;j++)
        {
        s2=s2+"<img src='"+(((LastFlags[j]))?style.sp:style.tree_v)+"' "+style.wh+">";
        }
      s+=s2;
      }
    s+="<img border=0 src='"+((item0.hasChild)?((item0.Expanded)?style.tree_m:style.tree_p):((LastFlags[Level])?style.tree_h2:style.tree_h))+
      "' "+style.wh+((item0.hasChild)?" id='"+item0.DUID+":plus' onClick=\"expand ('"+item0.DUID+"')\"":"")+
      "><nobr>";

    if (item0.hasChild) {ico=style.tree_f;} else {ico=style.tree_page;}
    if (item0._v) {ico=style.tree_pref; }
    fs=style.FontStyleText;
    if ((item0._s==2)||((item0._s==3))) {
      fs=style.FontStyleTextHidden;
      ico=style.tree_hidpage;
      }

    s+="<img id='"+item0.DUID+":icon' src='"+ico+"' "+style.wh+">";
    s+="<span onClick='NavTree_Click(\""+item0.DUID+"\")' id='"+item0.DUID+":alink'";
    if (item0._v) {s+=" title='"+item0._virtinf+"'"; fs+="font-style:italic;";}
    s+=" onMouseOver='NavTree_MouseOver(\""+item0.DUID+"\")' style='"+fs+"'>"+
       ((item0._i)?"<img align='"+style.IMGAlign+"' id='"+item0.DUID+"img' "+item0._i_img+">":"");
    if (item0._v) {s+="-- ";}


    if (item0._lay)
      {
      var tmp;
      if (Layouts[item0._lay]) {
        tmp=Layouts[item0._lay].split("-",2);
      } else {
        tmp=["","Unknown"];
      }
      var l=tmp[0];
      s+="<font color='#808080'>"+l+" </font>";
      }
    if (item0._tmpl)
      {
      s+="<font color='#808080'>{"+item0._tmpl+"} </font>";
      }
    if (item0._home)
      {
      s+="<img "+style.home_img+">";
      }
    s+=item0.Caption;
    s+="</span>";

    s+="</nobr></td>";

    inf="";
    if (BrowserViewMode==1) {inf=Layouts[item0._lay];}
    if (BrowserViewMode==2) {inf=item0._date;}
    s+="</tr></table></td><td align='right'><table border=0 cellpadding=0 cellspacing=0><tr><td class='jsb_tab'><nobr><span id='"+item0.DUID+":info'>"+inf+"</span></nobr></td>";

    if (!readonly)
      {
      s+="<td class='jsb_tab' width='20'><img src='"+style.tree_chkoff+"' "+style.wh+" onClick='checkout(\""+item0.DUID+"\")' id='"+item0.DUID+":check'></td>\
       <td><span id='"+item0.DUID+":edit'><img "+style.wh+" onClick='EditTreeItem(\""+item0.DUID+"\",true);' border=0 src='"+item0.style.tree_action+"'></span></td>";
      }
    s+="</tr></table></td></tr>";
    document.writeln(s);
    if (item0.hasChild)
      {
      document.writeln ("<tr><td colspan='10'>");
      this.Build(StyleName,Level+1,item0,readonly,LastFlags);
      document.writeln ("</td></tr>");
      }

    item0.scritem=P$.find(item0.DUID+":scritem");
    item0.alink=P$.find(item0.DUID+":alink");
    item0.info=P$.find(item0.DUID+":info");
    item0.editbutton=P$.find(item0.DUID+":edit");
    if (!readonly) {item0.check=P$.find(item0.DUID+":check"); }
    if (item0.hasChild) {item0.plus=P$.find(item0.DUID+":plus");}
    if (item0._i) {item0.img=P$.find(item0.DUID+":icon");}
    }
  document.writeln ("</table>");

  // Close poplayer
  if (Level)
    {
    document.write ("</div>");
    MyParent.poplayer=P$.find(MyParent.DUID+"poplayer");
    }
  }


function TNavTreeItem_Highlight (doHighlight,Level)
  {
  var fontColor,bgColor,DrillSrc,SelPicLSrc,SelPicRSrc,ImgSrc,vis,filter='';
  if (!Level) {Level=0;}
  if (Level>NavTree_MAXLEVELS) {return;}
  this.highlighted=doHighlight;

  if (this.scritem)
    {
    if (this._i) {ImgSrc=this._i_src;}
    if (doHighlight)
      {
      fontColor=this.style.hFontColor;
      bgColor=this.style.hBgColor;
      if (Level>0) {bgColor=this.style.parent_hBgColor; fontColor='black';}
      if (this._hi) {ImgSrc=this._hi_src;}
      }
    else
      {
      fontColor=(this._s)?this.style.nFontColorHidden:this.style.nFontColor;
      bgColor=this.style.nBgColor;
      if (this.Checked) {bgColor=this.style.selBgColor;}
      }
    if (!bgColor) {bgColor="";}
    this.scritem.style.backgroundColor=bgColor;

    if (this.img) {this.img.src=ImgSrc;}
    this.alink.style.color=fontColor;
    }
  if (this.Parent) {this.Parent.Highlight(doHighlight,Level+1);}
  }


// -- NavTreeDispatcher
function NavTree_Click(DUID)
{
	var item0=NavTree_Items[DUID]; if (!item0) {return;}
	var tree=item0.NavTree;
	if (tree.TargetURL)
	{
		var url;
		if (tree.UseQueryMethod==1)
		{url=tree.TargetURL+((tree.TargetURL.charAt(tree.TargetURL.length-1)=='?')?"&":"?")+"SysContext="+item0.ContextID+"&JSBPageID="+item0.ID;}
		else
		{
			url=tree.TargetURL+"/"+item0.ContextID+"/"+item0.ID;
		}

		if (tree.TargetFrame)
		{
			tf=window.parent.frames[tree.TargetFrame];
			if (!tf) {tf=window.frames[tree.TargetFrame]; }
			if (tf)
			{
				tf.location.href=url;
			}
			else {alert ("Target frame "+tree.TargetFrame+" not found!");}
			window.close();
		}
		else
		{
			window.location.href=url;
		}
	}
	else {
		W.modalResult(item0.ContextID+"/"+item0.ID+"\n["+ContextName+"] "+item0.Caption);
	}
}

function NavTree_MouseOver (DUID)
  {
  var item0=NavTree_Items[DUID]; if (!item0) {return;}
  if ((NavTree_hItem)&&(NavTree_hItem!=item0)&&(NavTree_hItem!=item0.Parent))
    {
    NavTree_hItem.Highlight(false);
    }
  if (!item0.highlighted) {item0.Highlight(true);}
  NavTree_hItem=item0;
  if (NavTreeTimer) {window.clearTimeout (NavTreeTimer); NavTreeTimer=false;}
  }

function NavTree_MouseOut ()
  {
  NavTreeTimer=window.setTimeout("NavTree_Close()",NavTree_TIMEOUT_OFF);
  }

function NavTree_Close()
  {
  if (NavTree_hItem) {NavTree_hItem.Highlight(false);}
  NavTree_hItem=false;
  NavTreeTimer=false;
  }

function NavTree_HoldSelection()
  {
  if (NavTreeTimer) {window.clearTimeout (NavTreeTimer); NavTreeTimer=false;}
  }


// Document hook events for Drag'n'Drop -------------------------------------

function HTree_MouseUp(e)
  {
  ExtEvent=(e)?e:event;
  if (!ExtEvent.srcElement) {ExtEvent.srcElement=ExtEvent.target;}

  if (!DragMode) {return;}
  DragMode=false;
  DragPot.style.visibility="hidden";
  if (DragItem)
    {
    s=ExtEvent.srcElement.id;
    if (s.substr (0,4)=="TNT:")
      {
      a=s.split(":"); DUID=a[0]+":"+a[1];
      var item0=NavTree_Items[DUID];
      if (item0.readonly) {return;}
      if ((item0)&&(item0!=DragItem))
        {
        // check for parent
        ii=item0;
        for (i=0;i<10;i++)
          {
          ii=ii.Parent; if (!ii) {break;}
          if (ii==DragItem) {return;}
          }
        CurrentDUID=item0.DUID;
				OpenActionsPopupMenu(ExtEvent,"putbef|putaft|putinsd",false);
        }
      }
    }
  }

function HTree_OnKeyPressed()
  {
  if ((event.keyCode==27)&& (DragMode))
    {
    DragItem=DragMode=false;
    DragPot.style.visibility="hidden";
    HidePopup();
    }
  }
function HTree_MouseOver(e)
  {
  ExtEvent=(e)?e:event;
  if (!ExtEvent.srcElement) {ExtEvent.srcElement=ExtEvent.target;}

  if (DragMode)
    {
    if (ie)
      {
      if (ExtEvent.y<50) {document.body.scrollTop-=10;}
      if (ExtEvent.y>document.body.offsetHeight-50) {document.body.scrollTop+=10;}
      DragPot.style.left=ExtEvent.x+document.body.scrollLeft+5;
      DragPot.style.top=ExtEvent.y+document.body.scrollTop+5;
      }
    else
      {
      if (ExtEvent.y<50) {window.pageYOffset=Int(window.pageYOffset)-10;}
      if (ExtEvent.y>screen.height-50) {window.pageYOffset=Int(window.pageYOffset)+10;}
      DragPot.style.left=ExtEvent.x+Int(window.pageXOffset)+5;
      DragPot.style.top =ExtEvent.y+Int(window.pageYOffset)+5;
      }
    DragPot.style.visibility="visible";
    return true;
    }
  }

function HTree_MouseDown(e)
  {
  ExtEvent=(e)?e:event;
  if (!ExtEvent.srcElement) {ExtEvent.srcElement=ExtEvent.target;}

  var button=ExtEvent.button;
  if (!button) {button=1;}
  if (button==1)
    {
    s=ExtEvent.srcElement.id;
    if (s.substr (0,4)=="TNT:")
      {
      a=s.split(":"); DUID=a[0]+":"+a[1];
      var item0=NavTree_Items[DUID];
      if (!item0) {return;}
      if (item0.readonly) {return;}

      DragMode=true;
      DragItem=item0;
      DragPot.innerHTML="<img align='absmiddle' src='"+ImagesURL+"tree_page.gif'>"+item0.Caption;
      }
    }

  if (button==2)
    {
    s=ExtEvent.srcElement.id;
    if (s.substr (0,4)=="TNT:")
      {
      a=s.split(":");
      DUID=a[0]+":"+a[1];
      var item0=NavTree_Items[DUID];
      if (!item0) {return;}
      if (item0.readonly) {return;}
      EditTreeItem(DUID,false);
      }
    }

  return true;
  }

function HTree_ContextMenu(){
  return false;
  }
// Popup menus ----

NPopup_Selected=false;
function NPopup_Over(obj){
  obj.oldbg=obj.style.backgroundColor;
  obj.oldcol=obj.style.color;
  obj.style.backgroundColor="#ff8800";
  obj.style.color="ffffff";
  NPopup_Selected=obj;
}
function NPopup_Out(){
  if (NPopup_Selected) {NPopup_Selected.style.backgroundColor=NPopup_Selected.oldbg;
    NPopup_Selected.style.color=NPopup_Selected.oldcol;}
}

/*function var_dump(obj)
{var s=""; cnt=0;
 for (var i in obj)
    {cnt++; if (cnt>100) break;
    s+="."+i+"="+obj[i]+" | "} return s;
}
*/