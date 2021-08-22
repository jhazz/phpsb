class phpsbFlash.SideSizer extends phpsbFlash.Control
{
	#include "ComponentVersion.as"
	static var symbolName:String = "SideSizer";
	static var symbolOwner:Object = phpsbFlash.SideSizer;
	var className:String = "SideSizer";

	private var mcMiddle:MovieClip;
	private var dragMode: Boolean;
	var length:Number;
	var side:Object;

	function init()
	{
		super.init();
		redraw();
	}
	function onRollOver()
	{
		if ((side.sideNo==1)||(side.sideNo==3)) dispatcher.setCursor("curHorSeparator");
		else dispatcher.setCursor("curVertSeparator");
	}
	function onRollOut()
	{
		dispatcher.setCursor();
	}
	function onPress()
	{
		dispatcher.onDockSideSizerStartDrag(side);
	}

	function onSepRelease()
	{
		trace ('release');
		this.dragMode=false;
		delete this.onMouseMove;
		delete this.onRelease;
		delete this.onReleaseOutside;
		dispatcher.setCursor();
	}

	function size()
	{
		switch(side.sideNo)
		{
		case 1: case 3: //top, bottom:
			mcMiddle._y=mcMiddle._x=0;
			mcMiddle._width=length;
			mcMiddle._height=phpsbFlash.Dispatcher.SIDESIZER_WIDTH;
			break;
		case 2: case 4: //'right':
			mcMiddle._x=0;
			mcMiddle._y=0 
			mcMiddle._width=phpsbFlash.Dispatcher.SIDESIZER_WIDTH;
			mcMiddle._height=length;
			break;
		}
	}
	function draw()
	{
		_visible=(length>0) ;
		super.draw();
		size();
	}
}