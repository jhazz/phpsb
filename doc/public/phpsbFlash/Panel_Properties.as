class phpsbFlash.Panel_Properties extends phpsbFlash.Panel
{
	#include "ComponentVersion.as"
	static var symbolName:String = "Panel_Properties";
	static var symbolOwner:Object = phpsbFlash.Panel_Properties;
	var className:String = "Panel_Properties";
	var btnCancel:MovieClip;
	var btnOk:MovieClip;
	var bg:MovieClip;
	
	/*
	function onRearrange(event)
	{
		trace ("Property panel rearranging");
	}
*/

	function init()
	{
		super.init();
	}
	function lockMouse() {
	}
	function draw()
	{
		super.draw();
		trace ("Proppanel draw");
		bg._visible=!_collapsed;
	}
	function size()
	{
		super.size();
		btnCancel._x=width-80;
		btnOk._x=width-160;
		btnOk._y=btnCancel._y=height-33;
		bg._width=width;
		bg._height=height;
		bg.onPress=lockMouse;
		bg.useHandCursor=false;
//		trace ("Panel size()");
		// rearrange content
	}
	
}