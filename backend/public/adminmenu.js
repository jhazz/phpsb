//
//  (c)2002 Siberian Design Center. JhAZZ
//

function TMenuStyle(AStyleName,MenuLevel)
  {
  this.Vertical=true;
  this.FontDecoration='none';
  this.FontWeight='normal';
  this.FontSize="14px";
  this.Target="AdminFrame";
  this.OverlapY=0;
  this.OverlapX=0;
  this.VAlign="bottom";
  this.HAlign="left";
  this.IMGAlign="absbottom"; //  texttop,absmiddle,absbottom (combine with VAlign!!)
  this.FadeInactive=false;
  this.Spacer="sp.gif";
  this.hDrillPic="sp.gif,1,1";
  this.nDrillPic="sp.gif,1,1";
  this.Font='Arial Narrow';
  if (screen.width<1024) {  this.FontSize="10px";  }
  this.nDrillPic="tri1_on,8,8";
  this.hDrillPic="tri1_off.gif,8,8";
  P$.assign(this,SkinDefs.Defaults);

  if (AStyleName=="HeadMenu")
    {
    this.CellSpacing=0;
    this.CellPadding=3;
    this.CutPic="cut.gif";
    if (MenuLevel==0)
      {
      this.nFontColor='#606060';
      this.hFontColor='#801F9D';
      this.nBgColor=false;
      this.hBgColor='#d0d0d0';
      this.Vertical=false;
      this.nDrillPic="tri2_on.gif,8,8";
      this.hDrillPic="tri2_off.gif,8,8";
      this.SepPic="menu_v_separator.gif,2,20";
      this.OverlapY=0;
      this.OverlapX=-10;
      P$.assign(this,SkinDefs.Level0);
      }
    else
      {
      this.nDrillPic="tri1_on.gif,8,8";
      this.hDrillPic="tri1_off.gif,8,8";
      this.CellSpacing=0;
      this.CellPadding=3;
      this.CellHeight=20;
      this.nBgColor='#d0d0d0';
      this.hBgColor='#AB88B5';
      this.nFontColor='#000000';
      this.hFontColor='#ffffff';
      this.OverlapY=6;
      this.OverlapX=2;
      this.DecorR ="top_R.gif,10,50";
      this.DecorB ="top_B.gif,116,10";
      this.DecorBR="top_BR.gif,10,10";
      this.DecorL ="top_L.gif,10,200";
      this.DecorBL="top_BL.gif,10,10";

      this.DecorTR="top_TR.gif,10,10";
      this.DecorT ="top_T.gif,116,10";
      this.DecorTL="top_TL.gif,10,10";
      
      P$.assign(this,SkinDefs.Level1);
      if (MenuLevel<2)
        {
        this.DecorTR=this.DecorT=this.DecorTL=false;
        }
      }
  }
  this.FontStyleText=((this.Font)?'font-family:'+this.Font+';':'')+
  ((this.FontSize)?'font-size:'+this.FontSize+';':'')+
  ((this.FontWeight)?'font-weight:'+this.FontWeight+';':'')+
  ((this.nFontColor)?'color:'+this.nFontColor+';':'')+
  ((this.FontDecoration)?'text-decoration:'+this.FontDecoration+';':'');
  }

function ParseImgName (tObj,aurl,name1,name2,namen)
  {
  var i,s,sa,v,a=ParseImgName.arguments;
  for (i=2;i<a.length;i++)
    {
    v=a[i]; s=eval("tObj."+v); if (!s) {continue;}
    sa=s.split (",");
    sa[0]=aurl + sa[0];
    eval("tObj."+v+"_img="+((sa[0])?"\" border=0 src='"+sa[0]+"' "+((sa[1])?" width="+sa[1]+" height="+sa[2]:"")+"\"":"\"\"")+";");
    eval("tObj."+v+"_src='"+((sa[0])?sa[0]:"")+"';");
    if (sa[1]){eval("tObj."+v+"_w="+sa[1]+"; tObj."+v+"_h="+sa[2]+";");}
    tmp=new Image();
    tmp.src=sa[0];
    document._pi_.push(tmp);
    }
  }

function TMainMenu(Name,Data,RootContextID,RootID,CheckData)
  {
  var sarr,s,j,i,k,id,ctx,v,vv,item0;
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
      tmp=new TMenuItem (this,sarr[j]);
      }
    }

  for (i=0;i<this.Items.length;i++)
    {
    item0=this.Items[i];
    item0.hasChild=false;
    ctx=item0.ContextID; id=item0.ID;
    if (item0._at)
      {
      sarr=item0._at.split(":");
      ctx=sarr[0];
      id=sarr[1];
      }
    // look for kids of item0
    for (j=0;j<this.Items.length;j++)
      {
      item1=this.Items[j];
      if ((item1.ContextID==ctx)&&(item1.ParentID==id))
        {
        if ((!item1.Parent)&&(item0.ContextID==RootContextID))
          {
          // only current context has privilege to get kids
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

function TMenuItem(AMainMenu,Args)
  {
  var sarr,p,i,pids,s,s2,epos;
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
  ParseImgName (this,"","_i","_hi");
  if (sarr[0])
    {
    pids=sarr[0].split (":",4);
    this.ContextID=pids[0];
    this.ParentID=pids[1];
    this.ID=pids[2];
    this.Caption=pids[3];
    this.MUID=pids[0]+":"+pids[1]+":"+pids[2];
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


function TMainMenu_Build(StyleName,Level,MyParent,CGI_page,DesignMode)
  {

  if (!Level) {Level=0;}
  if (Level>MAINMENU_MAXLEVELS) {return;}
  if (!MyParent) {MyParent=this.root;}
  var i,s,item0,style,s2,isLast;

  tmp=new TMenuStyle(StyleName,Level);
  style=tmp;
  style.CGI_page=CGI_page;
  style.DesignMode=DesignMode;
  style.urlsuffix=".html";
  if (DesignMode)
    {
    style.urlsuffix=".html?DesignMode="+DesignMode;
    }
  ParseImgName (style,SkinURL+"/","hDrillPic","nDrillPic","SepPic","CheckPicOn","CheckPicOff","nSelPicL","hSelPicL","nSelPicR","hSelPicR",
     "DecorT","DecorTL","DecorTR","DecorL","DecorR","DecorB","DecorBL","DecorBR","BgPic","CutPic","Spacer");
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

    s="<td style='cursor:hand' id='"+item0.DUID+"scritem' "+((style.nBgColor)?" bgcolor='"+style.nBgColor+"'":"")+
      " onMouseOver='MMD_MouseOver(\""+item0.DUID+"\")'>"+
      "<table width='100%' border=0 cellspacing=0 cellpadding='"+style.CellPadding+"'><tr valign='"+style.VAlign+"'>";

    if (item0.Caption=="-")
      {s+="<td><table border=0 cellpadding=0 cellspacing=0 width='100%'><tr><td background='"+style.CutPic_src+"'><img width=1 height="+style.CutPic_h+" src='"+style.Spacer_src+"'></td></tr></table></td>";
      item0.inactive=true;
      }
    else
      {s+=((style.nSelPicL)?"<td><img id='"+item0.DUID+"selpicL'"+style.nSelPicL_img+"></td>":"")+
      ((style.CheckPicOn)?("<td><img "+((item0.Checked)?style.CheckPicOn_img:style.CheckPicOff_img))+"></td>":"")+
      "<td width='100%' align='"+style.HAlign+"' "+((item0._u)?"onClick='document.getElementById(\"AdminFrame\").src=\""+ActionURL+"/"+item0._u+"\";'":"")+"><table border=0 cellspacing=0 cellpadding=0 width='"+style.CellWidth+"' height='"+style.CellHeight+"'><tr  valign='bottom'><td align='"+style.HAlign+"'>"+
      "<a id='"+item0.DUID+"alink'  target='AdminFrame' href='"+((item0._u)?(ActionURL+"/"+item0._u):'javascript:;')+"' onMouseOver='MMD_MouseOver(\""+item0.DUID+"\")' style='"+style.FontStyleText+"'>"+
       ((item0._i)?"<img align='"+style.IMGAlign+"' id='"+item0.DUID+"img' "+item0._i_img+">":"")+
       item0.Caption+"</a></td></tr></table></td>"+
      ((item0.hasChild)?"<td align='right'><img id='"+item0.DUID+"drill' "+style.nDrillPic_img+"></td>":"")+
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
      if (item0._i) {item0.img=P$.find(item0.DUID+"img");}
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
    if (item0.hasChild) {this.Build(StyleName,Level+1,item0,CGI_page,DesignMode); }
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
      DrillSrc=this.style.nDrillPic_src;
      SelPicLSrc=this.style.nSelPicL_src;
      SelPicRSrc=this.style.nSelPicR_src;
      vis="hidden";
      }
    if (!bgColor) {bgColor="";}
    this.scritem.style.backgroundColor=bgColor;

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

// -- MMD
function MMD_ReArrange (){
  for (var i=0;i<MainMenus.length;i++) {MainMenus[i].ReArrange();}
  }
function MMD_MouseOver (DUID)
  {
  var item0=MainMenu_Items[DUID];
  if (!item0) {alert("Unknown object DUID ["+DUID+"]"); return;}
  if ((MainMenu_hItem)&&(MainMenu_hItem!=item0)&&(MainMenu_hItem.Level>=item0.Level))
    {
    MainMenu_hItem.Highlight(false);
    }
  if (!item0.highlighted) {item0.Highlight(true);}
  if (item0.poplayer) { item0.poplayer.style.visibility="visible";}
  MainMenu_hItem=item0;
  if (MainMenuTimer) {window.clearTimeout (MainMenuTimer); MainMenuTimer=false;}
  }

function MMD_MouseOut ()
  {
  MainMenuTimer=window.setTimeout("MMD_Close()",MAINMENU_TIMEOUT_OFF);
  }
function MMD_Close()
  {
  if (MainMenu_hItem) {MainMenu_hItem.Highlight(false);}
  MainMenu_hItem=false;
  MainMenuTimer=false;
  }
function MMD_HoldSelection()
  {
  if (MainMenuTimer) {window.clearTimeout (MainMenuTimer); MainMenuTimer=false;}
  }

//
// VARS DEFINITION

var MainMenus=new Array();
var MainMenu_Items=new Array();

var MAINMENU_MAXLEVELS=5;
var MAINMENU_TIMEOUT_OFF=100; // ms
var MAINMENU_PAGE_WIDTH=screen.width;
var tmp;
var MainMenu_hItem; // Hilighted menu item
var MainMenuTimer;  // setTimeout
var MenuReady=false;