<?

class sys_LANGUAGE_DISPATCHER {
	var $Languages;
	
	function &LoadLanguages()
	{
		if ($this->Languages) return $this->Languages;
		global $cfg;
		$f=$cfg['DataPath'].'/.languages';
		if (is_file($f))
		{
			$Lines=file($f);
			foreach ($Lines as $line)
			{
				$line=trim($line);
				if ((!$line)||(substr($line,0,1)=='#')) continue;
				list($LangID,$Enabled,$Caption)=explode (":",$line,4);
				if ($LangID) $this->Languages[$LangID]=array(Enabled=>$Enabled,Caption=>$Caption);
			}
		}
		return $this->Languages;
	}

	# return array(LangID(tinyint), LanguageCaption(string))
	function GetLanguageByName($Language) {
		if (!$this->Languages) $this->LoadLanguages();
		return $this->Languages[$Language];
	}

	function LoadLanguage($CartridgeName=""){
		global $cfg,$_LANGUAGE;
		if (!$_LANGUAGE) {
			$_LANGUAGE=$cfg['Language'];
			if ($_COOKIE['DefaultLanguage']) $_LANGUAGE=$_COOKIE['DefaultLanguage'];
		}
		if ($CartridgeName)
		{
			$dir="$cfg[ScriptsPath]/$CartridgeName";
			if (!is_dir($dir)) $dir="$cfg[PHPSBScriptsPath]/$CartridgeName";
			$langfile1="$dir/$CartridgeName/lang.$_LANGUAGE.php";
			$langfile2="$dir/$CartridgeName/lang.en.php";
		}
		else
		{
			$langfile1="$cfg[ScriptsPath]/lang.$_LANGUAGE.php";
			if (!file_exists($langfile1)) $langfile1="$cfg[PHPSBScriptsPath]/lang.$_LANGUAGE.php";
			$langfile2="$cfg[PHPSBScriptsPath]/lang.en.php";
		}

		if (!file_exists($langfile1)) {
			if (!file_exists($langfile2)) {
				print "<h1>Language '$_LANGUAGE' file not found</h1>Required file: $langfile<br>English language file not found too: '$langfile2'";
				return false;
			}
			$langfile1=$langfile2;
		}
		require_once ($langfile1);
		return true;
	}
}

$GLOBALS['_LANGUAGE_DISPATCHER']=new sys_LANGUAGE_DISPATCHER();
$_LANGUAGE_DISPATCHER->LoadLanguage();

?>