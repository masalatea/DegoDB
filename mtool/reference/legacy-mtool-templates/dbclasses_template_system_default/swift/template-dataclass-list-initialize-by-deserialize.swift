    init(json:Any?)
    {
__ARRAY_INITIALIZE_BASE__
        var array = json as? Array<Any>;
        if (array != nil) {
            for i in 0 ..< array!.count {
                let object = array![i];
                _backendArray.append(__ARRAY_ITEM_CLASS_(json:object));
            }
        }
        // == START OF EDITABLE AREA FOR INIT WITH DIC ==
        // == END OF EDITABLE AREA FOR INIT WITH DIC ==
    }
