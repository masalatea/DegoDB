
#import <Foundation/Foundation.h>
#import "__CLASS_NAME__.h"

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

NSString *const __CLASS_NAME__TextArray[] = {
__CONST_STRING_DEFINITIONS__
};
int const __CLASS_NAME__TextArraySize = __DEFINE_COUNT__;

@implementation __CLASS_NAME__Conversion
{
}

+ (NSString*) Get__CLASS_NAME__Text:(NSInteger) val
{
    if (val >= 0 && val < __CLASS_NAME__TextArraySize) {
        return __CLASS_NAME__TextArray[val];
    }
    return nil;
}
+ (__CLASS_NAME__) Get__CLASS_NAME__:(NSString*) enum_value
{
    for(int index = 0 ; index < __CLASS_NAME__TextArraySize ;index++) {
        if ([__CLASS_NAME__TextArray[index] compare:enum_value]) {
            return index;
        }
    }
    return -1;
}

// == START OF EDITABLE AREA FOR ADDITIONAL CLASS IMPLEMENTATION DEFINITION ==
// == END OF EDITABLE AREA FOR ADDITIONAL CLASS IMPLEMENTATION DEFINITION ==

@end

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==
