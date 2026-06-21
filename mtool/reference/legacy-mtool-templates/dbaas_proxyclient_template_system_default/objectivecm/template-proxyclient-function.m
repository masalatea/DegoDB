// ==========================================================================
// __FUNCTION_NAME__
// ==========================================================================
- (bool) __FUNCTION_NAME__:(__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams*) param
{
    @try{
        NSString *urlString = [NSString stringWithFormat:@"%@%@", [self GetBaseURL], @"__REQUEST_URL__"];
        NSURL *url = [NSURL URLWithString:urlString];
        NSLog(@"Request URL: %@", urlString);
        
        NSDictionary *jsonDic = [param Serialize];
        if([NSJSONSerialization isValidJSONObject:jsonDic]){
            NSError *error = nil;
            NSData *json = [NSJSONSerialization dataWithJSONObject:jsonDic options:0 error:&error];
            
            NSURLSessionConfiguration *config = [NSURLSessionConfiguration defaultSessionConfiguration];
            NSURLSession *session = [NSURLSession sessionWithConfiguration:config delegate:nil delegateQueue:nil];
            
            NSMutableURLRequest *request = [NSMutableURLRequest requestWithURL:url cachePolicy:NSURLRequestUseProtocolCachePolicy timeoutInterval:60.0];
            [request addValue:@"application/json" forHTTPHeaderField:@"Content-Type"];
            [request addValue:@"application/json" forHTTPHeaderField:@"Accept"];
            [request setHTTPMethod:@"POST"];
            [request setHTTPBody:json];
            
            // == START OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ BEFORE CALL ==
            // == END OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ BEFORE CALL ==
            
            NSURLSessionDataTask *task = [session dataTaskWithRequest:request completionHandler:^(NSData *data, NSURLResponse *response, NSError *error) {
                __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult* proxy_result = nil;
                NSString* error_message = @"";
                @try {
                    if (!error) {
                        NSHTTPURLResponse *httpResp = (NSHTTPURLResponse *) response;
                        if (httpResp.statusCode == 200) {
                            NSDictionary *dic = [NSJSONSerialization JSONObjectWithData:data options:0 error:nil];
                            proxy_result = [[__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult alloc] initWithDic:dic];
                        } else {
                            error_message = [NSString stringWithFormat:@"Error Occured while request. Status Code:%ld", (long)httpResp.statusCode ];
                        }
                    }
                }
                @catch(NSException* e)
                {
                    error_message = [NSString stringWithFormat:@"[Error] %@", e];
                }
                if (proxy_result == nil) {
                    if ([@"" isEqualToString:error_message]) {
                        error_message = [NSString stringWithFormat:@"Error Occured while request."];
                    }
                    proxy_result = [[__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult alloc] initWithStatus:ProxyResultStatusTypeStringNGinClient: error_message];
                }
                // == START OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ BEFORE CALLBACK ==
                // == END OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ BEFORE CALLBACK ==
                if([self.OnFinsh__FUNCTION_NAME__ respondsToSelector:@selector(onFinish__FUNCTION_NAME__:)]){
                    [self.OnFinsh__FUNCTION_NAME__ onFinish__FUNCTION_NAME__:proxy_result];
                }
                // == START OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ AFTER CALLBACK ==
                // == END OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ AFTER CALLBACK ==
            }];
            [task resume];
            
            return true;
        }
    }
    @catch (NSException* e)
    {
        NSLog(@"[Error] %@", e);
        @throw e;
    }
    return false;
}
