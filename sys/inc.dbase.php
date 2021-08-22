<?php
if (ACCEPT_INCLUDE!=1) {die ("You can't access this file directly"); }
$cfg['DbaseType']['MySQL']['PHPModule']="inc.mysql.php";
$cfg['DbaseType']['MySQL3']['PHPModule']="inc.mysql.php";
$cfg['DbaseType']['MySQL4']['PHPModule']="inc.mysql.php";

# Example for other types
# $cfg['DbaseType']['ODBC'][PHPModule]="odbc.inc.php";
# $cfg['DbaseType']['Oracle'][PHPModule]="oracle.inc.php";

if (!$cfg['Dbase']['Type']) $cfg['Dbase']['Type']='MySQL';

$DbasePHPModule=$cfg['PHPSBScriptsPath'].'/sys/'.$cfg['DbaseType'][$cfg['Dbase']['Type']]['PHPModule'];
if (!$DbasePHPModule){
  print "<h1>Error</h1>Missing SQL-server type :'".$cfg['Dbase']['Type']."'<br/>Check out <b>inc.config.php</b> and set correct type.";
  exit;
}
  
require_once ($DbasePHPModule);
$Database=new TDatabase;
$Database->Connect($cfg['Dbase']['Host'],$cfg['Dbase']['Name'],$cfg['Dbase']['Login'],$cfg['Dbase']['Password']);



##
## TRecordSet
## ----------
##
##   Keeps indexed data loaded by TDatabase->Query
##
##   ->Free() - drop data if exists
##   ->Dump() - dumps data content in table (one key only)
##   ->Rows[] - data array of rows (each row is object)
##   ->RowCount - number of rows
##   ->Error - last error occured
##   ->Fields[] - fields information
##   ->FieldCount - number of fields amount
##   ->Top - top row of queried data (object)
##

  function utf2html ($str) {
   
    $ret = "";
    $max = strlen($str);
    $last = 0;  // keeps the index of the last regular character
    for ($i=0; $i<$max; $i++) {
        $c = $str{$i};
        $c1 = ord($c);
        if ($c1>>5 == 6) {  // 110x xxxx, 110 prefix for 2 bytes unicode
            $ret .= substr($str, $last, $i-$last); // append all the regular characters we've passed
            $c1 &= 31; // remove the 3 bit two bytes prefix
            $c2 = ord($str{++$i}); // the next byte
            $c2 &= 63;  // remove the 2 bit trailing byte prefix
            $c2 |= (($c1 & 3) << 6); // last 2 bits of c1 become first 2 of c2
            $c1 >>= 2; // c1 shifts 2 to the right
            $ret .= "&#" . ($c1 * 0x100 + $c2) . ";"; // this is the fastest string concatenation
            $last = $i+1;      
        }
        elseif ($c1>>4 == 14) {  // 1110 xxxx, 110 prefix for 3 bytes unicode
            $ret .= substr($str, $last, $i-$last); // append all the regular characters we've passed
            $c2 = ord($str{++$i}); // the next byte
            $c3 = ord($str{++$i}); // the third byte
            $c1 &= 15; // remove the 4 bit three bytes prefix
            $c2 &= 63;  // remove the 2 bit trailing byte prefix
            $c3 &= 63;  // remove the 2 bit trailing byte prefix
            $c3 |= (($c2 & 3) << 6); // last 2 bits of c2 become first 2 of c3
            $c2 >>=2; //c2 shifts 2 to the right
            $c2 |= (($c1 & 15) << 4); // last 4 bits of c1 become first 4 of c2
            $c1 >>= 4; // c1 shifts 4 to the right
            $ret .= '&#' . (($c1 * 0x10000) + ($c2 * 0x100) + $c3) . ';'; // this is the fastest string concatenation
            $last = $i+1;      
        }
    }
    $str=$ret . substr($str, $last, $i); // append the last batch of regular characters
    return $str;
} 

class TRecordset
  {
  var $Fields;
  var $FieldCount;
  var $Rows;
  var $RowCount;
  var $Top;
  var $Handle;
  var $KeyFields;

  function TRecordset() {$this->Handle=false;}

  function Free () {if ($this->Handle) {mysql_free_result ($this->Handle); $this->Handle=false; } }

  function Dump ()
    {
    # TOOOOOOO OLD !!!!!
    
    echo "<table border=1 cellspacing=0><tr>";
    for ($i=0;$i<$this->FieldCount;$i++)
      { echo "<td><b>".$this->Fields[$i]."</b></td>"; }
    echo "</tr>";

    # $this->KeyFields
    foreach ($this->Rows as $i=>$row)
      {
      echo "<tr>";
      for ($j=0;$j<$this->FieldCount;$j++)
        {
        $rname=$this->Fields[$j];
        $s=$row->$rname;
/*        for ($i=0;$i<strlen($s);$i++)
        {
        	$c=substr($s,$i,1);
        	$ss.="[$c-".ord($c)."]";
        }*/
        echo "<td>$s</td>";
        }
      echo "</tr>";
      }
    echo "</table>";
    }
  }

function DBGetID($ClassName,$Context="",$Object="",$Increment=1)
  {
  ##  returns unique id for named numeric space: 'cartridge.class/context'
  return $GLOBALS['Database']->GetNewID($ClassName,$Context,$Object,$Increment);
  }
function &DBQuery ($str,$GroupingKeys=false,$LimitTop=0,$LimitRows=0)
  {
  return $GLOBALS['Database']->Query ($str,$GroupingKeys,$LimitTop=0,$LimitRows=0);
  }
function DBExec ($str)
  {
  return $GLOBALS['Database']->Exec ($str);
  }
function DBEscape ($str,$FromPostOrGet=false)
  {
  return $GLOBALS['Database']->Escape($str,$FromPostOrGet);
  }
function DBReplace($args)
  {
  return $GLOBALS['Database']->Replace($args);
  }
function DBInsert($args)
{
  return $GLOBALS['Database']->Insert($args);
}
function DBUpdate($args)
{
  return $GLOBALS['Database']->Update($args);
}
function DBQuery2Array ($QueryString,&$vars,$keyfields=false) {
	return $GLOBALS['Database']->Query2Array($QueryString,$vars,$keyfields);
}
?>
