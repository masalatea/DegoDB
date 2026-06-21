    public var BackendArray:Array<__ARRAY_ITEM_CLASS_> {
        get {
            return _backendArray;
        }
    }
    private var _backendArray:Array<__ARRAY_ITEM_CLASS_>;
    func append(newElement:__ARRAY_ITEM_CLASS_) {
        _backendArray.append(newElement);
    }
    func insert(newElement:__ARRAY_ITEM_CLASS_, i:Int) {
        _backendArray.insert(newElement, at: i);
    }
    func replaceObjectAtIndex(index:Int, newElement:__ARRAY_ITEM_CLASS_) {
        _backendArray[index] = newElement;
    }
    subscript(index: Int) -> __ARRAY_ITEM_CLASS_ {
        get {
            return _backendArray[index];
        }
        set(newValue) {
            _backendArray[index] = newValue;
        }
    }
    func count() -> Int {
        return _backendArray.count;
    }
    func reverse() {
        _backendArray.reverse();
    }
    func removeFirst() {
        _backendArray.removeFirst();
    }
    func removeLast() {
        _backendArray.removeLast();
    }
    func removeAll() {
        _backendArray.removeAll();
    }
    func remove(at:Int) {
        _backendArray.remove(at: at);
    }
