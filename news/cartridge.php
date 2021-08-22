<?
class news
{
	function news()
	{
		$_=&$GLOBALS['_STRINGS']['news'];
		$this->Title=$_['NEWS_CARTRIDGETITLE'];
		$this->Roles=array
		(
		PostNews=>$_['ROLE_POSTNEWS'],
		RemoveNews=>$_['ROLE_REMOVENEWS'],
		ChangeSettings=>$_['ROLE_CHANGESETTINGS'],
		NewsBackend=>$_['ROLE_NEWSBACKEND']
		);
	}
	/*
	function ObjectClasses()
	{
	$_=&$GLOBALS[_STRINGS][news];
	return array(
	"Event"=>array(
	ClassCaption=>$_[TNEWSEVENT_D_THENEWSEVENT],
	Table=>"news_Events",
	KeyField=>"NewsID",
	CaptionField=>"Title",
	OwnerBindField=>false,
	OwnerUserField=>false,
	Browse=>"news.INewsEvent.Browse",
	OnBeforeDelete=>false,
	Select=>false,
	Sockets=>array(
	'imghead'=>array(Caption=>$_[HEADIMAGE],BindableClass=>"img.Document",SingleObject=>true),
	'imgindex'=>array(Caption=>$_[TITLE_IMAGEINDEX],BindableClass=>"img.Document"),
	'text'=>array(Caption=>$_[TNEWSEVENT_D_BODY],BindableClass=>"stdctrls.Richtext"),
	'qna'=>array(Caption=>"Q&A",BindableClass=>"msg.Qna"),
	),
	),
	);

	}
	*/

	function Controls()
	{
		$_=&$GLOBALS['_STRINGS']['news'];
		return array(
			TNewsBrowser=>array(
			Caption=>$_['TNEWSBROWSER_CAPTION'],Description=>$_['TNEWSBROWSER_DESCRIPTION'],Icon=>'ico_TNewsBrowser.gif'),
			TNewsPath=>array(Caption=>$_['TNEWSEVENT_CAPTION'],Description=>$_['TNEWSEVENT_DESCRIPTION'],Icon=>'ico_TNewsEvent.gif'),
		);
	}

	function Menu()
	{
		$_=&$GLOBALS['_STRINGS']['news'];
		return array (
		array
			(
			PutToCategory=>"content",
			Items=>array
				(
				array(Caption=>$_['CAPTION_BROWSE_GROUPS'],Call=>"news.IReview.Browse.bm"),
				)
			),
		array
			(
			PutToCategory=>"admin",
			Items=>array(
				array(Caption=>$_['CAPTION_EDIT_GROUPS'],Call=>"news.IGroup.Browse.n"))
			)
		);
	}

	function Settings()
	{
		$_=&$GLOBALS['_STRINGS']['news'];
		return array
		(
		MonthsToShow=>array(Caption=>$_['SETTING_MONTHSTOSHOW'],Type=>'int',DefaultValue=>'36'),
		NewsGroupsContext=>array(Caption=>$_['SETTINGS_GROUPCONTEXT'],Type=>'syscontext',DefaultValue=>"newsgroups"),
		PagesDefaultContext=>array(Caption=>$_['SETTINGS_EVENTCONTEXT'],Type=>'syscontext',DefaultValue=>"news"),
		SubjectsContext=>array(Caption=>'Subjects context',Type=>'syscontext',DefaultValue=>"subjects"),
		);
	}

	function ObjectClasses()
	{
		$_=&$GLOBALS['_STRINGS']['news'];
		return array
		(
		"news.Group"=>array(Caption=>"News events group",UseSettingsContext=>"NewsGroupsContext"),

		"news.Event"=>array(Caption=>"News event",UseSettingsContext=>"PagesDefaultContext",
			'Interface'=>"news.INewsEvent",
			Table=>"news_Events",
			Fields=>array(
				'NewsID'=>array(FieldType=>'int'),
				'Title'=>array(FieldType=>'string',Caption=>$_['TNEWSEVENT_D_TITLE']),
				'DateOfNews'=>array(FieldType=>'date',Caption=>$_['TNEWSEVENT_D_DATE'],TargetClass=>false),
				'Intro'=>array(FieldType=>'text',Caption=>$_['TNEWSEVENT_D_INTRO']),
				'Author'=>array(FieldType=>'string',Caption=>$_['TNEWSEVENT_D_AUTHOR']),
				'DateToShow'=>array(FieldType=>'date',Caption=>$_['TNEWSEVENT_D_DATETOSHOW']),
				'DateToHide'=>array(FieldType=>'date',Caption=>$_['TNEWSEVENT_D_DATETOHIDE']),
				'PubStatus'=>array(FieldType=>'droplist',Caption=>$_['PUBSTATUS'],Values=>array('0'=>$_['PUBSTATUS_0'],'5'=>$_['PUBSTATUS_5'],'10'=>$_['PUBSTATUS_10'])),
				),
			IDField=>"NewsID",
			CaptionField=>"Title",
			IdentityMethod=>0,
			ModifyTimeField=>'DateModified',
			ReplicaTimeField=>false,
			CreateUserIDField=>'CreateByUserID',
			ModifyUserIDField=>'ModifyByUserID',
			BindToField=>false,
			StatusField=>false,
			HistoryField=>false,
			LangField=>false,
			Folders=>array(
				'imghead'=>array(Caption=>$_['HEADIMAGE'],BindableClass=>"img.Document",SingleObject=>true),
				'imgindex'=>array(Caption=>$_['TITLE_IMAGEINDEX'],BindableClass=>"img.Document"),
				'text'=>array(Caption=>$_['TNEWSEVENT_D_BODY'],BindableClass=>"stdctrls.Richtext"),
				'media'=>array(Caption=>"Media file",BindableClass=>"media.File"),
				'qna'=>array(Caption=>"Q&A",BindableClass=>"msg.Qna"),)
		  ),
		);
	}
}
?>
