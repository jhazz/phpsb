<?
$cfg['VirtualExtension']='html';

list($cfg['SiteURL'],$cfg['RootURL'])=explode ("/",$cfg['SiteURL'],2);
if ($cfg['RootURL']) $cfg['RootURL']="/".$cfg['RootURL'];
$cfg['DefaultSysSkin']   ='sys_green';
$cfg['PHPSBScriptsPath'] =$cfg['PHPSB_PATH'].'/ver/'.$cfg['PHPSB_VERSION'];
$cfg['ScriptsPath']      =$cfg['RootPath']."/scripts";
$cfg['ActionURL']        ="http://".$cfg['SiteURL'].$cfg['RootURL']."/scripts/do";
$cfg['EditPageURL']      =$cfg['RootURL']."/edit";
$cfg['MailTemplatesPath']=$cfg['RootPath']."/mail_templates";

$cfg['DataPath']         =$cfg['RootPath']."/data";
$cfg['PublicURL']        =$cfg['RootURL'] ."/public";
$cfg['FilesPath']        =$cfg['RootPath']."/files";
$cfg['FilesURL']         =$cfg['RootURL'] ."/files";
$cfg['FilesRelativeURL'] ="../../files"; # relative from page content. Used by RichText

$cfg['ThemesPath']      =$cfg['RootPath']."/themes";
$cfg['ThemesURL']       =$cfg['RootURL' ]."/themes";

$cfg['SkinsPath']       =$cfg['RootPath']."/skins";
$cfg['SkinsURL']        =$cfg['RootURL' ]."/skins";

$cfg['BackupsPath']     =$cfg['RootPath']."/_backups";
$cfg['TempPath']        =$cfg['RootPath']."/tmp";
$cfg['TempURL']         =$cfg['RootURL']."/tmp";

# ResourceName => array(Path,DirMode,FileMode)
$cfg['Resources']=array(
  'scripts' =>array($cfg['ScriptsPath'],0777,0777),
  'phpsb'   =>array($cfg['PHPSBScriptsPath'],0777,0777),
  'themes'  =>array($cfg['ThemesPath'],0777,0777),
  'skins'   =>array($cfg['SkinsPath'],0777,0777),
  'data'    =>array($cfg['DataPath'],0777,0777),
  'files'   =>array($cfg['FilesPath'],0773,0777),
  'root'    =>array($cfg['RootPath'],0777,0777),
  'other'   =>array($cfg['RootPath'],0777,0777),
  'backups' =>array($cfg['BackupsPath'],0777,0777),
  'tmp'     =>array($cfg['TempPath'],0777,0777),
  );

$cfg['Session']=array(
  'Timeout'             => 60*60*24,
  'KeepStatisticTimeout'=> 60*60*24 * 3,
  'CookieLife'          => 60*60*24 * 90, # 3 months of [x]Remember my password
  'CookieURL'           => $cfg['RootURL']."/");

?>
