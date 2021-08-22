class phpsbFlash.Button extends phpsbFlash.Control
{
	#include "ComponentVersion.as"
	static var symbolName:String = "Button";
	static var symbolOwner:Object = phpsbFlash.Button;
	var className:String = "Button";
	
	var __width:Number;
	var __height:Number;
	var btnLabel:TextField;

	public var _label:String;
	[Inspectable(defaultValue="Submit", type="String")]
	public function set label(s:String)
	{
		_label=s;
		btnLabel.text=s;
	}
	public function get label():String
	{
		return _label;
	}

	function Button()
	{
		width=__width = _width;
		height=__height = _height;
		_xscale = _yscale=100;
		invalidate();
	}

	function invalidate()
	{
		doLater("draw");
	}
	function doLater(func:String,args:Array,dropIfExist:Boolean,earlier:Boolean)
	{
		_root.dispatcher.doLater(this,func,args,dropIfExist,earlier);
	}
	function setSize(w:Number, h:Number)
	{
		width=w;
		height=h;
		size();
	}

	function draw()
	{
		btnLabel.text=label;
		// if any data changed and its representation should be redrawn
	}
	function size()
	{
		// rearrange content
	}
	
}