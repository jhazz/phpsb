<?
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
define ('ACCEPT_INCLUDE',1);

$_FRONTDOOR="";
if ($_GET['passargs'])
{
	list ($srcpath,$_FRONTDOOR,$oldget)=explode("|",$_GET['passargs'],3);
	if ($oldget) { parse_str ($oldget,$oldget); $_GET+=$oldget;}
	unset($_GET['passargs']);
}
if (!$srcpath)
{
	print "Undefined source path comes from .htaccess";
	exit;
}

require_once ("$srcpath/inc.config.php");
if ($_FRONTDOOR)
{
	$i=strrpos($_FRONTDOOR,'/');
	if ($i!==false) $_FRONTDOOR=substr($_FRONTDOOR,0,$i);
	$_FRONTDOOR="/".$_FRONTDOOR;
	$_SERVER['FRONTDOOR']=$_FRONTDOOR;
}
$cfg=array();
if (is_array($altcfg))overload_cfg();

$_SERVER['PATH_INFO']=trim_frontdoor_string($_SERVER["REQUEST_URI"],$cfg['SiteURL'],$_FRONTDOOR);
if (!$cfg['PHPSB_VERSION']) $cfg['PHPSB_VERSION']="active";
include("$cfg[PHPSB_PATH]/ver/".$cfg['PHPSB_VERSION']."/inc.autoconfig.php");


switch($_FRONTDOOR)
{
	case '/admin':
		header ('Pragma: no-cache');
		header ('Cache-Control: no-cache');
		$href=$cfg['ActionURL'].'/backend.ICover.View.b';
		if ($cfg['UseSSLBackend']) $href=str_replace('http://','https://',$href);
		print "<style>
    a{text-decoration:none; color:#a0a0a0; font-size:8px;font-weight:bold; font-family:verdana,arial,helvetica,sans-serif;}
    a:hover{text-decoration:underline; color:#f0a0a0;}
    </style>
    <center><br/><a style='decoration:none' href='$href'>&nbsp;</a><meta http-equiv='Refresh' Content='1; url=$href'>";
		exit;

	case '/error':
		#  print "<hr>";
		#    print $_SERVER['PATH_INFO'];
		#    print_r($_GET);

		switch($_GET['error'])
		{
			case 404:
				header("HTTP/1.0 404 Not found",true);
				exit;
			case 403:header("HTTP/1.0 403 Not found",true); print "<h1>403 Page not found</h1>"; break;
			case 405:header("HTTP/1.0 405 Not found",true); print "<h1>405 Page not found</h1>"; break;
			default : print "<h1>$_GET[error] Error: Page not found</h1>"; break;
		}
		if ($_GET['from']) print "<p>$_GET[from]</p>"; else print "<p>".$_SERVER['REQUEST_URI']."</p>";
		exit;
		break;
	case '/edit':
		$_ENVIRONMENT='f';  # means frontend
		$_DESIGN_MODE=1;    # checks by pageloader
		break;
	case '/scripts/do':
		require_once ("$cfg[PHPSBScriptsPath]/sys/inc.doaction.php");
		exit;
		break;
	default:
		$_ENVIRONMENT='f';  # means frontend
		if (!$_FRONTDOOR)
		{
			# Call door keeper to open the door
			if ($cfg['OpeningDoor'])
			{
				list($domain,$_FRONTDOOR)=explode("/",$cfg['OpeningDoor'],2);
				$_FRONTDOOR='/'.$_FRONTDOOR;
				if (isset($altcfg["FRONTDOOR=$_FRONTDOOR"])) $cfg=$altcfg["FRONTDOOR=$_FRONTDOOR"]+$cfg;
#				print "<h1>OPEN DOOR $_FRONTDOOR</h1>";
				if ($domain)
				{
					$cfg['SiteURL']=$domain;
					if (isset($altcfg["HTTP_HOST=$domain"])) $cfg=$altcfg["HTTP_HOST=$domain"]+$cfg;
					#          print "<h1>OPEN DOOR TO DOMAIN $domain</h1>";
				}
			}
			else
			{
				$_FRONTDOOR='/en';
			}
		}
		$_THEME_NAME=$cfg['Theme'];
		if (!front_door_exists($_FRONTDOOR))
		{
			header("HTTP/1.0 404 Not found",true);
			exit;
		}
		break;
}
require_once ($cfg['PHPSBScriptsPath']."/jsb/inc.pageloader.php");

function front_door_exists($fdoorpath)
{
	global $cfg;
	if (substr($fdoorpath,0,1)!='/') return true;
	$fdoorsfile=$cfg['DataPath'].'/.frontdoors';
	if (substr($fdoorpath,0,1)=='/') $fdoor_name=substr($fdoorpath,1);
	if (is_file($fdoorsfile))
	{
		$frontdoors=file($fdoorsfile);
		foreach($frontdoors as $fdoor)
		{
			$fdoor=trim($fdoor);if (!$fdoor)continue;
			if ($fdoor==$fdoor_name) {return true;}
		}
		return false;
	} else {if ($fdoorpath=='/en') return true;}
	return false;
}

function overload_cfg()
{
	global $cfg,$altcfg;$cfg=array();foreach($altcfg as $cond=>$acfg){
		$p=explode("=",$cond,2);
		if((isset($p[1]) && (preg_match("'".addslashes($p[1])."'i",$_SERVER[$p[0]])||($p[1]==$_SERVER[$p[0]])))||($cond=="main")){
			$cfg=$acfg+$cfg;
		}}
}


/**
 * Parses REQUEST_URI string and trim out local folder and frontdoor prefixes
 *
 * @example  [/mysitefolder[/de/]]news/12.html  ->  /news/12.html
 * @param string $s
 * @param string $siteurl
 * @param string $fdoor
 * @return string adapted PATH_INFO w/o folder and frontdoor prefixes
 */
function trim_frontdoor_string($requesturi, $siteurl, $fdoor)
{
	list ($domain,$folder)=explode("/",$siteurl,2);
	if ($folder)
	{
		$trimfirst="/$folder";
		if (substr($requesturi,0,strlen($trimfirst))==$trimfirst) $requesturi=substr($requesturi,strlen($trimfirst));
	}
	if ($fdoor)
	{
		$trimfirst=$fdoor;
		if (substr($requesturi,0,strlen($trimfirst))==$trimfirst) $requesturi=substr($requesturi,strlen($trimfirst));
	}
	return $requesturi;
}

?>

