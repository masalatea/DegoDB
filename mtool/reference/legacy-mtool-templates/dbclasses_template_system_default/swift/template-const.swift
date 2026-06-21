
// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

public enum __CLASS_NAME__:Int {
    case
__CONST_DEFINITIONS__
}

public let __CLASS_NAME__TextArray = [
__CONST_STRING_DEFINITIONS__
];

public class __CLASS_NAME__Conversion
{
    public static func Get__CLASS_NAME__Text(val:__CLASS_NAME__) -> String
    {
        if (val.rawValue >= 0 && val.rawValue < __CLASS_NAME__TextArray.count) {
            return __CLASS_NAME__TextArray[val.rawValue];
        }
        return "";
    }
    public static func Get__CLASS_NAME__(enum_value:Any?) -> __CLASS_NAME__
    {
        let enum_string:String = ProxyClientDataConversionUtil.ConvToString(value:enum_value);
        for index in 0 ..< __CLASS_NAME__TextArray.count {
            if (enum_string == __CLASS_NAME__TextArray[index]) {
                return __CLASS_NAME__(rawValue:index)!;
            }
        }
        return __CLASS_NAME__.UNKNOWN;
    }

// == START OF EDITABLE AREA FOR ADDITIONAL CLASS IMPLEMENTATION DEFINITION ==
// == END OF EDITABLE AREA FOR ADDITIONAL CLASS IMPLEMENTATION DEFINITION ==

}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==
