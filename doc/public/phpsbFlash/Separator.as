class phpsbFlash.Separator extends phpsbFlash.Control
{
	#include "ComponentVersion.as"
	static var symbolName:String = "Separator";
	static var symbolOwner:Object = phpsbFlash.Separator;
	var className:String = "Separator";

	[Inspectable(defaultValue=true, type="Boolean")]
	function set horizontal(v:Boolean) 
		{
		_horizontal=v; 
		redraw();
		}
	function get horizontal():Boolean {return _horizontal;}
	private var _horizontal:Boolean;
	
	var data:Object;
	var mcLeft:MovieClip;
	var mcRight:MovieClip;
	var mcMiddle:MovieClip;
	var dragMode: Boolean;
	var boundingBox:MovieClip;

	function onRollOver()
	{
		if (_horizontal) dispatcher.setCursor("curHorSeparator");
		else dispatcher.setCursor("curVertSeparator");
	}
	function onRollOut()
	{
		dispatcher.setCursor();
	}
	function onPress()
	{
		dragMode=true;
		this.onMouseMove=onSepMove;
		this.onRelease=onSepRelease;
		this.onReleaseOutside=onSepRelease;
	}
	function onSepMove()
	{
		dispatcher.onDockSeparatorMove(this);
	}
	function init()
	{		
		boundingBox._visible=false;
		super.init();
	}
	function onSepRelease()
	{
		this.dragMode=false;
		delete this.onMouseMove;
		dispatcher.setCursor();
	}
	function size()
	{
		super.size();
		if (_horizontal) {
			mcMiddle._rotation=mcRight._rotation=mcLeft._rotation=0;
			mcLeft._y=mcMiddle._y=mcRight._y=0;
			mcLeft._x=0
			mcMiddle._x=1;
			mcMiddle._width=width-2;
			mcMiddle._height=4;
			mcRight._x=width-1;
		} else {
			mcMiddle._rotation=mcRight._rotation=mcLeft._rotation=90;
			mcLeft._x=mcMiddle._x=mcRight._x=4;
			mcLeft._y=0;
			mcMiddle._y=1; 
			mcMiddle._width=4;
			mcMiddle._xscale=100*(height-2)/29;
			mcMiddle._yscale=100;
			mcRight._y=height-1;
		}
	}
	function draw()
	{
		super.draw();
		size();
	}
}