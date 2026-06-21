- (id) initWithDic:(NSDictionary*)dic
{
    self = [super init];
    if (self != nil) {
__ARRAY_INITIALIZE_BASE__
        if ([dic isKindOfClass:[NSArray class]]) {
            NSArray* array = (NSArray*)dic;
            for (id object in array) {
                [self addObject:[[__CLASS_NAME__ alloc] initWithDic:object]];
            }
        }
        // == START OF EDITABLE AREA FOR INIT WITH DIC ==
        // == END OF EDITABLE AREA FOR INIT WITH DIC ==
    }
    return self;
}
