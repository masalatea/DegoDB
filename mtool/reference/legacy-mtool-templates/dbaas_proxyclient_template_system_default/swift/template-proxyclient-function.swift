    // ==========================================================================
    // __FUNCTION_NAME__
    // 
    // [JP] 結果はDelegateで返されます。呼び出し元に OnFinsh__CLASS_BASE_NAME__ProxyClient__FUNCTION_NAME__Delegate を実装して下さい。
    // [EN] Result will be returned by Delegate. Please implement OnFinsh__CLASS_BASE_NAME__ProxyClient__FUNCTION_NAME__Delegate to get result.
    // 
    // [例/Example]
    // [JP] Delegateをclassに追加して下さい
    // [EN] Add delegate into class such as:
    // class ViewController: UIViewController, OnFinsh__CLASS_BASE_NAME__ProxyClient__FUNCTION_NAME__Delegate
    // 
    // [JP] OnFinsh__CLASS_BASE_NAME__ProxyClient__FUNCTION_NAME__Delegateに従ってクラスに関数を追加して下さい。
    // [EN] Add function into the class based on OnFinsh__CLASS_BASE_NAME__ProxyClient__FUNCTION_NAME__Delegate definition:
    // func onFinish__FUNCTION_NAME__(proxy_result:__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult)
    // [JP] そして、実装時にはStatusを確認します。
    // [EN] Then, implement this class and check Status such as:
    // if (proxy_result.Status == ProxyResultStatusType.OK) {
    //     if (proxy_result.Result != nil) { // Result exists only for Get Function
    //         // ... do something
    //     }
    // }
    // 
    // [JP]呼び出し例
    // [EN]Call Example
    // let proxy_client:__CLASS_BASE_NAME__ProxyClient = __CLASS_BASE_NAME__ProxyClient();
    // proxy_client.OnFinsh__FUNCTION_NAME__ = self;
    // let param:__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams = __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams();
    // param.xxx = @"xxx" ... set necessary parameter
    // let result:Bool = proxy_client.__FUNCTION_NAME__(param: param);
    // if (result) {
    //     print("Success");
    // }
    // [JP] 結果はDelegate経由で返されます。
    // [EN] Then, result will be returned by delegate
    // ==========================================================================
    public func __FUNCTION_NAME__(param:__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams) -> Bool
    {
        do{
            let urlString:String = String.init(format:"%@%@", BaseURL, "__REQUEST_URL__");
            let url = URL(string: urlString);
            print("Request URL: %@", urlString);
            
            let jsonDic:Dictionary<String, Any> = param.Serialize();
            if(JSONSerialization.isValidJSONObject(jsonDic)) {
                let json:Data = try JSONSerialization.data(withJSONObject:jsonDic);
                
                let config:URLSessionConfiguration = URLSessionConfiguration.default;
                let session:URLSession = URLSession(configuration:config);
                var request:URLRequest = URLRequest(url:url!);
                request.cachePolicy = .useProtocolCachePolicy;
                request.timeoutInterval = 60.0;
                request.addValue("application/json", forHTTPHeaderField:"Content-Type");
                request.addValue("application/json", forHTTPHeaderField:"Accept");
                request.httpMethod = "POST";
                request.httpBody = json;
                
                // == START OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ BEFORE CALL ==
                // == END OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ BEFORE CALL ==
                
                let task:URLSessionDataTask = session.dataTask(with:request as URLRequest, completionHandler: {(data, response, error) in
                    var proxy_result:__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult? = nil;
                    var error_message:String = "";
                    do {
                        if (data != nil && response != nil && error == nil) {
                            let httpResp:HTTPURLResponse = response as! HTTPURLResponse;
                            if (httpResp.statusCode == 200) {
                                let json = try JSONSerialization.jsonObject(with:data!);
                                proxy_result = __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult(json:json);
                            } else {
                                error_message = String.init(format:"Error Occured while request. Status Code:%d", httpResp.statusCode );
                            }
                        }
                    }
                    catch
                    {
                        error_message = String.init(format:"[Error] \(error)");
                    }
                    
                    if (proxy_result == nil) {
                        if (error_message == "") {
                            error_message = String.init(format:"Error Occured while request.");
                        }
                        proxy_result = __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult(status:ProxyResultStatusTypeTextArray[ProxyResultStatusType.NGinClient.rawValue], msg:error_message);
                    }
                    // == START OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ BEFORE CALLBACK ==
                    // == END OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ BEFORE CALLBACK ==
                    self.OnFinsh__FUNCTION_NAME__?.onFinish__FUNCTION_NAME__(proxy_result:proxy_result!);
                    // == START OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ AFTER CALLBACK ==
                    // == END OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ AFTER CALLBACK ==
                });
                task.resume();
                
                return true;
            }
        }
        catch
        {
            print("[Error] \(error)");
        }
        return false;
    }
    // ==========================================================================
    // Callback for __FUNCTION_NAME__ to return result
    // ==========================================================================
    var OnFinsh__FUNCTION_NAME__:OnFinsh__CLASS_BASE_NAME__ProxyClient__FUNCTION_NAME__Delegate?;

