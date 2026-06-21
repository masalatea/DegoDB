-(void)addObject:(id)anObject {
    [_backendArray addObject:anObject];
}
-(void)insertObject:(id)anObject atIndex:(NSUInteger)index {
    [_backendArray insertObject:anObject atIndex:index];
}
-(void)replaceObjectAtIndex:(NSUInteger)index withObject:(id)anObject {
    [_backendArray replaceObjectAtIndex:index withObject:anObject];
}
-(id)objectAtIndex:(NSUInteger)index {
    return [_backendArray objectAtIndex:index];
}
-(NSUInteger)count {
    return _backendArray.count;
}
-(void)removeObject:(id)anObject {
    [_backendArray removeObject:anObject];
}
-(void)removeLastObject {
    [_backendArray removeLastObject];
}
-(void)removeAllObjects {
    [_backendArray removeAllObjects];
}
-(void)removeObjectAtIndex:(NSUInteger)index {
    [_backendArray removeObjectAtIndex:index];
}
