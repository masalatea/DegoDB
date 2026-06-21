
import Foundation

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

public class __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult : ProxyClientResultBase
{
__PROPERTIES__

    override init()
    {
__INITIALIZE_PROPERTY__
        // == START OF EDITABLE AREA FOR INIT ==
        // == END OF EDITABLE AREA FOR INIT ==
        super.init();
    }
    override init(status:String, msg:String)
    {
__INITIALIZE_PROPERTY__
        // == START OF EDITABLE AREA FOR INIT WITH STATUS ==
        // == END OF EDITABLE AREA FOR INIT WITH STATUS ==
        super.init(status:status, msg:msg);
    }
    override init(json:Any?)
    {
        var dic:Dictionary<String, Any>? = json as? Dictionary<String, Any>;
        if (dic != nil) {
__DESERIALIZE__
            // == START OF EDITABLE AREA FOR INIT WITH DIC ==
            // == END OF EDITABLE AREA FOR INIT WITH DIC ==
        }
        super.init(status:ProxyClientDataConversionUtil.ConvToString(value:dic!["_status"]), msg:ProxyClientDataConversionUtil.ConvToString(value:dic!["Message"]));
    }

// == START OF EDITABLE AREA FOR ADDITIONAL CLASS IMPLEMENTATION DEFINITION ==
// == END OF EDITABLE AREA FOR ADDITIONAL CLASS IMPLEMENTATION DEFINITION ==

}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==
