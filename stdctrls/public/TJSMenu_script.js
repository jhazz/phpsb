function ParseImgName (tObj,name1,name2,namen)
  {
  var i,s,sa,v,a=ParseImgName.arguments;
  for (i=1;i<a.length;i++)
    {
    v=a[i]; s=eval("tObj."+v); if (!s) {continue;}
    sa=s.split (",");
    sa[0]=SkinURL + "/" + sa[0];
    eval("tObj."+v+"_img="+((sa[0])?"\" border=0 src='"+sa[0]+"' "+((sa[1])?" width="+sa[1]+" height="+sa[2]:"")+"\"":"\"\"")+";");
    eval("tObj."+v+"_src='"+((sa[0])?sa[0]:"")+"';");
    if (sa[1]){eval("tObj."+v+"_w="+sa[1]+"; tObj."+v+"_h="+sa[2]+";");}
    tmp=new Image();
    tmp.src=sa[0];
    document._pi_[document._pi_.length]=tmp;
    }

  }

function TMainMenu(Name,Data,RootContextID,RootID,CheckData)
  {
  var sarr,s,j,i,k,id,ctx,pctx,v,vv,item0;
  this.Items=new Array();
  this.Built=false;
  this.Name=Name;
  this.Build=TMainMenu_Build;
  this.ReArrange=TMainMenu_ReArrange;
  this.DUID="TMM_"+MainMenus.length;
  this.root=new TMenuItem(this,RootContextID+":root:"+RootID+":MainMenu_"+Name);
  for (i=0;i<Data.length;i++)
    {
    s=Data[i]; sarr=s.split ("|",100);
    for (j=0;j<sarr.length;j++)
      {
      tmp=new TMenuItem (this,sarr[j],pctx);
      if (tmp.LocalContextID) {pctx=tmp.LocalContextID;}
      }
    }
  for (i=0;i<this.Items.length;i++)
    {
    item0=this.Items[i];
    item0.hasChild=false;
    ctx=item0.ContextID; id=item0.ID;
    if (item0._at)
      {
      sarr=item0._at.split("/");
      ctx=sarr[0];
      id=sarr[1]; if (!id) id=0;
      }
    for (j=0;j<this.Items.length;j++)
      {
      item1=this.Items[j];
      if ((item1.ContextID==ctx)&&(item1.ParentID==id))
        {
        if ((!item1.Parent)&&(item0.ContextID==RootContextID))
          {
          item1.Parent=item0;
          item0.Childs[item0.Childs.length]=item1;
          item0.hasChild=true;
          }
        }
      }
    }

  if (CheckData)
    {
    for (i=0;i<CheckData.length;i++)
      {
      s=CheckData[i]; sarr=s.split (",");
      for (j=0;j<sarr.length;j++)
        {
        v=sarr[j]; vv=v.split (":",2);
        for (k=0;k<this.Items.length;k++)
          {item0=this.Items[k]; if ((item0.ContextID==vv[0])&&(item0.ID==vv[1])){item0.Checked=true; break;}}
        }
      }
    }
  MainMenus[MainMenus.length]=this;
  }


function TMenuItem(AMainMenu,Args,PrevContext)
  {
  var sarr; var p; var i; var pids;
  this.Highlight=TMenuItem_Highlight;
  sarr=Args.split ('@',100);
  if (sarr.length>1)
    {
    for (i=1;i<sarr.length;i++)
      {
      s=sarr[i]; epos=s.indexOf('=');
      eval ("this._"+s.slice (0,epos)+"=\""+s.slice (epos+1)+"\";");
      }
    }
  ParseImgName (this,"_i","_hi");
  if (sarr[0])
    {
    pids=sarr[0].split (":",4);
    this.ContextID=pids[0];
    this.ParentID=pids[1];
    this.ID=pids[2];
    this.Caption=pids[3];
    if (this.ContextID=="") {this.ContextID=PrevContext;} else {this.LocalContextID=this.ContextID;}
    } else {return false;}
  this.Level=0;
  this.MainMenu=AMainMenu;
  this.Childs=new Array();
  i=AMainMenu.Items.length;
  this.DUID=AMainMenu.DUID+"_"+i;
  MainMenu_Items[this.DUID]=this;
  AMainMenu.Items[i]=this;
  return this;
  }


function TMenuStyle_Init(menustyle)
  {
  this.FontStyleText=((this.Font)?'font-family:'+this.Font+';':'')+
  ((this.FontSize)?'font-size:'+this.FontSize+';':'')+
  ((this.FontWeight)?'font-weight:'+this.FontWeight+';':'')+
  ((this.nFontColor)?'color:'+this.nFontColor+';':'')+
  ((this.FontDecoration)?'text-decoration:'+this.FontDecoration+';':'');
  }

function TMainMenu_Build(StyleName,Level,MyParent,CGI_page)
  {
  if (!Level) {Level=0;}
  if (Level>MAINMENU_MAXLEVELS) {return;}
  if (!MyParent) {MyParent=this.root;}
  var i,s,item0,style,s2,isLast;
  tmp=new TMenuStyle(StyleName,Level);
  tmp.Init=TMenuStyle_Init;
  tmp.Init(tmp);

  style=tmp;
  style.CGI_page=CGI_page;
  style.urlsuffix=URLSuffix; //".html";

  ParseImgName (style,"hDrillPic","nDrillPic","SepPic","CheckPicOn","CheckPicOff","nSelPicL","hSelPicL","nSelPicR","hSelPicR",
     "DecorT","DecorTL","DecorTR","DecorL","DecorR","DecorB","DecorBL","DecorBR","BgPic","nBgPic","hBgPic","CutPic","Spacer");
  // Open poplayer
  if (Level)
    {document.write ("<div align='left' style='z-index:30; position:absolute; left:0; top:0; visibility:hidden;' id='"+MyParent.DUID+"poplayer'>");}
  // Mousedetect table
  s="<table border=0 cellpadding=0 cellspacing=0"+((style.MenuWidth)?(" width='"+style.MenuWidth)+"'":"") +"><tr><td onMouseOver='MMD_HoldSelection()' onMouseOut='MMD_MouseOut()' >";
  // DECOR TABLE
  s+="<table width='100%' border=0 cellpadding=0 cellspacing=0><tr>";
  if (style.DecorTL)
    {s+="<td><table width='100%' border=0 cellspacing=0 cellpadding=0>"+
    "<tr><td align='left'><img "+style.DecorTL_img+"></td><td background='"+style.DecorT_src+"' width='100%'><img "+style.DecorT_img+
    "></td><td align='right'><img "+style.DecorTR_img+"></td></tr></table></td></tr><tr>";
    }
  if (style.DecorL)
    {
    s+="<td><table width='100%' border=0 cellpadding=0 cellspacing=0><tr><td background='"+style.DecorL_src+"'><img height=1 width="+style.DecorL_w+" src='"+style.Spacer_src+"'></td>";
    }
  s+="<td "+((style.BgColor)?" bgcolor='"+style.BgColor+"'":"")+((style.BgPic)?" background='"+style.BgPic_src+"'":"")+" width='100%'>"+"<table width='100%' border=0 cellpadding=0 cellspacing="+style.CellSpacing+">";
  document.write(s);

  if (!style.Vertical) {document.write ("<tr valign='"+style.VAlign+"'>");}

  var isLast=false;
  for (i=0;i<MyParent.Childs.length;i++)
    {
    item0=MyParent.Childs[i];
    item0.style=style;
    if (i==(MyParent.Childs.length-1)) {isLast=true;}
    if ((item0._rk)&&((ResourceKeys)&&(!ResourceKeys[item0._rk]))) continue;

    s="<td "+((style.nBgPic)?"background='"+style.nBgPic_src+"'":"")+" style='cursor:hand' id='"+item0.DUID+"scritem' "+((style.nBgColor)?" bgcolor='"+style.nBgColor+"'":"")+
      " onMouseOver='MMD_MouseOver(\""+item0.DUID+"\")'>"+
      "<table width='100%' border=0 cellspacing=0 cellpadding='"+style.CellPadding+"'><tr valign='"+style.VAlign+"'>";

    if (item0.Caption=="-")
      {s+="<td><table border=0 cellpadding=0 cellspacing=0 width='100%'><tr><td background='"+style.CutPic_src+"'><img width=1 height="+style.CutPic_h+" src='"+style.Spacer_src+"'></td></tr></table></td>";
      item0.inactive=true;
      }
    else
      {
      u=(item0._v)?item0._v : item0.ContextID+"/"+item0.ID;
      u=(item0._u)?item0._u:(item0.style.CGI_page+"/"+u+item0.style.urlsuffix);

      s+=((style.nSelPicL)?"<td><img id='"+item0.DUID+"selpicL'"+style.nSelPicL_img+"></td>":"")+
      ((style.CheckPicOn)?("<td><img "+((item0.Checked)?style.CheckPicOn_img:style.CheckPicOff_img))+"></td>":"")+
      "<td width='100%' align='"+style.HAlign+"' onClick='location.href=\""+((item0._u)?item0._u:(item0.style.CGI_page+"/"+item0.ContextID+"/"+item0.ID+item0.style.urlsuffix))+"\";'><table border=0 cellspacing=0 cellpadding=0 width='"+style.CellWidth+"' height='"+style.CellHeight+"'><tr  valign='bottom'><td align='"+style.HAlign+"'>"+
      "<a id='"+item0.DUID+"alink' href='"+u+"' onMouseOver='MMD_MouseOver(\""+item0.DUID+"\")' style='"+style.FontStyleText+"'"+((item0.style.css)?" class='"+item0.style.css+"'":"")+">"+
       ((item0._i && (!style.HideImages))?"<img align='"+style.IMGAlign+"' id='"+item0.DUID+"img' "+item0._i_img+">"+((style.IMGAlign=="nl")?"<br>":""):"")+
       item0.Caption+"</a></td></tr></table></td>"+
      ((item0.hasChild)?"<td align='right'><img id='"+item0.DUID+"drill' "+style.nDrillPic_img+"></td>":"<td></td>")+
      ((style.nSelPicR)?"<td><img id='"+item0.DUID+"selpicR'"+style.nSelPicR_img+"></td>":"");
      }
    s+="</td></tr></table>";

    s2="";
    if ((style.SepPic)&&(!isLast)) {s2="<td><img "+style.SepPic_img+"></td>";}
    if (style.Vertical)
      {s="<tr valign='top'>"+s+"</tr>";
      if (s2!="") {s+="<tr>"+s2+"</tr>";}
      }
    else {s+=s2;}
    document.writeln(s);
    if (!item0.inactive)
      {
      item0.scritem=P$.find(item0.DUID+"scritem");
      item0.drill=P$.find(item0.DUID+"drill");
      item0.alink=P$.find(item0.DUID+"alink");
      item0.selpicL=P$.find(item0.DUID+"selpicL");
      item0.selpicR=P$.find(item0.DUID+"selpicR");
      if (item0._i && (!style.HideImages)) {item0.img=P$.find(item0.DUID+"img");}
      }
    }
  if (!style.Vertical) {document.write ("</tr>");}
  document.writeln ("</table>");
  // Close decor
  s=((style.DecorL)?"</td><td background='"+style.DecorR_src+"'><img height=1 width="+style.DecorR_w+" src='"+style.Spacer_src+"'></td></tr></table></td></tr>":"")+
    ((style.DecorBL)?"<tr><td colspan=3><table width='100%' border=0 cellspacing=0 cellpadding=0><tr><td align='left'><img "+
      style.DecorBL_img+"></td><td width='100%' background='"+style.DecorB_src+"'><img "+style.DecorB_img+"></td><td align='right'><img "+
      style.DecorBR_img+"></td></tr></table></td></tr>":"");
  document.writeln (s+"</table>");
  // Close mousedetect
  document.write("</td></tr></table>");
  // Close poplayer
  if (Level)
    {
    document.write ("</div>");
    MyParent.poplayer=P$.find(MyParent.DUID+"poplayer");
    }
  for (i=0;i<MyParent.Childs.length;i++)
    {
    item0=MyParent.Childs[i];
    if (item0.hasChild) {this.Build(StyleName,Level+1,item0,CGI_page); }
    }
  }

function TMainMenu_ReArrange(Level,AItem,DrillLeft)
  {
  if (!Level) {Level=0;}
  if (Level>MAINMENU_MAXLEVELS) {return;}
  if (!AItem) {AItem=this.root;}
  var i,item0,x,y;
  if (AItem.hasChild)
    {
    if (AItem.poplayer)
      {
      if (Level==1)
        {
        var rpos=new GetAbsXY(AItem.scritem);
        x=rpos.x+AItem.scritem.offsetWidth;
        y=rpos.y+AItem.scritem.offsetHeight;
        xl=rpos.x;
        }
      else
       {
        xl=AItem.scritem.offsetLeft+AItem.Parent.poplayer_newx;
        x=xl+AItem.scritem.offsetWidth-AItem.style.OverlapX;
        y=AItem.scritem.offsetTop+AItem.scritem.offsetHeight+AItem.Parent.poplayer_newy;
        }
      if (AItem.style.Vertical)
        {
        if (DrillLeft)
          {if ((xl-AItem.poplayer.offsetWidth)<0) {DrillLeft=false;}
          }
        else
          { if ((x+AItem.poplayer.offsetWidth)>MAINMENU_PAGE_WIDTH) {DrillLeft=true; } }

        if (DrillLeft) {x=xl-AItem.poplayer.offsetWidth+AItem.style.OverlapX;}
         else {x-=AItem.style.OverlapX;}
        y=y-AItem.scritem.offsetHeight-AItem.style.CellSpacing-AItem.style.OverlapY;
        }
      else
       {x=x-AItem.poplayer.offsetWidth+AItem.style.CellSpacing-AItem.style.OverlapX;
        y=y-AItem.style.OverlapY;
        }

      if (x<0) {x=0;}
      if ((x+AItem.poplayer.offsetWidth) > MAINMENU_PAGE_WIDTH) {x=MAINMENU_PAGE_WIDTH-AItem.poplayer.offsetWidth;}
      AItem.poplayer.style.left=x;
      AItem.poplayer.style.top=y;
      AItem.poplayer_newx=x;
      AItem.poplayer_newy=y;
      }
    for (i=0;i<AItem.Childs.length;i++)
      {
      item0=AItem.Childs[i];
      if (item0.hasChild) {this.ReArrange(Level+1,item0,DrillLeft); }
      }
    }
  this.Arranged=true;
  }

function TMenuItem_Highlight (doHighlight,Level)
  {
  var fontColor,bgColor,DrillSrc,SelPicLSrc,SelPicRSrc,ImgSrc,vis,filter='';
  if (!Level) {Level=0;}
  if (Level>MAINMENU_MAXLEVELS) {return;}
  this.highlighted=doHighlight;
  if (!this.MainMenu.Arranged) {this.MainMenu.ReArrange();}

  if (this.scritem)
    {
    if (this._i) {ImgSrc=this._i_src;}
    if (doHighlight)
      {
      fontColor=this.style.hFontColor;
      bgColor=this.style.hBgColor;
      bgPic=this.style.hBgPic_src;
      DrillSrc=this.style.hDrillPic_src;
      SelPicLSrc=this.style.hSelPicL_src;
      SelPicRSrc=this.style.hSelPicR_src;
      vis="visible";
      if ((Level>1)&&(this.style.FadeInactive)) {filter='alpha(opacity=80)';}
      if (this._hi) {ImgSrc=this._hi_src;}
      }
    else
      {fontColor=this.style.nFontColor;
      bgColor=this.style.nBgColor;
      bgPic=this.style.nBgPic_src;
      DrillSrc=this.style.nDrillPic_src;
      SelPicLSrc=this.style.nSelPicL_src;
      SelPicRSrc=this.style.nSelPicR_src;
      vis="hidden";
      }
    if (!bgColor) {bgColor="";}
    this.scritem.style.backgroundColor=bgColor;

    this.scritem.style.backgroundImage=(bgPic)?"url("+bgPic+")":"";

    if (this.img) {this.img.src=ImgSrc;}
    this.alink.style.color=fontColor;
    if (this.drill) {this.drill.src=DrillSrc;}
    if (this.selpicL) {this.selpicL.src=SelPicLSrc;}
    if (this.selpicR) {this.selpicR.src=SelPicRSrc;}
    }
  if (this.poplayer)
    {this.poplayer.style.visibility=vis;
    if (ie) {this.poplayer.style.filter=filter;}
    }
  if (this.Parent) {this.Parent.Highlight(doHighlight,Level+1);}
  }

function MMD_ReArrange(){for(var i=0;i<MainMenus.length;i++) {MainMenus[i].ReArrange();}}
function MMD_MouseOver(DUID)
  {
  var item0=MainMenu_Items[DUID];
  if (!item0) {alert("Unknown object DUID ["+DUID+"]"); return;}
  if ((MainMenu_hItem)&&(MainMenu_hItem!=item0)&&(MainMenu_hItem.Level>=item0.Level)){
    MainMenu_hItem.Highlight(false);
  }
  if (!item0.highlighted) {item0.Highlight(true);}
  if (item0.poplayer) { item0.poplayer.style.visibility="visible";}
  MainMenu_hItem=item0;
//  window.status=item0.Caption;
  if (MainMenuTimer) {window.clearTimeout (MainMenuTimer); MainMenuTimer=false;}
  }
function MMD_MouseOut() {
  MainMenuTimer=window.setTimeout("MMD_Close()",MAINMENU_TIMEOUT_OFF);
  }
function MMD_Close(){
  if (MainMenu_hItem) {MainMenu_hItem.Highlight(false);}
  MainMenu_hItem=false;
  MainMenuTimer=false;
}
function MMD_HoldSelection(){
  if (MainMenuTimer) {window.clearTimeout (MainMenuTimer); MainMenuTimer=false;}
}


// VARS DEFINITION
var tmp,MainMenu_hItem,MainMenuTimer,MenuReady=false;
var MainMenus=new Array();
var MainMenu_Items=new Array();
