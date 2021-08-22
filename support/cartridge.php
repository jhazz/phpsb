<?
class support
{
	function support()
	{
		$_=&$GLOBALS['_STRINGS']['support'];
		$this->Title=$_['SUPPORT_CARTRIDGE_TITLE'];
		$this->Roles=array
		(
		Administrtor=>$_['ROLE_ADMINISTRATOR'],
		Service=>$_['ROLE_SERVICE'],
		);
	}

	function Controls()
	{
		$_=&$GLOBALS['_STRINGS']['support'];
		return array();
	}

	function Menu()
	{
		$_=&$GLOBALS['_STRINGS']['support'];
		return array (
		array
			(
			PutToCategory=>"admin",
			Items=>array
				(
				array(Caption=>$_['MENUCAPTION_BROWSE_TICKETS'],Call=>"support.ITickets.Browse.bm"),
				)
			),
		);
	}

	function Settings()
	{
		$_=&$GLOBALS['_STRINGS']['support'];
		return array
		(
		UseMacAddress=>array(Caption=>'Использовать MAC адрес для идентификации сотрудника',Type=>'boolean',DefaultValue=>'0'),
		AgentsGroupsContext=>array(Caption=>'Раздел сайта групп сотрудников',Type=>'syscontext',DefaultValue=>"agentgroups"),
		);
	}

	function ObjectClasses()
	{
		$_=&$GLOBALS['_STRINGS']['support'];
		return array
		(
		"support.AgentGroup"=>array(Caption=>"Группа сотрудников",UseSettingsContext=>"AgentsGroupsContext"),
		);
	}
}
?>
