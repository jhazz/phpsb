var editBox=false,pasteBox=false;
var editBoxRange=false;
var prevEditBoxRange=false;
var editBoxEventElement=false;
var popup_TabStyles=false;
var clean_rules={A:'className,title,href,relhref',
  IMG:'/,id,ImgID,border,alt,srcwidth,srcheight,width,height,src,relsrc,align,hspace,vspace',
  TBODY:'1',
  TH:'align,width,valign,colspan,rowspan',
  TABLE:'width,cellpadding,cellspacing,autoformat',
  TR:'valign',
  TD:'className,align,valign,width,colspan,rowspan',
  LI:'className',
  UL:'className',
  P:'className',BR:'/,className',
  OL:'className',
  B:'1',EM:'1',I:'1',STRONG:'1',H1:'1',H2:'1',H3:'1',H4:'1',H5:'1',H6:'1',SUB:'1',SUP:'1'};
var tables_to_format=new Array();
var nexttabid=0;
var ResizedImgsArray=new Array();
var EditingObject=false;

function body_onLoad()
  {
  editBox=P$.find('DIVeditBox');
  editBox.contentEditable= true;
  pasteBox=GetParentObj('DIVpasteBox');
  pasteBox.contentEditable= true;
  popup_TabStyles=P$.find('DIVpopup_tabstyles');
  editBox.focus();
  eframe_OnSelect();
  }

function GetParentObj(DUID) {return parent.document.getElementById (DUID);}
function eframe_OnSelect(el)
  {
  var r=editBox.document.selection.createRange();
  if (!el)
    {
    if (editBox.document.selection.type=='Control')
      {
      if (r.length==1) {el=r(0);}
      }
    else
      {
      el=r.parentElement();
      }
    }

  var tag=el.tagName;
  var tag1=(tag)?el.parentElement.tagName : false;


  if (tag=='IMG')
    {
    EditingObject=el;
    var p=el.parentElement;
    var link=(p.tagName=='A')? ((p.getAttribute('relhref'))?p.getAttribute('relhref'):p.getAttribute('href')): '';
    window.parent.pframe_ShowImgProperties(
      el.getAttribute('width'),
      el.getAttribute('height'),
      el.getAttribute('hspace'),
      el.getAttribute('vspace'),
      link,el.getAttribute('alt'));
    return;
    }

  if ((tag=='A')||(tag1=='A'))
    {
    if (tag1=='A') {el=el.parentElement;}

    EditingObject=el;
    var h=el.getAttribute('relhref');
    if (!h) {h=el.getAttribute('href');}
    window.parent.pframe_DisplayTextStyle(el);
    var s=el.tagName+((el.className)?"."+el.className:"");
    window.parent.pframe_ShowLinkProperties(h,el.getAttribute('title'),s);
    return;
    }

  window.parent.pframe_DisplayTextStyle(el);
  EditingObject=el;
  }

function eframe_DrawPopup(x,y,cmds)
  {
  parent.pframe_DrawPopup(x,y,cmds);
  }


function eframe_AfterMouseUp(x,y)
  {
  var el = editBoxEventElement;
  s="copy,cut,paste";

  var i;
  for (i=0;i<5;i++)
    {
    if (el.tagName=='IMG') {return;}
    if ((el.tagName=='TD')||(el.tagName=='TH'))
      {
      s+=",|,insertrow,insertrowbelow,insertcol,insertcolright,|,deleterow,deletecol,|,tabstyle";
      editBoxEventElement=el;
      break;
      }
    if (el.tagName=='TABLE')
      {
      s="tabstyle";
      editBoxEventElement=el;
      break;
      }
    if (parent.block_tags.indexOf("|"+el.tagName+"|")!=-1) { break;}
    el=el.parentElement;
    }
  eframe_StoreCursorPos();
  eframe_DrawPopup(x,y,s);
  }
function eframe_MouseUp()
  {
  eframe_ClosePopup();
  if (this.event.button==1)
    {
    eframe_OnSelect();
    return true;
    }
  editBoxEventElement=event.srcElement;
  setTimeout ('eframe_AfterMouseUp('+event.x+','+event.y+')',1);
  }

function eframe_InsertTable()
  {
  var r=editBox.document.selection.createRange();
  if (editBox.document.selection.type!='Control')
    {
    r.pasteHTML("<table align='center' id='justnewtable' width='100%' border='0'><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr></table>");
    eframe_SetTabAutoformat(P$.find("justnewtable"),1);
    }
  }

function jsb_cleanUpHTML(el,level)
  {
  if (!level) {level=0; tables_to_format=new Array(); nexttabid=0;}
  var tag,content,s,i,r,an,av,att,ends,tagend,parentname;
  s=el.nodeName;
  if (s==null)
    {
    alert ('strange');
    }
  ends="</"+s+">";
  tagend=">";
  tag=s.toUpperCase();
  r=clean_rules[tag];
  parentname=(el.parentElement)?el.parentElement.nodeName:"";

//  if ((tag=='p')&&((parentname=='td')||(parentname=='th')))
//    {
//    r=false;
//    }

	
  if (r)
    {
    if (tag=='TR') {s+=" valign='top'";}
    att=r.split (',');
    var autoformat=-1;
    for (i=0;i<att.length;i++)
      {
      an=att[i].toLowerCase();
      if (an=="/")
        {
        ends=""; tagend="/>";
        continue;
        }

      av=el.getAttribute(an,0);
	    if ((tag=='TABLE')&&(an=='width')) {av='100%';}

      if (av)
        {
        if (an=="classname") {an="class";}
        if ((tag=='IMG')&&(an=='src')) av=el.getAttribute('relsrc');
        if ((tag=='A')&&(an=='href')) {var h2=el.getAttribute('relhref'); if (h2) av=h2;}

        if (tag=='TABLE')
          {
          switch (an)
            {
            case 'border': av=0; break;
            case 'cellspacing': av=1; break;
            case 'cellpadding': av=2; break;
            }
          }

        s+=" "+an+"='"+av+"'";
        }
      }
    if ((tag=='TABLE')&&(autoformat==-1))
      {
      s+=" id='tab_"+nexttabid+"'";
      tables_to_format[tables_to_format.length]="tab_"+nexttabid;
      nexttabid++;
      } 
    s="<"+s+tagend;
    }
  else
    {s=""; ends="";}


  content="";
  for (i=0;i<el.childNodes.length;i++)
    {
    els=el.childNodes[i];
    switch (els.nodeType)
      {
      case 1: content+=jsb_cleanUpHTML(els,level+1);break;
      case 3: content+=els.nodeValue; break;
      }
    }
  if ((content)||(tag=='IMG')||(tag=='BR')||(tag=='TABLE')||(tag=='TH')||(tag=='TR')||(tag=='TD'))
    {
    return s+content+ends;
    }
  else
    {
    return "";
    }
  }


function eframe_KeyPressed()
  {
  event.returnValue=true;
  var r;
  /*
  if ((event.keyCode==13)&&(!event.shiftKey))
    {
    r=editBox.document.selection.createRange();
    var el=r.parentElement();
    if (!el) {return;}
    if ((el.nodeName!='TD')&&(el.nodeName!='TH')&&(el.nodeName!='DIV')) {el=el.parentElement;}
    if (!el) {return;}
    if ((el.nodeName!='TD')&&(el.nodeName!='TH')&&(el.nodeName!='DIV')) {el=el.parentElement;}
    if (!el) {return;}

    if ((el.nodeName=='TD')||(el.nodeName=='TH'))
      {
      event.returnValue=false;
      var row=el.parentElement;
      var tab=row.parentElement.parentElement;

      if (el.cellIndex==(row.cells.length-1))
        {
        if (el.parentElement.rowIndex==(tab.rows.length-1))
          {
          var s,empty=false;

          if (row.rowIndex>0)
            {
            // check for empty row
            empty=true;
            for (var i=0;i<row.cells.length;i++)
              {
              s=row.cells[i].innerHTML;
              if ((s!="")&&(s!="&nbsp;")) empty=false;
              }
            }

          if (!empty)
            {
            // add new row
            var newrow=tab.insertRow();
            var newcell;
            for (var i=0;i<row.cells.length;i++)
              {
              newcell=newrow.insertCell();
              newcell.innerHTML='&nbsp;';
              }
            eframe_ReFormatTable(tab);
            el=newrow.cells[0];
            }
          else
            {
            // remove last row
            tab.deleteRow(row.rowIndex);
            eframe_ReFormatTable(tab);
            el=tab;
            }
          }
        else
          {
          el=tab.rows[row.rowIndex+1].cells[0];
          }
        }
      else
        {
        // jump to right cell
        el=row.cells[el.cellIndex+1];
        }
      r.moveToElementText(el);
      r.collapse(false);
      r.select();
      }
    }
    */
  }

function eframe_Paste()
  {
  if (!editBoxRange)
    {
    eframe_StoreCursorPos();
    }
  pasteBox.focus();
  var r2=pasteBox.document.selection.createRange();
  pasteBox.document.execCommand('selectall');
  pasteBox.document.execCommand('paste');
  editBoxPasted=jsb_cleanUpHTML(pasteBox);
  if (event) {event.returnValue=false;}
  setTimeout ('eframe_PasteAfter()',0);
  return false;
  }

function eframe_PasteAfter()
  {
  var tabid;
  editBoxRange.pasteHTML(editBoxPasted);
  for (var i=0;i<tables_to_format.length;i++)
    {
    tabid=tables_to_format[i];
    eframe_SetTabAutoformat(P$.find(tabid),1);
    }
  eframe_RestoreCursorPos();
  }

function jsb_ControlResizeEnd()
  {
  var el = this.event.srcElement;
  if (el.nodeName=='IMG')
    {
    var id=el.getAttribute('ImgID');
    var srcw=Number(el.getAttribute('srcwidth'));
    var srch=Number(el.getAttribute('srcheight'));
    var h=el.offsetHeight;
    var w=el.offsetWidth;
    if (srch)
      {
      h=w / (srcw/srch);
      el.style.height=h;
      }

    ResizedImgsArray[id]=id+";"+w+";"+h;
    }
  return eframe_OnSelect();
  }


function eframe_SetTabAutoformat(table, newStyle)
  {
  var tc,ts=parent.tableAutoFormatStyles[newStyle];
  var r,c,cls,maxCell,maxRow,t,row,cell;

  if (ts.Table)
    {
    table.className=ts.Table.cls;
    }
  table.setAttribute('autoformat',newStyle);
  table.removeAttribute('id');
  maxRow=table.rows.length-1;
  for (r=0;r<=maxRow;r++)
    {
    row=table.rows[r];
//    row.setAttribute('valign','top'); DONT WORK???!!
    maxCell=row.cells.length-1;
    for (c=0;c<=maxCell;c++)
      {
      cell=row.cells[c];
      if ((r%2) == 0) {tc=ts.Even;} else {tc=ts.Odd;}
      if ((c==0)&&(ts.Left)) {tc=ts.Left;}
      if ((c==maxCell)&&(ts.Right)) {tc=ts.Right;}
      if ((c==maxCell)&&(r%2==0)&&(ts.RightEven)) {tc=ts.RightEven;}
      if ((c==maxCell)&&(r%2!=0)&&(ts.RightOdd))  {tc=ts.RightOdd;}

      if ((c==0)&&(r%2==0)&&(ts.LeftEven)) {tc=ts.LeftEven;}
      if ((c==0)&&(r%2!=0)&&(ts.LeftOdd))  {tc=ts.LeftOdd;}

      if ((r==0)&&(ts.Top)) {tc=ts.Top;}
      if ((r==maxRow)&&(ts.Bottom)) {tc=ts.Bottom;}

      if ((c==0)&&(r==0)&&(ts.TopLeft)) {tc=ts.TopLeft;}
      if ((c==maxCell)&&(r==0)&&(ts.TopRight)) {tc=ts.TopRight;}
      if ((c==0)&&(r==maxRow)&&(ts.BottomLeft)) {tc=ts.BottomLeft;}
      if ((c==maxCell)&&(r==maxRow)&&(ts.TopRight)) {tc=ts.BottomRight;}

      if (cell.tagName!=tc.tag.toUpperCase())
        {
        inner=cell.innerHTML;
        newNode=document.createElement (tc.tag);
        cell.replaceNode(newNode);
        cell=newNode;
        cell.innerHTML=inner;
        }
      cc=tc.cls; if (cc=="") {cc=false;}
      cell.className=cc;
      cell.setAttribute('width','');
//      cell.setAttribute('width',(100/(maxCell+1))+'%');
      }
    }

  }


function eframe_ReFormatTable(tab)
  {
  var autoformat=tab.getAttribute("autoformat");
  if (autoformat)
    {
    eframe_SetTabAutoformat(tab,autoformat);
    }
  }

function eframe_StoreCursorPos()
  {
  editBoxRange=editBox.document.selection.createRange();
  editBoxScrollX=editBox.document.body.scrollLeft;
  editBoxScrollY=editBox.document.body.scrollTop;
  window.status=Number(window.status)+1;
  }

function eframe_RestoreCursorPos()
  {
  if (editBoxRange)
    {
    editBoxRange.select();
    editBox.document.body.scrollLeft=editBoxScrollX;
    editBox.document.body.scrollTop=editBoxScrollY;
    editBoxRange=false;
    }
  }
function eframe_ClosePopup()
  {
  parent.pframe_ClosePopup();
  popup_TabStyles.style.visibility='hidden';
  }

function eframe_DrawTabStyles()
  {
  var col=0,i,j,k,s="",ss;
  tstyles=parent.tableAutoFormatStyles ; //pframe_get_tableAutoFormatStyles();

  s="<table border='1' cellpadding='10' cellspacing='0'>";
  for (i=1;i<tstyles.length;i++)
    {
    if (col==0) {s+="<tr>";}

    ss="#"+i+"<br><table id='sampletab_"+i+"'><tbody>";
    for (j=0;j<5;j++)
      {
      ss+="<tr>";
      for (k=0;k<5;k++) ss+="<td>"+j+":"+k+"</td>";
      ss+="</tr>";
      }
    ss+="</tbody></table><br><a href='javascript:execCmd(\"settablestyle\","+i+");'>"+parent.RT_SELECT_TABLEAUTOFORMAT+"</a>";

    col++;
    s+="<td>"+ss+"</td>";
    if (col>2) {col=0; s+="</tr>";}
    }
  if (col!=0) {s+="</tr>";}
  s+="</table>";
  document.write(s);

  for (i=1;i<tstyles.length;i++)
    {
    eframe_SetTabAutoformat(P$.find("sampletab_"+i),i);
    }
  }



function eframe_viewSource()
  {
//  alert (editBox.innerHTML);
  alert (jsb_cleanUpHTML(editBox));
  }
function eframe_DoSubmit()
  {
  parent.RichTextEditForm.TextContent.value=jsb_cleanUpHTML(editBox);
  editBox.innerHTML="";

  if (ResizedImgsArray.length>0)
    {
    var e,s="";
    for (var e in ResizedImgsArray)
      {
      if (s!=="") {s+="|";}
      s+=ResizedImgsArray[e];
      }
    parent.RichTextEditForm.ResizedImgs.value=s;
    }

  parent.RichTextEditForm.submit();
  }

function eframe_SetTextStyle(tagclass)
  {
  if ((tagclass=='')||(!tagclass))
    {
    // cleanup style
    var el=editBoxRange.parentElement();
    var i;
    textstyle_focused=false;
    for (i=0;i<5;i++)
      {
      tn=el.tagName;
      tnn="|"+tn+"|";
      if (parent.block_tags.indexOf(tnn)!=-1) { break;}
      if (parent.para_tags.indexOf(tnn)!=-1)
        {
        var pe=el.parentElement;
        el.outerHTML=el.innerHTML;
        editBoxRange.moveToElementText(pe);
        editBoxRange.collapse();
        break;
        }
      el=el.parentElement;
      if (!el) {break;}
      }
    eframe_RestoreCursorPos();
    }
  else
    {
    eframe_RestoreCursorPos();
    var tc=tagclass.split ('.');
    var t=tc[0],c=tc[1];
    var tn, tnn;
    execCmd ('formatblock','<'+t+'>');
    var el=editBox.document.selection.createRange().parentElement();
    if (c)
      {
      for (var i=0;i<5;i++)
        {
        tn=el.tagName;
        tnn="|"+tn+"|";
        if (parent.block_tags.indexOf(tnn)!=-1) { break;}
        if ((t=='A')&&(tn=='A'))
          {
          el.setAttribute('className',c);
          break;
          }
        if (parent.para_tags.indexOf(tnn)!=-1)
          {
          el.setAttribute('className',c);
          break;
          }
        el=el.parentElement;
        }
      }
    }
  eframe_OnSelect();
  }

function jsb_SelectURL_callback(newurl,args)
  {
  	var title=undefined;
  eframe_RestoreCursorPos();
 	var arr=newurl.split('\n');
 	if (arr.length>1) {
 		newurl=arr[0]; 
 		title=arr[1];
 	}
  execCmd ('CreateLink',newurl);
  editBoxRange=editBox.document.selection.createRange();
  var el=false;
  if (editBox.document.selection.type=='Control')
    {
    if (editBoxRange.length==1) {el=editBoxRange(0).parentElement;}
    }
  else
    {
    el=editBoxRange.parentElement();
    }

  if ((el)&&(el.tagName=='A'))
    {
    el.setAttribute('relhref',el.getAttribute('href'));
    if (title!=undefined) {el.setAttribute('title',title);}
    }
  eframe_OnSelect();
  }

function jsb_InsertImage_callback(result,args)
  {
//  eframe_RestoreCursorPos();
  var name=result.Name;
  editBoxRange.pasteHTML("<img id='postimg_"+result.ImgID+"' border='0' relsrc='"+result.ImgSrc+"' src='"+result.ImgSrc+
    "' width='"+result.Width+"' height='"+result.Height+
    "' srcwidth='"+result.SrcWidth+"' srcheight='"+result.SrcHeight+
    "' ImgID='"+result.ImgID+"' />");

//  editBox.focus();
  eframe_OnSelect();
  }
function eframe_UpdateLinkProperties(link,name)
  {
  EditingObject.setAttribute ('title',name);
  }
function eframe_UpdateImgProperties(w,h,hspace,vspace,link,name)
  {
  var t=EditingObject;
  t.setAttribute('hspace',Number(hspace));
  t.setAttribute('vspace',Number(vspace));
  t.setAttribute('width',Number(w));
  t.setAttribute('height',Number(h));
  t.setAttribute('alt',name);
//  t.style.width=w;
//  t.style.height=h;
  }


function execCmd(cmd,param,storecursor)
  {
  var el,tr_el,tbody_el,tab_el,delta,i,c,target;
  if (storecursor)
    {
    eframe_StoreCursorPos();
    }
  delta=0;
  switch(cmd)
    {
    case 'paste':
      eframe_Paste();
      return;
    case 'table':
      eframe_InsertTable();
      return;
    case 'tabstyle':
      popup_TabStyles.style.visibility='visible';
      popup_TabStyles.style.top=document.body.scrollTop;
      popup_TabStyles.style.left=10;
      return;
    case 'settablestyle':
      popup_TabStyles.style.visibility='hidden';
      el=editBoxEventElement;
      if ((el.tagName=='TD')||(el.tagName=='TH'))
        {
        tab=el.parentElement.parentElement.parentElement;
        }
      else
        {
        if (el.tagName=='TABLE') {tab=el;}
        }
      eframe_SetTabAutoformat(tab,param);
      break;
    case 'insertrowbelow': delta=1;
    case 'insertrow':
      el=editBoxEventElement;
      if ((el.tagName=='TD')||(el.tagName=='TH'))
        {

        var tr_el=el.parentElement;
        var tab=tr_el.parentElement.parentElement;
        var tr_el_new=tab.insertRow (tr_el.rowIndex+delta);
        for (i=0;i<tr_el.cells.length;i++)
          {
          c=tr_el_new.insertCell();
          c.innerHTML='&nbsp;';
          c.setAttribute ('className',tr_el.cells[i].getAttribute('className',0));
          }
        }
      eframe_ReFormatTable(tab);
      break;
    case 'insertcolright': delta=1;
    case 'insertcol':
      el=editBoxEventElement;
      if ((el.tagName=='TD')||(el.tagName=='TH'))
        {
        var tab=el.parentElement.parentElement.parentElement;
        for (i=0;i<tab.rows.length;i++) tab.rows[i].insertCell(el.cellIndex+delta);
        }
      eframe_ReFormatTable(tab);
      break;
    case 'deleterow':
      el=editBoxEventElement;
      if ((el.tagName=='TD')||(el.tagName=='TH'))
        {
        var tab=el.parentElement.parentElement.parentElement;
        tab.deleteRow(el.parentElement.rowIndex);
        }
      eframe_ReFormatTable(tab);
      break;

    case 'deletecol':
      el=editBoxEventElement;
      if ((el.tagName=='TD')||(el.tagName=='TH'))
        {
        var remindx=el.cellIndex;
        var tab=el.parentElement.parentElement.parentElement;
        for (i=0;i<tab.rows.length;i++) tab.rows[i].deleteCell(remindx);
        }
      eframe_ReFormatTable(tab);
      break;
    case 'link':
      W.openModal({url:parent.URL_SelectPage,w:450,h:500,callback:"jsb_SelectURL_callback"});
      return;
    case 'image':
      W.openModal({url:parent.URL_InsertImage,w:450,h:200,callback:"jsb_InsertImage_callback"});
      return;
    case 'img_align_left':
      EditingObject.setAttribute('align','left');
      break;
    case 'img_align_right':
      EditingObject.setAttribute('align','right');
      break;
    case 'img_align_inline':
      EditingObject.setAttribute('align','');
      break;
    case 'img_resetsize':
      var t=EditingObject;
      h=Number(t.getAttribute('srcheight'));
      w=Number(t.getAttribute('srcwidth'));
      if ((w>0)&&(h>0))
        {
        t.style.width=w;
        t.style.height=h;
        var id=t.getAttribute('ImgID');
        if (id)
          {
          ResizedImgsArray[id]=id+";"+w+";"+h;
          }
        }
      break;
    default:
      editBox.document.execCommand(cmd, false, param);
    }
  eframe_RestoreCursorPos();
  eframe_OnSelect();
  }