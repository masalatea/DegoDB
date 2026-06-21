- (NSArray<NSDictionary*>*)Serialize
{
    NSArray* result = [[NSArray<NSDictionary*> alloc] init];
    for (int i = 0; i < self.count; i++) {
        NSDictionary* item = [self[i] Serialize];
        [result arrayByAddingObject:item];
    }
    return result;
}
