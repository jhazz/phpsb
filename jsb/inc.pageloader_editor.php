<?php

function InitPageEditor()
  {
  global $cfg,$EditOnlyLayout,$DesignMode,$JSBLayoutID,$JSBPageID,$SysContext,$_SYSSKIN_NAME;
  $SysSkinURL="$cfg[PublicURL]/sys/skins/$_SYSSKIN_NAME";

  $__=&$GLOBALS['_STRINGS']['_'];
?>
<script>
var CallURL='<? print $cfg['ActionURL']; ?>/';
var DesignMode=<? print intval($DesignMode); ?>;
var JSBLayoutID=<? print intval($JSBLayoutID); ?>;
var JSBPageID=<? print $JSBPageID; ?>;
var SysContext='<? print $SysContext; ?>';
var JSB_BindingValues=false;
var JSB_highlighted_hat=false;
var JSB_HighLightedCtrl=false;
var JSB_DragControlID=false;
var EditOnlyLayout=<? print (($EditOnlyLayout)?1:0);?>;
var MSG_PLEASE_WAIT='<? print $__['MSG_PLEASE_WAIT']; ?>';
function JSB_CTRL_Out(ControlID){}
function JSB_CTRL_Over(ControlID,EditURL)
  {
  if (JSB_DragControlID)
    {
    var hat=P$.find("JSB_CellHat_"+ControlID);
    hat.style.backgroundColor='#0020ff';
    return;
    }

  if (JSB_HighLightedCtrl)
    {
    JSB_HighLightedCtrl.style.backgroundColor="";
    JSB_HighLightedCtrl.style.border='1px inset';
    }
  var cell=P$.find("JSB_Cell_"+ControlID);
  var pos=GetAbsXY(cell);
//  cell.style.backgroundColor='#FFDFBF';
  cell.style.border='1px dotted orange';
  JSB_HighLightedCtrl=cell;
  JSB_HighLightedCtrlID=ControlID;
  var f=P$.find('DIV_JSB_FloatingToolbar');
  f.style.left=pos.x;
  f.style.top=pos.y;
  f.style.width=cell.offsetWidth;
  f.style.visibility="visible";
  var pen=P$.find("JSB_FloatingToolbar_Edit");
//  if (EditURL!="")
//    {
//    pen.style.visibility="inherit";
//    }
//  else
//    {
    pen.style.visibility="hidden";
//    }
  return true;
  event.returnValue=true;
  }

function JSB_ON_KeyPress()
  {
  if (JSB_DragControlID && (event.keyCode==27))
    {
    if (JSB_highlighted_hat)
      {
      JSB_highlighted_hat.style.backgroundColor='';
      JSB_highlighted_hat=false;
      }
    P$.find("DIV_JSB_DragPot").style.visibility='hidden';
    JSB_DragControlID=false;
    }
  }
function JSB_ON_MouseUp()
  {
  P$.find("DIV_JSB_DragPot").style.visibility='hidden';
  if (JSB_DragControlID && JSB_highlighted_hat)
    {
    JSB_highlighted_hat.style.backgroundColor='';
    var SlotName=JSB_highlighted_hat.getAttribute('SLOTNAME');
    var ControlID=JSB_highlighted_hat.getAttribute('CONTROLID');
    var moveargs=false;
    if (ControlID)
      {
      moveargs="TargetControlID="+ControlID;
      }
    if (SlotName)
      {
      moveargs="TargetSlot="+SlotName;
      }
    if (moveargs)
      {
      JSB_Transaction(CallURL+"jsb.IControl.Move.n?EditSysContext="+SysContext+"&EditJSBPageID="+JSBPageID+"&ControlID="+JSB_DragControlID+"&"+moveargs);
      }
    }
  JSB_DragControlID=false;
  }

function JSB_ON_MouseOver(e)
  {
  var id,srce;
  if (e==undefined) {srce=event.srcElement; } else {srce=e.target; event=e; }
  var ce,i,ControlID=false,SlotName,t,hat,ControlBlock;
  ce=srce;
  for (i=0;i<20;i++)
    {
    t=ce.getAttribute('CONTROLID');
    if (t) { ControlBlock=ce; ControlID=t; hat=P$.find("JSB_CellHat_"+ControlID); break;}
    t=ce.getAttribute('SLOTNAME');
    if (t) { SlotName=t; SlotBlock=ce;}
    t=ce.getAttribute('SLOTNAME');
    if (t) {hat=ce; SlotName=t; break; }
    ce=(ie)?ce.parentElement:ce.parentNode;
    if ((!ce)||(ce.nodeName=='BODY')) break;
    }

  if ((!JSB_DragControlID)&&(ControlID))
    {
    JSB_CTRL_Over(ControlID);
    }

  if (JSB_DragControlID)
    {
    if (ie)
      {
      if (event.y<50) {document.body.scrollTop-=10;}
      if (event.y>document.body.offsetHeight-50) {document.body.scrollTop+=10;}
      }

    var pot=P$.find("DIV_JSB_DragPot");

    if (ie)
      {
      pot.style.left=event.x+document.body.scrollLeft+15;
      pot.style.top= event.y+document.body.scrollTop+15;
      }
    else
      {
      pot.style.left=event.pageX+5;
      pot.style.top= event.pageY+5;
      }
    pot.style.visibility='visible';
    var hatcolor='#0020FF';
    if (JSB_highlighted_hat)
      {
      JSB_highlighted_hat.style.backgroundColor='';
      }
    if (hat)
      {
      JSB_highlighted_hat=hat;
      hat.style.backgroundColor=hatcolor;
      }
    }
  }
function JSB_ON_MouseDown(e)
  {
  if (ie) {id=event.srcElement.id;} else {id=e.target.id; event=e;}

  if (id=='jsb_dragger')
    {
    P$.find('DIV_JSB_FloatingToolbar').style.visibility='hidden';
    JSB_DragControlID=JSB_HighLightedCtrlID;
    var f1=P$.find("JSB_ControlCaption_"+JSB_HighLightedCtrlID);
    var f=P$.find("DIV_JSB_DragPot");
    f.innerHTML="<table cellpadding='0' border='1' cellspacing='0'><tr><td bgcolor='#0050ff'><font color='white'><b>"+f1.innerHTML+"</b></font></td></tr></table>";
    event.returnValue=false;
    return false;
    }
  }

function JSB_CTRL_DoBehavior()
  {
  P$.find('DIV_JSB_FloatingToolbar').style.visibility='hidden';
  W.openModal({url:CallURL+"jsb.IControl.Edit.b",w:500,h:500,subaction:'edit',
    EditSysContext:SysContext,EditJSBPageID:JSBPageID,
    EditControlID:JSB_HighLightedCtrlID,reloadOnOk:1});
  }
function JSB_Transaction(callurl)
  {
  var t;
  if (window.parent)
    {
    t=window.parent.frames['transaction'];
    if (t)
      {
      t.location.href=callurl;
      return;
      }
    }
  W.openModal({url:callurl,w:300,h:100,reloadOnOk:1});
  }
function JSB_CTRL_DoRemove()
  {
  if (window.confirm("<? print $__['WARNING_CTRL_REMOVE'];  ?>"))
    {
    JSB_Transaction(CallURL+"jsb.IControl.Remove.n?EditSysContext="+SysContext+"&EditJSBPageID="+JSBPageID+"&ControlID="+JSB_HighLightedCtrlID);
    }
  }

function JSB_CTRL_DoAdd()
  {
  P$.find('DIV_JSB_FloatingToolbar').style.visibility='hidden';
  W.openModal({url:CallURL+"jsb.IControl.Add.b",w:500,h:500,
    subaction:'insbefore',EditSysContext:SysContext,EditJSBPageID:JSBPageID,
    BaseControlID:JSB_HighLightedCtrlID,
    reloadOnOk:1});
  }
function JSB_CTRL_AddToSlot(SlotName)
  {
  P$.find('DIV_JSB_FloatingToolbar').style.visibility='hidden';
  W.openModal({url:CallURL+"jsb.IControl.Add.b",w:500,h:500,
    subaction:"addtoslot",EditSysContext:SysContext,EditJSBPageID:JSBPageID,Slot:SlotName,
    reloadOnOk:1});
  }

</script>
<style type="text/css">
.JSB_EditableControlCaption {background-color:#9080f0; color:white; font-family:verdana; font-size:10px; font-weight:normal;}
.JSB_EditableContentCaption {background-color:#002080; color:white; font-family:verdana; font-size:10px; font-weight:normal;}
.JSB_NoneditableControlCaption {background-color:#909097; color:#e0e0e0; font-family:verdana; font-size:10px; font-weight:normal;}
</style>

<div id='DIV_JSB_FloatingToolbar' style='width:100; position:absolute; visibility:hidden; z-index:110'>
<table width='100%' cellpadding=3 cellspacing=1><tr>
<td><img onmousedown='JSB_ON_MouseDown(event);' id='jsb_dragger' border=0 <? print "src='$SysSkinURL/jsb_move.gif' alt='$__[HINT_CTRL_MOVE]'"; ?>
width=16 height=16></a></td>

<td><a href='javascript:JSB_CTRL_DoAdd()'><img border=0
<? print "src='$SysSkinURL/jsb_add.gif' alt='$__[HINT_CTRL_ADD_BEFORE]'"; ?>
width=16 height=16></a></td>

<td><a href='javascript:JSB_CTRL_DoBehavior()'><img border=0 <? print "src='$SysSkinURL/jsb_behavior.gif' alt='$__[HINT_CTRL_BEHAVIOR]'";  ?> width='16' height='16'></a></td>
<td width='100%' align='left'><a href='javascript:JSB_CTRL_DoEdit()'><img id='JSB_FloatingToolbar_Edit' border=0 <? print "src='$SysSkinURL/jsb_edit.gif' alt='$__[HINT_CTRL_EDIT]'";  ?> width='16' height='16'></a></td>
<td><a href='javascript:JSB_CTRL_DoRemove()'><img border=0
<? print "src='$SysSkinURL/jsb_trash.gif' alt='$__[HINT_CTRL_REMOVE]'";?>
width=16 height=16></a></td>
</tr></table></div>
<div onMouseOver='event.returnValue = false;' id='DIV_JSB_DragPot' style='visibility:hidden;position:absolute;'></div>

<?
} # end of function InitEditor

?>
