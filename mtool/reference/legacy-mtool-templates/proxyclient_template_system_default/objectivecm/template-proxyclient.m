
#import "__CLASS_BASE_NAME__ProxyClient.h"
__INCLUDE_PROXY_RESULT_HEADER__
__INCLUDE_PROXY_REQUEST_PARAMS_HEADER__
__INCLUDE_HEADER_FOR_FUNCTION__

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

@implementation __CLASS_BASE_NAME__ProxyClient

- (id) init
{
    self = [super init];
    if (self != nil) {
        NSString *baseURL = @"__BASE_URL__";
        [self setBaseURL:baseURL];
        // == START OF EDITABLE AREA FOR INITIALIZER ==
        // == END OF EDITABLE AREA FOR INITIALIZER ==
    }
    return self;
}

- (id) initWithBaseURL:(NSString*)baseURL
{
    self = [super init];
    if (self != nil) {
        [self setBaseURL:baseURL];
        // == START OF EDITABLE AREA FOR INITIALIZER WITH BASE URL ==
        // == END OF EDITABLE AREA FOR INITIALIZER WITH BASE URL ==
    }
    return self;
}

__FUNCTIONS__

// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==

@end

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==
