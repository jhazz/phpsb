<?php
if (ACCEPT_INCLUDE!=1) {die ("You can't access this file directly"); }
##
## TDatabase
## ----------
##
## Class MySQL database abstraction layer
##
##   ->Free() - drop data if exists
##   ->Dump() - dumps data content in table
##   ->Rows[] - data array of rows
##   ->RowCount - number of rows
##   ->Error - last error occured
##   ->Fields[] - fields information
##   ->FieldCount - number of fields amount
##   ->Top - top row of queried data

class TDatabase
{
var $Active;
var $Error;
var $Type;
var $Dbase;
var $Link;

function Connect($address,$dbase,$user="",$pass="")
  {
  $this->Active=false;
  $this->Dbase=$dbase;
  $this->Link= mysql_connect ($address,$user,$pass);
  if (!$this->Link)  {
  	$this->Error=mysql_error();
  	$this->ErrNo=mysql_errno();
#  	print "<p>$this->Error</p>"; 
  	return false;
  }
  if (!mysql_select_db($dbase, $this->Link)) {$this->Error=mysql_error($this->Link); print $this->Error; return false;}
  mysql_unbuffered_query("SET CHARACTER SET 'utf8'",$this->Link);
	mysql_unbuffered_query("SET NAMES 'utf8'",$this->Link);
  $this->Type="MySQL";
  $this->Active=true;
  return true;
  }

function Exec ($QueryString,$NoPrintErrors=false)
  {
  global $cfg;
  if (($cfg['TraceSQL'])&&function_exists('trace')) trace ($QueryString,3);
  $r=mysql_db_query ($this->Dbase,$QueryString);
  if ($r) { return $r; }
  else {
    $this->Error=mysql_error();
    if ($cfg['Dbase']['ShowErrors'])
      {
      if (($cfg['TraceSQL'])&&function_exists('trace'))
        {
        trace ("SQL Error: ".$this->Error,2);
        trace ("In Query : ".$QueryString,2);
        }
      print_error ($this->Error,$QueryString);
      }
    return false;
    }
  }
function Insert ($args)
{
	# Table
	# Values
	# Delayed
	# Debug - just print database query instead of executing
	# ToString - returns database query string instead of executing
	# GetAutoInc
	
  $Table=$args['Table'];
  if (!$Table) {print_developer_warning("Missing argument Table in function Insert"); return false;}
  if (!is_array($args['Values'])) {print_developer_warning("Missing argument Values in function Insert"); return false;}

  $s1=$s2="";
  foreach ($args['Values'] as $k=>$v)
    {
    if ($v===NULL) continue;
    $s1.=(($s1)?",":"").("`$k`");
    $s2.=(($s2)?",":"").((is_string($v))?"'".$this->Escape($v)."'":$v);
    }
  $d=($args['Delayed'])?"DELAYED":"";
  $s="INSERT $d INTO `$Table` ($s1) VALUES ($s2)";
  if ($args['Debug']) {print "<table><tr><td>$s</td></tr></table>";return true;}
  if ($args['ToString']) return $s;
  $r=$this->Exec($s,true);
  if (!$r) return false;
  if ($args['GetAutoInc']) {return mysql_insert_id();}
	return true;
}

function Update ($args)
{
	$Debug=$args['Debug'];
  $Table=$args['Table'];
  if (!$Table) {print_developer_warning("Missing argument Table in function Update"); return false;}
  if (!is_array($args['Keys'])) {print_developer_warning("Missing argument Keys in function Update"); return false;}
  if (!is_array($args['Values'])) {print_developer_warning("Missing argument Values in function Update"); return false;}
  $s1=$s2="";
  foreach ($args['Values'] as $k=>$v)
    {
	    if ($s1) $s1.=",";
    	if ($v===NULL) $s1.="$k=NULL";
    	else $s1.=(is_string($v))?"`$k`='".$this->Escape($v)."'":"`$k`=$v";
    }
  foreach ($args['Keys'] as $k=>$v)
    {
    $s2.=(($s2)?" AND ":"").((is_string($v))?"`$k`='".$this->Escape($v)."'":"`$k`=$v");
    }
  $d=($args['Delayed'])?"LOW_PRIORITY":"";
  $s="UPDATE $d `$Table` SET $s1 WHERE $s2";
  if ($args['Debug']) {print "<table><tr><td>$s</td></tr></table>";return true;}
  if ($args['ToString']) return $s;
  $r=$this->Exec($s,true);
  if (!$r)return false;
	return true;
}

function Replace ($args)
  {
  # Table - table name
  # Values -
  # Keys
  # Debug
	$__=&$GLOBALS['_STRINGS']['_'];


  $Table=$args['Table'];
  if (!$Table) {print_developer_warning("Undefined argument Table"); return;}
  if (!$args['Values']) {print_developer_warning("Undefined argument Values"); return;}
  if (!$args['Keys']) {print_developer_warning("Undefined argument Keys"); return;}

  foreach ($args['Values'] as $k=>$v)
    {
    if ($v===NULL) continue;
    $s.=(($s)?",":"").((is_string($v))?"`$k`='$v'":"`$k`=$v");
    }

  foreach ($args['Keys'] as $k=>$v)
    {
    if (!$v)
      {
      if ($v===NULL)
        {
        print_developer_warning("Primary key [$Table.$k] not set");
        return;
        }
      $v=0;
      }
    $s.=(($s)?",":"").((is_string($v))?"`$k`='$v'":"`$k`=$v");
    }
  $s="REPLACE INTO $Table SET $s";
  if ($args['Debug']) {print "<table><tr><td>$s</td></tr></table>";return true;}
  $r=$this->Exec($s,true);
  if (!$r)
    {
    $fields=mysql_list_fields($this->Dbase,$Table,$this->Link);
    $fieldsCount=mysql_num_fields($fields);
    for ($i=0;$i<$fieldsCount;$i++)
      {
      $fname=mysql_field_name($fields,$i);
      $ftype=mysql_field_type($fields,$i);
      $fflags=explode (" ",mysql_field_flags($fields,$i));
      $notnull=(array_search("not_null",$fflags)!==false);
      $pkey=(array_search("primary_key",$fflags)!==false);
      if (($pkey) && (!isset($args['Keys'][$fname])))
        {
        $errors.="Warning: Field[$fname] is primary key but absent in Keys; \n";
        }
      $ufield=$args['Values'][$fname];
      if (!isset($ufield)) $ufield=$args['Keys'][$fname];
      if ($notnull)
        {
        if (!isset($ufield))
          {
          $errors.="Error: Not specified value of NOT_NULL field [$fname]; \n";
          }
        }
      if ((ftype=='string')||(ftype=='blob'))
        {
        if (!is_string($ufield)) {$errors.="Error: Field[$fname] should be 'string' but received value is '".gettype($ufield)."' ;\n" ;}
        }
      else
        {
        if (is_string($ufield)) {$errors.="Error: Field[$fname] should be '$ftype' but received is '".gettype($ufield)."' ;\n" ;}
        }
      }
    print_error("Error in REPLACE INTO table '$Table'",$errors);
    return false;
    }
  else
    {
    return true;
    }
  }

function &QueryResult2Recordset ($r,$keyfields=false)
  {
  global $cfg;
  $Recordset=new TRecordset;
  if (!$r)
    {
    return false;
    }
  $Recordset->Handle=$r;
  $Recordset->Database=$this;
  $Recordset->FieldCount=mysql_num_fields($r);
  $Recordset->RowCount=0;
  $keyindex="";

  # Check for key existance in the query
  for ($i=0;$i<$Recordset->FieldCount;$i++)
    {
    $f=mysql_field_name ($r,$i);
    $Recordset->Fields[$i]=$f;
    $Recordset->FieldType[$f]=mysql_field_type($r,$i);
    $Recordset->FieldFlags[$f]=mysql_field_flags($r,$i);
    }

  $s="";
  if ($keyfields)
    {
    if (!is_array($keyfields))
      {
      $keyfields=array($keyfields);
      }

    $Recordset->KeyFields=$keyfields;
    $found=0;
    foreach ($Recordset->Fields as $f)
      {
      foreach ($keyfields as $keyfield)
        {
        if ($f==$keyfield) {$found++;}
        }
      }

    if ($found<count($keyfields))
      {
      if ($cfg['Dbase']['ShowErrors'])
        {
        if (is_array($keyfield)) {$s=implode (",",$keyfield);} else {$s=$keyfield;}
        print_error ("Requested keys not found in the query result",$s);
        return false;
        }
      }

    $s1="";
    foreach ($keyfields as $i=>$keyfield)
      {
      $s.="[\$row->$keyfield]";
      }
    $s=$s1.'$Recordset->Rows'.$s.'=$row;';
    }
  else
    {
    $s='$Recordset->Rows[$Recordset->RowCount]=$row;';
    }

  $Recordset->RowCount=0;
  while($row = mysql_fetch_object($r))
    {
    eval($s);
    if (!$Recordset->RowCount) {$Recordset->Top=$row;}
    $Recordset->RowCount++;
    }
  if (!$Recordset->RowCount) {return false;}
  return $Recordset;
  }

function &Query ($QueryString,$keyfield=false,$LimitTop=0,$LimitRows=0)
  {
  global $cfg;
  $r=mysql_query ($QueryString,$this->Link);

  if ($r===false)
    {
    $this->Error=mysql_error($this->Link);
    if ($cfg['Dbase']['ShowErrors'])
      {
      print_error ($this->Error,$QueryString);
      }
    if (($cfg['TraceSQL'])&& function_exists('trace'))
      {
      trace ("Error:$this->Error",2);
      trace ($QueryString,2);
      }
    }
  else
    {
    if (($cfg['TraceSQL']) && function_exists('trace')) trace ($QueryString,3);
    }
  return $this->QueryResult2Recordset($r,$keyfield);
  }
  
function Query2Array ($QueryString,&$vars,$keyfields) {
  global $cfg;
  $r=mysql_query ($QueryString,$this->Link);

  if ($r===false){
  	$this->Error=mysql_error($this->Link);
  	if ($cfg['Dbase']['ShowErrors']) {
  		print_error ($this->Error,$QueryString);
  	}
  	if (($cfg['TraceSQL'])&& function_exists('trace')){
  		trace ("Error:$this->Error",2);
  		trace ($QueryString,2);
  	}
  	return false;
  } else {
  	if (($cfg['TraceSQL']) && function_exists('trace')) trace ($QueryString,3);
  }

  $indexByName=$indexByID=$indexByID2="";
  if ($keyfields) {
  	if (!is_array($keyfields)) {$keyfields=array($keyfields); }
  	foreach ($keyfields as $i=>$kf) {
			list($k1,$k2)=explode (':',$kf);
			$keyfields[$i]=$k1;
  		$indexByID.="[\$row['$k1']]"; 
  		if ($k2) {
  			$indexByName.="['ByName'][\$row['$k2']]"; $indexByID2.="['ByID'][\$row['$k1']]";
  		} else {
  			$indexByName.="[\$row['$k1']]"; $indexByID2.="[\$row['$k1']]";
  		}
  	}
  }
  
  $eval="\$vars$indexByID=\$row;";
  if ($indexByID!=$indexByName) $eval="\$vars$indexByName=\$row; \$vars$indexByID2=&\$vars$indexByName;";
  
	$fieldCount=mysql_num_fields($r);
	
	$rowNo=0;
  while($row = mysql_fetch_assoc($r)){
  	if ($indexByID) {
  		if (!$rowNo) { 
  			foreach ($keyfields as $k) {
  				if (!isset($row[$k])) {
  					print_error ("Query has no key field [$k]",$QueryString);
  					return false;
  				}
  			}
  		}
  		eval ($eval);
  	} else {
  		$vars=$row;
  	}
  }
  return true;
}
  

function GetNewID($ClassName,$Context="",$Object="",$Increment=1)
  {
  $c="$ClassName/$Context";
  if ($Object) $c.="/$Object";

  global $Database;
  $NewID=1500;
  $q2=DBQuery ("SELECT NextUID FROM sys_UID WHERE UIDContext='$c'");
  if ($q2)
    {
    $NewID=$q2->Top->NextUID;
    DBExec ("UPDATE sys_UID SET NextUID=".($NewID+$Increment)." WHERE UIDContext='$c'");
    }
  else
    {
    DBExec ("INSERT INTO sys_UID (UIDContext,NextUID) VALUES ('$c',".($NewID+$Increment).")");
    }
  return $NewID;
  }

function Escape($str,$FromPostOrGet=false)
  {
  if ($FromPostOrGet && (get_magic_quotes_gpc())) return $str;
  return mysql_escape_string($str);
  }
}
# End of class TDatabase

?>
