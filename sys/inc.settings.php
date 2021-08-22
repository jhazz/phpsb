<?php
class Settings
{
var $Data=array();

function Load($loadDefaults=false)
  {
  global $cfg;
  $fname=$cfg['DataPath'].'/.settings';
  if (!isset($this->Settings))
    {
    if (is_file($fname))
      {
      $fp=fopen ($fname,'rb');
      $this->Data=unserialize(fread($fp,15000));
      fclose($fp);
      }
    else $loadDefaults=true;
    }

  if (($loadDefaults)&&(method_exists($_ENV,"LoadCartridgesList")))
    {
    $cartridges=&$_ENV->LoadCartridgesList(true);
    foreach($cartridges as $cartridgeName=>$active)
      {
      if ($active)
        {
        if (!isset($this->Settings[$cartridgeName]))
          {
          $cartridge=&$_ENV->LoadCartridge($cartridgeName);
          if (!method_exists($cartridge,"Settings")) continue;
          $sd=&call_user_method("Settings",$cartridge); # load settings description
          foreach($sd as $Name=>$desc)
            {
            $this->Data[$cartridgeName][$Name]=$desc['DefaultValue'];
            }
          }
        }
      }
    }
  $cfg['Settings']=&$this->Data;
  return true;
  }

function Save()
  {
  if (!$this->Data) return;
  global $cfg;
  $fname=$cfg['DataPath'].'/.settings';
  $fp=fopen ($fname,'w');
  fputs ($fp,serialize($this->Data));
  fclose($fp);
  return true;
  }

function phpstr($name,$value,$tab=0)
  {
  if (!$value) return "";
  if ($tab) $name="'$name'";
  $sp="";for($i=0;$i<$tab;$i++){$sp.="  ";}
  $eq=($tab)?"=>":"=";
  $s="";
  if (is_array($value))
    {
    foreach ($value as $k=>$v)
      {
      $s.=$this->phpstr($k,$v,$tab+1);
      }
    if (!$s) return "";
    $s="$sp$name$eq"."array(\n$s$sp)";
    }
  elseif (("".intval($value))===("".$value))
    $s="$sp$name$eq$value";
  else
    $s="$sp$name$eq'$value'";

  $s.=(($tab)?",":";")."\n";
  return $s;
  }


function _updateConfigAndHtaccess()
  {
  global $altcfg,$SITES;

  $infostr="";
  $SiteRootPath=$altcfg['main']['RootPath'];
  $DataPath=$SiteRootPath.'/data';
  $fdoors=false;
  if (is_readable($DataPath.'/.frontdoors'))
    {
    $fdoors=file($DataPath.'/.frontdoors');
    }
  if (!$altcfg)
    {
    return array(Error=>"Config file not loaded to memory");
    }
  $fdoorstr="";
  $possibledoor="";
  if ($fdoors) foreach ($fdoors as $fdoorid)
    {
    $fdoorid=trim($fdoorid);
    if (!$possibledoor) $possibledoor="/$fdoorid";
    if ($fdoorid) $fdoorstr.="($fdoorid/)|";
    } else {$possibledoor='/en';}
  if (!$altcfg['main']['OpeningDoor']) $altcfg['main']['OpeningDoor']=$possibledoor;


  $cfgs="<"."?\n".$this->phpstr('$altcfg',$altcfg)."\n?".">";

  if (!is_dir($SiteRootPath)) return array(Error=>"Config error: RootPath='$SiteRootPath' is not a directory");
  if (file_exists("$SiteRootPath/inc.config.php"))
    {
    if (file_exists("$SiteRootPath/~inc.config.php")) unlink ("$SiteRootPath/~inc.config.php");
    rename("$SiteRootPath/inc.config.php","$SiteRootPath/~inc.config.php");
    $infostr="Previous config back up to '~inc.config.php'<br><br>";
    }
  $fp=fopen ("$SiteRootPath/inc.config.php","w");
  if (!$fp) return array(Error=>"Unable to write to file '$SiteRootPath/inc.config.php'");
  fputs ($fp,$cfgs);
  fclose ($fp);
  $infostr.="Configuration file updated<br><br>";


  $hts="\n\n\n";
  if (is_readable("$SiteRootPath/.htaccess"))
    {
    $hts=implode(file("$SiteRootPath/.htaccess"));
    $p=strpos($hts,"## AUTOBUILD SECTION ##");
    if ($p!==false) $hts=substr($hts,0,$p); else $hts="";
    }
  $phpsbpath=preg_replace("/\\\\+/","/",$altcfg['main']['PHPSB_PATH']);
  # exlude home
  $root=preg_replace("/\\\\+/","/",$_SERVER['DOCUMENT_ROOT']);
  if (substr($root,-1)=='/') $root=$substr($root,0,-1);
  $i=strpos(strtolower($phpsbpath),strtolower($root));
  if ($i!==false) $phpsbpath=substr($phpsbpath,strlen($root));

  list($sitehost,$sitefolder)=explode("/",$altcfg['main']['SiteURL'],2);
  if ($sitefolder) $sitefolder='/'.$sitefolder;
  $SiteRootPath=preg_replace("/\\\\+/","/",$altcfg['main']['RootPath']);


  $pversion=$altcfg['main']['PHPSB_VERSION'];
  if (!$pversion) $pversion='active';
  $hts.="## AUTOBUILD SECTION ##
RewriteEngine on
RewriteCond %{"."REQUEST_URI} ^$sitefolder/public/([a-z0-9_]+)/(.*)\$
RewriteRule ^public/([a-z0-9_]+)/(.*)\$ $phpsbpath/ver/$pversion/\$1/public/\$2 [L]

RewriteCond %{"."REQUEST_URI} ^$sitefolder/($fdoorstr(edit/)|(error/)|(admin)|(scripts/do/))(.*)\$
RewriteRule ([^/]*)($fdoorstr(edit/)|(error/)|(admin)|(scripts/do/))(.*)\$ $phpsbpath/ver/$pversion/dispatcher.php?passargs=$SiteRootPath|\$2|%{"."QUERY_STRING} [L]

RewriteCond %{"."REQUEST_URI} ^(($sitefolder)|($sitefolder/))\$
RewriteRule (.*)\$ $phpsbpath/ver/$pversion/dispatcher.php?passargs=$SiteRootPath||%{"."QUERY_STRING} [L]

ErrorDocument 403 $phpsbpath/ver/$pversion/dispatcher.php?passargs=$SiteRootPath|error/|error=403
ErrorDocument 404 $phpsbpath/ver/$pversion/dispatcher.php?passargs=$SiteRootPath|error/|error=404
ErrorDocument 405 $phpsbpath/ver/$pversion/dispatcher.php?passargs=$SiteRootPath|error/|error=405
ErrorDocument 500 $phpsbpath/ver/$pversion/dispatcher.php?passargs=$SiteRootPath|error/|error=500
";
  if (file_exists("$SiteRootPath/.htaccess"))
    {
    if (file_exists("$SiteRootPath/~.htaccess")) unlink ("$SiteRootPath/~.htaccess");
    rename("$SiteRootPath/.htaccess","$SiteRootPath/~.htaccess");
    $infostr.="Previous .htaccess back up to '~.htaccess'<br><br>";
    }
  $fp=fopen ("$SiteRootPath/.htaccess","w");
  if (!$fp)
    {
    $infostr.="Unable to write to file '$SiteRootPath/.htaccess'";
    return false;
    }
  fputs ($fp,$hts);
  fclose ($fp);
  $infostr.="inc.config.php and .htaccess files are updated successfully";
  return array(Message=>$infostr);
  }
}

$_SETTINGS=new Settings();
$_SETTINGS->Load();


?>
