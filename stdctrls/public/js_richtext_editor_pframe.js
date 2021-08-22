var toolbar='undo,redo,|,copy,cut,paste,bold,italic,insertunorderedlist,insertorderedlist,|,table,image,link';
var para_tags='|P|H1|H2|H3|H4|H5|H6|H7|BLOCKQUOTE|';
var block_tags='|TABLE|TR|TD|DIV|BODY|OL|UL|';
var img_edit_panel=false;
var link_edit_panel=false;
var textstyle_focused=false;

function pframe_toolbar2html(commands)
  {
  var c,ca,s;
  s="";
  var cs=commands.split(',');
  for (i in cs)
    {
    c=cs[i]; ca=_ACTIONS[c]; if (!ca) {ca=c;}
    if (c=='|') {s+="<td bgcolor='#c0c0c0'><img src='"+imgurl+"/"+"spacer.gif' width='8' height='16'></td>"; continue;}
    s+="<td bgcolor='#c0c0c0'><a href='javascript:EditFrame.execCmd(\""+c+"\",false,true)'><img alt='"+ca+"' border=0 src='"+imgurl+"/"+c+".gif'></a></td>";
    }
  return s;
  }

var SelectedStyleTD=false;
function pframe_OnStyle_MouseOver()
  {
  if (SelectedStyleTD)
    {
    with (SelectedStyleTD)
      {
      style.backgroundColor=style.oldbg; //'#f0f0f8';
      style.color=style.oldcolor; //'#f0f0f8';
      }
    }
  SelectedStyleTD=event.srcElement;
  if (SelectedStyleTD.tagName!="TD")
    {
    SelectedStyleTD=SelectedStyleTD.parentElement;
    }
  with (SelectedStyleTD)
    {
    style.oldbg=style.backgroundColor;
    style.oldcolor=style.color;
    style.backgroundColor='#000080';
    style.color='#ffffff';
    }
  }


function GetAbsXY(elem) {
  this.x=elem.offsetLeft;
  this.y=elem.offsetTop;
  while (elem.offsetParent != null) {
    elem=elem.offsetParent;
    this.x+=elem.offsetLeft;
    this.y+=elem.offsetTop;
    if (elem.tagName == 'BODY') break;
  } return this;
}



function pframe_SetTextStyle(style)
  {
  para_style_selector.style.visibility="hidden";
  EditFrame.eframe_SetTextStyle(style);
  }

function pframe_ShowTextStyleList()
  {
  var p=GetAbsXY(textstyle_dropbox);
  with(para_style_selector)
    {
    style.visibility='visible';
    style.left=p.x;
    style.top=p.y+textstyle_dropbox.offsetHeight;
    }
  }

function pframe_ShowAnchorStyleList()
  {
  var p=GetAbsXY(anchor_style_dropbox);
  with(anchor_style_selector)
    {
    style.visibility='visible';
    style.left=p.x;
    style.top=p.y + anchor_style_dropbox.offsetHeight;
    }
  }

function pframe_DisplayTextStyle(el)
  {
  pframe_HideProperties();

  var i,c,tn,tnn,ss="--",stylename=RT_UNFORMATTED,tf;
  textstyle_focused=false;
  for (i=0;i<5;i++)
    {
    tn=el.tagName;
    tnn="|"+tn+"|";
    if (block_tags.indexOf(tnn)!=-1) { break;}
    if (para_tags.indexOf(tnn)!=-1)
      {
      ss=tn;
      c=el.className;
      if ((c!="undefined")&&(c)) {ss+="."+c;}

      tf=TextFormats[ss];
      if (tf) {stylename=ss; textstyle_focused=el;}
      break;
      }
    el=el.parentElement;
    if (!el) {break;}
    }
  if (textstyle_focused)
    {
    textstyle_dropbox.value=ss;
    }
  else
    {
    textstyle_dropbox.value='--';
    }
  }
function pframe_EnumTextStyle(tagfamily,show_unformat)
  {
  var s="",tc,i,ss,count=0;
  var tt,tn;
  if (show_unformat)
    {
    s="<tr><td bgcolor='#707070' style='cursor:hand' onClick='pframe_SetTextStyle(\"\")' onMouseOver='pframe_OnStyle_MouseOver(\""+i+"\")'>"+RT_UNFORMATTED+"</td></tr>";
    }
  for (i in TextFormats)
    {
    tt=i.split ('.');
    tn=tt[0].toUpperCase();
    if (tn=='') {continue;}
    if (tagfamily.indexOf("|"+tn+"|")==-1) {continue;}
    tc=tt[1];

    ss=" bgcolor='#808080' style='cursor:hand' onClick='pframe_SetTextStyle(\""+i+"\")' onMouseOver='pframe_OnStyle_MouseOver(\""+i+"\")'";
    ss="<td "+ss+"><"+tn+" "+((tc)?" class='"+tc+"'":"")+">"+TextFormats[i]+"</"+tn+"></td>";
    s+="<tr>"+ss+"</tr>";
    count++;
    if (count>5)
      {
      s+="</table></td><td bgcolor='#d0d0d0'><table cellspacing='1' cellpadding='0' border='0'>";
      count=-10;
      }
    }
  s="<table border='0' cellpadding='4' cellspacing='0'><tr valign='top'><td bgcolor='#d0d0d0'><table cellspacing='1' cellpadding='0' border='0'>"+s+"</table></td></tr></table>";
  return s;
  }

function pframe_EnumTextStyleForDropBox(tagfamily)
  {
  var tt,tn;
  var s="<option value='--'>"+RT_UNFORMATTED+"</option>",tc,i;
  for (i in TextFormats)
    {
    tt=i.split ('.');
    tn=tt[0].toUpperCase();
    if (tn=='') {continue;}
    if (tagfamily.indexOf("|"+tn+"|")==-1) {continue;}
    if (tt[1]) {tn=tn+'.'+tt[1];}
    s+="<option value='"+tn+"'>"+TextFormats[i]+"</option>";
    }
  return s;
  }


function pframe_ShowLinkProperties(link,name,style)
  {
  link_edit_panel.style.display='block';
  P$.find('link_edit_link').value=link;
  P$.find('link_edit_name').value=name;
  anchor_style_dropbox.value=style;
  }

function pframe_UpdateLinkProperties()
  {
  EditFrame.eframe_UpdateLinkProperties(
    P$.find('link_edit_link').value,
    P$.find('link_edit_name').value);
  }


function pframe_ShowImgProperties(w,h,hspace,vspace,link,name)
  {
  pframe_HideProperties();
  img_edit_panel.style.display='block';
    P$.find('img_edit_hspace').value=hspace;
    P$.find('img_edit_vspace').value=vspace;
    P$.find('img_edit_width').value=w;
    P$.find('img_edit_height').value=h;
    P$.find('img_edit_link').value=link;
    P$.find('img_edit_name').value=name;

  }

function pframe_UpdateImgProperties()
  {
  EditFrame.eframe_UpdateImgProperties(
    P$.find('img_edit_width').value,
    P$.find('img_edit_height').value,
    P$.find('img_edit_hspace').value,
    P$.find('img_edit_vspace').value,
    P$.find('img_edit_link').value,
    P$.find('img_edit_name').value);
  }

function pframe_HideProperties()
  {
  img_edit_panel.style.display='none';
  link_edit_panel.style.display='none';
  para_style_selector.style.visibility='hidden';
  anchor_style_selector.style.visibility='hidden';
  }


function pframe_ClosePopup()
  {
  popupBox.style.visibility='hidden';
  }
function pframe_PopupHighlight(el,on)
  {
  color='#000000'; bgColor='#c0c0c0';
  if (on) {color='#ffffff';bgColor='#000080'; }
  el.style.color=color; el.style.backgroundColor=bgColor;
  }

function pframe_DrawPopup(x,y,cmds)
  {
  var maxy=window.document.documentElement.offsetHeight;
  var maxx=window.document.documentElement.offsetWidth
  y+=60;

  var cmda,i,s,c;
  cmda=cmds.split (',');
  s="";
  for (i in cmda)
    {
    c=cmda[i];
    if (c=='|')
      {
      s+="<tr><td colspan='2' bgColor='#c0c0c0'><hr></td></tr>"; continue;
      }
    ca="<img border='0' src='"+imgurl+"/"+c+".gif' width='21' height='20' align='absmiddle'>"+_ACTIONS[c];
    s+="<tr><td nowrap style='cursor:hand; font-size:10px; font-family:Verdana,Arial; font-weight:bold; color:#000000;' bgColor='#c0c0c0' onMouseOut='pframe_PopupHighlight(this,false);' onMouseOver='pframe_PopupHighlight(this,true);' onClick='pframe_ClosePopup(); EditFrame.execCmd(\""+c+"\"); event.returnValue=false; return false;'>"+ca+"</td></tr>";
    }
  if (s!="")
    {
    s="<table border='2' cellpadding='0' cellspacing='0'><tr><td><table border='0' cellpadding='0' cellspacing='0'>"+s+"</table></td></tr></table>";
    popupBox.innerHTML=s;
    maxx-=popupBox.offsetWidth+30;
    maxy-=popupBox.offsetHeight+20;
    if (x>maxx) {x=maxx;}
    if (y>maxy) {y=maxy;}

    popupBox.style.left=x; popupBox.style.top=y;
    setTimeout("popupBox.style.visibility='visible'; ",10);
    }
  }