<?
class um_IUserGroups
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. User management cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $RoleAccess=array(
  UserGroupManager=>"EditName,EditGroupRoles,Browse,DeleteGroups,UpdateGroupName,ApplyRoles,UpdateAccessTables,AddNewGroup",
  UserManager=>"ManagementPoint"
  );

function um_IUserGroups()
  {
  $_=&$GLOBALS[_STRINGS][um];
  }

function Browse($args)
  {
  $_=&$GLOBALS[_STRINGS][um];
  $__=&$GLOBALS[_STRINGS][_];
  global $cfg,$_CORE;
  $qgroups=DBQuery ("SELECT * FROM um_UserGroups ORDER BY GroupID","GroupID");
  if ($qgroups)
    {
    print "<table width='500'><tr><td>";
    $_CORE->PrintTable($qgroups,array(
      URL=>ActionURL("um.IUserGroups.DeleteGroups.b"),
      Fields=>array(Caption=>$_[CAPTION_GROUPS],EditName=>$_[CAPTION_RENAME],ApplyRoles=>$_[CAPTION_APPLYROLES]),
      ShowCheckers=>true,
      FieldHrefs=>array(
        EditName=>ActionURL("um.IUserGroups.EditName.bm")."?UserGroupID=",
        ApplyRoles=>ActionURL("um.IUserGroups.EditGroupRoles.bm")."?UserGroupID=",
        ),
      ShowDelete=>true,
      ShowOk=>false,
      CSS_TabHead=>"tabhead",
      CSS_Row=>"tab",
      ));
    print "</td></tr></table>";
    }
  print "<a href='#' onClick='document.getElementById(\"AddGroup\").style.display=\"block\"'>$_[CAPTION_ADDGROUP]</a>
  <br><div id='AddGroup' style='display:none'>
  <form method='post' action='".ActionURL("um.IUserGroups.AddNewGroup.bm")."'>
  <table width='500'><tr><td align='center'>
  $_[MSG_NEWGROUPNAME]<br>
  <input type='text' class='inputarea' maxlength='100' name='NewGroupName'>
  </td></tr></table><br><br>
  <input class='button' type='submit' value='$__[CAPTION_ADD]'>

  </form>
  </div>";
}

function DeleteGroups ($args)
  {
  extract(param_extract(array(
    check=>'int_checkboxes'),$args));

  $_=&$GLOBALS[_STRINGS][um];
  $__=&$GLOBALS[_STRINGS][_];
  global $cfg;

  if ($check)
    {
    $GroupList=implode (",",array_keys($check));
    $s="DELETE FROM um_UserGroups WHERE GroupID IN ($GroupList)";
    DBExec ($s);
    $s="DELETE FROM um_UserInGroups WHERE GroupID IN ($GroupList)";
    DBExec ($s);
    $s="DELETE FROM um_GroupRoles WHERE GroupID IN ($GroupList)";
    DBExec ($s);
    }
  return array (ForwardTo=>ActionURL("um.IUserGroups.Browse.bm"));
  }

function AddNewGroup($args)
  {
  extract(param_extract(array(
    NewGroupName=>'string'),$args));

  if (!$NewGroupName) {$NewGroupName="New group";}
  $GroupID=DBGetID("um.UserGroup");
  if (DBExec ("INSERT INTO um_UserGroups (GroupID,Caption) VALUES ($GroupID,'$NewGroupName')"))
    {
    return array (ForwardTo=>ActionURL("um.IUserGroups.Browse.bm",array(NewGroupName=>false)+$args));
    }
  }

function EditName($args)
  {
  extract(param_extract(array(
    UserGroupID=>'int'),$args));

  $_=&$GLOBALS[_STRINGS][um];
  $__=&$GLOBALS[_STRINGS][_];
  global $cfg;
  global $_LANGUAGE;

  $qgroup=DBQuery ("SELECT * FROM um_UserGroups WHERE GroupID=$UserGroupID");
  print "<form method='post' action='".ActionURL("um.IUserGroups.UpdateGroupName.b")."'>
    <h2>$_[TITLE_RENAMEGROUP]</h2>
    <input type='text' name='GroupName' value='".$qgroup->Top->Caption."'><br><br>
    <input type='hidden' name='UserGroupID' value='$UserGroupID'>
    <br><input class='button' type='submit' value='$__[CAPTION_OK]'>
    <input type='button' class='button'  value='$__[CAPTION_CANCEL]' onClick='history.back();'>
    </td></tr></table>
    </form>";
  }


function EditGroupRoles($args)
  {
  extract(param_extract(array(
    UserGroupID=>'int'),$args));

  $_=&$GLOBALS[_STRINGS][um];
  $__=&$GLOBALS[_STRINGS][_];
  global $cfg;
  global $_LANGUAGE;

#  $q=DBQuery ("SELECT * FROM sys_Cartridges WHERE Active=1","Cartridge");
  $qgroup=DBQuery ("SELECT * FROM um_UserGroups WHERE GroupID=$UserGroupID");
  $qgrants=DBQuery ("SELECT * FROM um_GroupRoles WHERE GroupID=$UserGroupID",array("Role","Cartridge"));

  print "<h2>$_[TITLE_ROLES_OF_GROUP_] '".$qgroup->Top->Caption."'</h2>
    <form method='post' action='".ActionURL("um.IUserGroups.ApplyRoles.bm")."'>
    <input type='hidden' name='UserGroupID' value='$UserGroupID'>
    <table cellpadding='0'><tr><td bgcolor='#808080'>
    <table cellspacing='1' cellpadding='2'><tr><td></td>
    <td bgcolor='#40a040'>$_[CAPTION_ALLOW]</td><td bgcolor='#f02020'>$_[CAPTION_DENY]</td></tr> ";

  $Cartridges=&$_ENV->LoadCartridgesList(true);
  foreach ($Cartridges as $CartridgeName=>$Active)
    {
    if (!$Active) continue;
    $dname="$cfg[ScriptsPath]/$CartridgeName";
    if (!is_dir($dname)) $dname="$cfg[PHPSBScriptsPath]/$CartridgeName";
    if (is_dir($dname))
      {
      $inifile="$dname/cartridge.php";
      if (file_exists($inifile))
        {
        $langfile="$dname/lang.$_LANGUAGE.php";
        if (file_exists($langfile)) {require_once ($langfile);}

        require_once($inifile);
        if (!class_exists ($CartridgeName))
          {
          print "Error in '$inifile': Class '$Cartridge' not found";
          continue;
          }
        $Cartridge=&new $CartridgeName;
        $Roles=$Cartridge->Roles;
        $Title=$Cartridge->Title;
        if (!$Title) {$Title=$Cartridge;}
        print "<tr><td class='tabhead'>$Title</td></tr>";
        if ($Roles)
          {
          foreach($Roles as $Role=>$RoleName)
            {
            $s=$CartridgeName.':'.$Role;
            $AllowName="Allow[$s]"; $AllowChecked="";
            $DenyName="Deny[$s]"; $DenyChecked="";

            if ($qgrants)
              {
              $g=$qgrants->Rows[$Role][$CartridgeName];
              if ($g->AllowMode==1) {$AllowChecked='checked';}
              if ($g->AllowMode==2) {$DenyChecked ='checked';}
              }

            print "<tr class='tab'>
              <td align='right' class='tab'>$RoleName</a><br><font color='#808080'><i>$CartridgeName:$Role</i></font></td>
              <td align='center' bgcolor='#40a040'><input name='$AllowName' type='checkbox' value='1' $AllowChecked></td>
              <td align='center' bgcolor='#f02020'><input name='$DenyName' type='checkbox' value='1' $DenyChecked></td>
              </tr>";
            }
          }
        }
      }
    }

  print "</table></td></tr></table><br><br><input class='button' type='submit' value='$__[CAPTION_OK]'>
    <input type='button' class='button'  value='$__[CAPTION_CANCEL]' onClick='history.back();'>
    </form>
    <table width='400'><tr><td>$_[TEXT_ABOUT_ROLE_APPLYING]</td></tr></table>
    ";
  }

function UpdateGroupName($args)
  {
  $_=&$GLOBALS[_STRINGS][um];
  $__=&$GLOBALS[_STRINGS][_];
  global $cfg;
  extract(param_extract(array(
    UserGroupID=>'int',
    GroupName=>'string'
    ),$args));

  if (!$UserGroupID)
    {
    return array(Error=>"UserGroupID does not defined!");
    }
  if ($GroupName)
    {
    DBExec ("UPDATE um_UserGroups SET Caption='$GroupName' WHERE GroupID=$UserGroupID");
    }
  return array (ForwardTo=>ActionURL("um.IUserGroups.Browse.bm"));
  }


function UpdateAccessTables($args)
  {
  $_=&$GLOBALS[_STRINGS][um];
  $__=&$GLOBALS[_STRINGS][_];
  global $cfg;
  extract(param_extract(array(
    Sure=>'int'
    ),$args));

  if (!$Sure)
    {
    print "<form method='post' action='".ActionURL("um.IUserGroups.UpdateAccessTables.bm")."'>
    <table width='300'><tr><td><p  align='justify'>
    ".nl2br($_[UPDATE_ACCESS_WARNING])."</p><input type='submit' class='button' value='$__[CAPTION_OK]'><input type='hidden' name='Sure' value='1'></td></tr></table></form>";
    return;
    }
  $cartridges=&$_ENV->LoadCartridgesList(true);
  print "<table>";
  $s="DELETE FROM um_ProtectIntf";
  DBExec ($s);

  foreach ($cartridges as $Cartridge=>$Active)
    {
    $dname="$cfg[ScriptsPath]/$Cartridge";
    if (!is_dir($dname)) $dname="$cfg[PHPSBScriptsPath]/$Cartridge";
    if (is_dir($dname))
      {
      $d=opendir($dname);
      print "<tr><td class='tabhead' colspan='10'>$Cartridge</td></tr>";
      while (($file = readdir($d)) !== false)
        {
        list ($n1,$InterfaceName,$n3)=explode (".",$file);
        $MapMethods=false;
        if ( (($n1=='protected')&&($n3=='php')) || (($n1=='public')&&($n3=='php')))
          {
          $IClassName="$Cartridge.$InterfaceName";
          print "<tr class='tab'><td>$IClassName</td><td>";
          $intf=&$_ENV->LoadInterface($IClassName);

          $MapMethods=$intf->RoleAccess;
          print "<pre>";
          print_r($MapMethods);
          print "</pre></td><td>";
          if ($MapMethods)
            {
            $s="INSERT INTO um_ProtectIntf (Cartridge,InterfaceName) VALUES ('$Cartridge','$InterfaceName')";
            DBExec ($s);
            print "<font color='red'>'$Cartridge.$InterfaceName' is protected</font>";
            foreach ($MapMethods as $Role=>$MethodsList)
              {
              $RoleMethods[$Cartridge][$Role][$InterfaceName]=$MethodsList;
              }
            $newfile="protected.$InterfaceName.php";
            print "<br>$dname/$file<br>$dname/$newfile";
            }
          else
            {
            print "<font color='green'>'$Cartridge.$InterfaceName' has public access</font>";
            $newfile="public.$InterfaceName.php";
            print "<br>$dname/$file<br>$dname/$newfile";
            }
          rename("$dname/$file","$dname/$newfile");
          print "</td></tr>";
          }

        }
      closedir($d);
      }
    }


  print "</table>";
#    print "<pre>"; print_r($RoleMethods); print "</pre>";
  $qr=DBQuery ("SELECT GroupID,AllowMode,Cartridge,Role,AllowedMethods FROM um_GroupRoles");
#    $qr->Dump();

  if ($qr)
    {
    DBExec ("DELETE FROM um_GroupRoles");
    foreach ($qr->Rows as $i=>$row)
      {
      $Cartridge=$row->Cartridge;
      $GroupID=$row->GroupID;
      $AllowMode=$row->AllowMode;
      $Role=$row->Role;

      $AllowedMethods="";
      if ($AllowMode==1)
        {
        $intfs=&$RoleMethods[$Cartridge][$Role];
        if ($intfs)
          {
          foreach($intfs as $intf=>$MethodsList)
            {
            if ($AllowedMethods) $AllowedMethods.="|";
            $AllowedMethods.=$intf.':'.$MethodsList;
            }
          }
        }

      $s="INSERT INTO um_GroupRoles (AllowMode,GroupID,Cartridge,Role,AllowedMethods) VALUES ($AllowMode,$GroupID,'$Cartridge','$Role','$AllowedMethods')";
      DBExec ($s);
      }
    }
  }

function ApplyRoles($args)
  {
  $_=&$GLOBALS[_STRINGS][um];
  $__=&$GLOBALS[_STRINGS][_];
  global $cfg;
  extract(param_extract(array(
    UserGroupID=>'int',
    Allow=>'array',
    Deny=>'array',
    ),$args));

  if (!$UserGroupID)
    {
    return array(Error=>"UserGroupID does not defined!");
    }

  $cartridges=false;
  $AllowedMethods=false;

  if ($Allow) foreach ($Allow as $AllowID=>$x)
    {
    if (($Deny) && ($Deny[$AllowID])) {$Allow[$AllowID]=false; continue;}
    list ($Cartridge,$Role)=explode (":",$AllowID);
    $cartridges[$Cartridge]=1;
    }

  if ($cartridges)
    {
    print "<table>";
    foreach ($cartridges as $Cartridge=>$x)
      {
      DBExec ("DELETE FROM um_ProtectIntf WHERE Cartridge='$Cartridge'");
      $dname=$cfg['ComponentsPath']."/$Cartridge";
      if (is_dir($dname))
        {
        $d=opendir($dname);
        print "<tr><td class='tabhead' colspan='10'>$Cartridge</td></tr>";
        while (($file = readdir($d)) !== false)
          {
          list ($n1,$InterfaceName,$n3)=explode (".",$file);
          $MapMethods=false;
          if (($n1=='interface')&&($n3=='php3'))
            {
            $IClassName="$Cartridge.$InterfaceName";
            print "<tr class='tab'><td>$IClassName</td><td>";
            $intf=load_object_interface($IClassName);
            $MapMethods=$intf->RoleAccess;
            print_r($MapMethods);
            print "</td></tr>";
            }

          if ($MapMethods)
            {
            DBExec ("INSERT INTO um_ProtectIntf (Cartridge,InterfaceName) VALUES ('$Cartridge','$InterfaceName')");
            foreach ($MapMethods as $Role=>$MethodsList)
              {
              if ($Allow["$Cartridge:$Role"])
                {
                $s="$InterfaceName:$MethodsList";
                if ($AllowedMethods["$Cartridge:$Role"])
                  {
                  $AllowedMethods["$Cartridge:$Role"].="|$s";
                  }
                else
                  {
                  $AllowedMethods["$Cartridge:$Role"]=$s;
                  }
                }
              }
            }
          }
        closedir($d);
        }
      }
    }

  DBExec ("DELETE FROM um_GroupRoles WHERE GroupID=$UserGroupID");

  if ($Allow) foreach ($Allow as $RoleID=>$x)
    {
    if ($x)
      {
      list ($cart,$role)=explode (":",$RoleID);
      $s=$AllowedMethods[$RoleID];
      DBExec ("INSERT INTO um_GroupRoles (AllowMode,GroupID,Cartridge,Role,AllowedMethods) VALUES (1,$UserGroupID,'$cart','$role','$s')");
      }
    }

  if ($Deny) foreach ($Deny as $RoleID=>$x)
    {
    list ($cart,$role)=explode (":",$RoleID);
    DBExec ("INSERT INTO um_GroupRoles (AllowMode,GroupID,Cartridge,Role) VALUES (2,$UserGroupID,'$cart','$role')");
    }
   print "<a href=".ActionURL('um.IUserGroups.Browse.bm').">$__[CAPTION_OK]</a>";
#   return array (ForwardTo=>ActionURL("um.IUserGroups.Browse.bm"));
  }
}
