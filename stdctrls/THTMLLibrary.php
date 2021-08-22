<?
class stdctrls_THTMLLibrary
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Image storage cartridge";
var $CopyrightURL="www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  $this->About=$_['THTMLLIB_ABOUT'];
  $this->Propdefs=array(
    LibName=>array (Type=>"String",Required=>true,Caption=>$_['THTMLLIB_LIBNAME']),
    );
  }

function print_scripts()
  {
  ?><script>
  function HTMLLibrary_UpdateItem(ID)
    {
    var w=window.open ("","wnd_"+ID,"width=500,height=100");
    var d=w.document;
    d.open ("");
    d.write ("<title>Please wait</title><body bgcolor='#d0d0d0'><center>Please wait. Updating...");
    d.close();
    eval ("form_"+ID+".submit()");
    }
  </script><?

  }
function AfterInit(&$Control)
  {
  if ($Control->DesignMode)
    {
    if (!$this->ScriptPrinted)
      {
      $this->ScriptPrinted=true;
      $this->print_scripts();
      }
#    $_ENV->InitWindows();
    }
  }
function Render(&$Control)
  {
  global $cfg;
  global $_STRINGS,$_THEME_NAME;
  $_=&$_STRINGS['stdctrls'];

  $libfilename=$cfg['ThemesPath'].'/'.$_THEME_NAME.'/'.basename($Control->Properties['LibName']).'.lib.html';

  if (!file_exists($libfilename))
    {
    return array(Error=>$_['THTMLLIB_NOLIBFILEFOUND'],Details=>$libfilename);
    }


//  var_dump($Control);
  $LayoutPath=$Control->LayoutControlPath;
  $ControlPath=$Control->PageControlPath;

  $s="BindTo='$ControlPath'";
  if ($LayoutPath) {$s.=" OR BindTo='$LayoutPath'";}
  $s="SELECT * FROM stdctrls_Libitems WHERE $s";
  $q=DBQuery ($s,"BindTo");
  if ($q)
    {
    $libitem=$q->Rows[$ControlPath];
    if (!$libitem) {$libitem=$q->Rows[$LayoutPath];}
    if (!$libitem)
      {
      return array(Error=>"Strange library item.");
      }
    }

  $LibItemID=0;
  if ($libitem)
    {
    $LibItemID=intval($libitem->LibItemID);
    }

  $f=file($libfilename);
  if ($f)
    {
    $CurrentID=-1;
    $PrintBlockMode=false;
    $blocknames=false;
    foreach ($f as $s)
      {
      $i=strpos($s,"---BLOCKBEGIN");
      if ($i!==false)
        {
        $PrintBlockMode=false;
        $CurrentID++;
        $blockname="Block #".$CurrentID;
        $s=substr ($s,$i+13);
        $cap=strpos($s,"---");
        if ($cap!==false)
          {
          $blockname=substr ($s,0,$cap);
          $s=substr($s,$cap+3);
          }
        $blocknames[$CurrentID]=trim($blockname);
        if ($CurrentID==$LibItemID)
          {
          $PrintBlockMode=true;
          }
        }

      if ($PrintBlockMode)
        {
        $i=strpos($s,"---BLOCKBEGIN");
        if ($i!==false)
          {
          $s=substr($s,0,$i);
          }
        $i=strpos($s,"---BLOCKEND");
        if ($i!==false)
          {
          $PrintBlockMode=false;
          $s=substr($s,0,$i);
          }
        print $s;
        }
      }



    if (($Control->DesignMode) && ($blocknames))
      {
      foreach ($blocknames as $id=>$name)
        {
        $sel=($id==$LibItemID)?" SELECTED":"";
        $s.="<option value='$id'$sel>$name</option>";
        }
      $ID=$Control->JSBPageControlID;
      print "<form method='POST' name='form_$ID' target='wnd_$ID' action='".ActionURL("stdctrls.IHTMLibrary.Save.f")."'>
        <select class='inputarea' name='NewID'>$s</select>
        <input class='button' type='button' onClick=\"HTMLLibrary_UpdateItem($ID)\" value='$_[THTMLLIB_SELECT_ITEM]'>
        <input type='hidden' name='BindTo' value='$ControlPath'>
        </form>";
      }
    }

  }

function Remove(&$Control)
  {
  print "Removing!";
  }


}
