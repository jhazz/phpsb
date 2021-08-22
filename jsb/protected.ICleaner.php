<?
class jsb_ICleaner
  {
var $CopyrightText="(c)2005 JhAZZ Site Builder. Standard site controls cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $RoleAccess=array(MainDesigner=>"View,Clean");

  function jsb_ICleaner()
    {
    $_=&$GLOBALS[_STRINGS][jsb];
    $this->Title=$_[ICLEAN_TITLE];
    $this->MethodsPublished="View";
    }

  function Clean($args)
    {
    $_ =&$GLOBALS[_STRINGS][jsb];

    $check=$args['check'];
    if (is_array($check))
      {
      $s="";
      foreach($check as $id=>$x)
        {
        if ($x!=1) {continue;}
        if ($s) {$s.=", ";}
        $id=DBEscape($id);
        $s.="'$id'";
        }
      print $_[ICLEAN_COMPLETE]."<br>";
      print "<table width='800'><tr><td>$s</td></tr></table>";
      $x="DELETE FROM jsb_Pages WHERE CONCAT(SysContext,':',JSBPageID) IN ($s)";
      DBexec ($x);
      $x="DELETE FROM jsb_PageControls WHERE CONCAT(SysContext,':',JSBPageID) IN ($s)";
      DBexec ($x);
      }
    }

  function View($args)
    {
    $_ =&$GLOBALS[_STRINGS][jsb];
    $__=&$GLOBALS[_STRINGS][_];

    global $cfg;

    $q=DBQuery ("SELECT SysContext,JSBPageID,Caption FROM jsb_Pages WHERE State=3 ORDER BY SysContext");
    if ($q)
      {

      print $_[ICLEAN_CONFIRM]."<form method='post' action='".ActionURL("jsb.ICleaner.Clean.b")."'><table><tr valign='top'>";
      $rowmax=ceil($q->RowCount/5);
      $i=0;
      $pctx="";

      foreach ($q->Rows as $rowno=>$row)
        {
        if (!$i) {print "<td width='20%'>";}
        $s=langstr_get($row->Caption);
        if ($pctx!=$row->SysContext) {$pctx=$row->SysContext; print "<b>$pctx</b><br>"; $i++;}

        $pageid="$row->SysContext:$row->JSBPageID";

        if (strlen ($s)>100) {$s=substr($s,0,100);}
        $viewurl=$cfg['EditPageURL']."/$row->SysContext/$row->JSBPageID";

        print "<input type='checkbox' name='check[$pageid]' value='1' checked><a href='$viewurl' target='_blank'>$s</a><br>";
        $i++;
        if ($i>$rowmax) {$i=0;print "</td>";}
        }


      print "</tr></table><input type='submit' class='button' value='$__[CAPTION_OK]'></form>";
      }

    }
  }


?>
