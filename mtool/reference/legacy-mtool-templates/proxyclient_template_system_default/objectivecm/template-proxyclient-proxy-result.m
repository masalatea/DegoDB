
__INCLUDE_PROXY_RESULT_HEADER__

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

@implementation __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult

- (id) init
{
    self = [super init];
    if (self != nil) {
__INITIALIZE_PROPERTY__
        // == START OF EDITABLE AREA FOR INIT ==
        // == END OF EDITABLE AREA FOR INIT ==
    }
    return self;
}

- (id) initWithStatus:(NSString*)status :(NSString*)msg;
{
    self = [super initWithStatus: status: msg];
    if (self != nil) {
__INITIALIZE_PROPERTY__
        // == START OF EDITABLE AREA FOR INIT WITH STATUS ==
        // == END OF EDITABLE AREA FOR INIT WITH STATUS ==
    }
    return self;
}

- (id)initWithDic:(NSDictionary*)dic {
    self = [super init];
    if (self != nil) {
        self._status = dic[@"_status"];
        self.Message = dic[@"Message"];
__DESERIALIZE__
        // == START OF EDITABLE AREA FOR INIT WITH DIC ==
        // == END OF EDITABLE AREA FOR INIT WITH DIC ==
    }
    return self;
}

// == START OF EDITABLE AREA FOR ADDITIONAL CLASS IMPLEMENTATION DEFINITION ==
// == END OF EDITABLE AREA FOR ADDITIONAL CLASS IMPLEMENTATION DEFINITION ==

@end

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==
