// ==========================================================================
// __FUNCTION_NAME__
// 
// [JP] 結果はDelegateで返されます。呼び出し元に OnFinsh__CLASS_BASE_NAME__ProxyClient__FUNCTION_NAME__Delegate を実装して下さい。
// [EN] Result will be returned by Delegate. Please implement OnFinsh__CLASS_BASE_NAME__ProxyClient__FUNCTION_NAME__Delegate to get result.
// 
// [例/Example]
// [JP] Delegateをinterfaceに追加して下さい
// [EN] Add delegate into interface such as:
// @interface ViewController : UIViewController <OnFinsh__CLASS_BASE_NAME__ProxyClient__FUNCTION_NAME__Delegate>
// 
// [JP] OnFinsh__CLASS_BASE_NAME__ProxyClient__FUNCTION_NAME__Delegateに従ってクラスに関数を追加して下さい。
// [EN] Add function into the class based on OnFinsh__CLASS_BASE_NAME__ProxyClient__FUNCTION_NAME__Delegate definition:
// - (void)onFinish__FUNCTION_NAME__:(__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult*)proxy_result;
// [JP] そして、実装時にはStatusを確認します。
// [EN] Then, implement this class and check Status such as:
// if (proxy_result.Status == ProxyResultStatusTypeOK) {
//     if (proxy_result.Result != nil) { // Result exists only for Get Function
//         NSLog(@"Result exists");
//     } else {
//         NSLog(@"No Result");
//     }
// }
// 
// [JP]呼び出し例
// [EN]Call Example
// __CLASS_BASE_NAME__ProxyClient *proxy_client = [[__CLASS_BASE_NAME__ProxyClient alloc] init];
// [proxy_client setOnFinsh__FUNCTION_NAME__: self];
// __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams *param = [[__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams alloc] init];
// param.xxx = @"xxx" ... set necessary parameter
// bool result = [proxy_client __FUNCTION_NAME__: param];
// if (result) {
//     NSLog(@"Success");
// }
// [JP] 結果はDelegate経由で返されます。
// [EN] Then, result will be returned by delegate
// ==========================================================================
- (bool) __FUNCTION_NAME__:(__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams*) param;

// ==========================================================================
// Callback for __FUNCTION_NAME__ to return result
// ==========================================================================
@property id<OnFinsh__CLASS_BASE_NAME__ProxyClient__FUNCTION_NAME__Delegate> OnFinsh__FUNCTION_NAME__;

