    func Serialize() -> Array<Dictionary<String, Any>>
    {
        var result:Array<Dictionary<String, Any>> = Array<Dictionary<String, Any>>();
        for i in 0 ..< _backendArray.count {
            let item:Dictionary<String, Any> = _backendArray[i].Serialize();
            result.append(item);
        }
        // == START OF EDITABLE AREA FOR SERIALIZE ==
        // == END OF EDITABLE AREA FOR SERIALIZE ==
        return result;
    }
