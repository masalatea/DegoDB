
import Foundation

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

public class __CLASS_NAME____INHERIT_CLASS__
{
__AUTOMATED_CODE_COMES_HERE__

    override init()
    {
        // == START OF EDITABLE AREA FOR INIT ==
        // == END OF EDITABLE AREA FOR INIT ==
        super.init();
    }
    override init(json:Any?)
    {
        var dic:Dictionary<String, Any>? = json as? Dictionary<String, Any>;
        if (dic != nil) {
__DESERIALIZE__
            // == START OF EDITABLE AREA FOR INIT WITH DIC ==
            // == END OF EDITABLE AREA FOR INIT WITH DIC ==
        }
        super.init(json:json);
    }
    override func Serialize() -> Dictionary<String, Any>
    {
__SERIALIZE_DEFINE__
__SERIALIZE__
        // == START OF EDITABLE AREA FOR SERIALIZE ==
        // == END OF EDITABLE AREA FOR SERIALIZE ==
        return dic;
    }

// == START OF EDITABLE AREA FOR ADDITIONAL CLASS IMPLEMENTATION DEFINITION ==
// == END OF EDITABLE AREA FOR ADDITIONAL CLASS IMPLEMENTATION DEFINITION ==

}

__CONST_DEFINITION__

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==
