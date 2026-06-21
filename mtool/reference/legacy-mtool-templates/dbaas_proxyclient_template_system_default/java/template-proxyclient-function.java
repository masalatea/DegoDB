    // ==========================================================================
    // __FUNCTION_NAME__ without AsyncTaskLoader pattern of Android (Direct call)
    // ==========================================================================
    public __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult __FUNCTION_NAME__(__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams param) throws java.io.IOException, java.net.URISyntaxException
    {
        try{
            jp.co.matsuesoft.CommonLib.ProxyClientBase.ProxyClientGeneralRequest<__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams, __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult> req = new jp.co.matsuesoft.CommonLib.ProxyClientBase.ProxyClientGeneralRequest<__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams, __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult>();
            java.net.URI baseURL = new java.net.URI(this.BaseURL);
            return req.Execute(baseURL.resolve("__REQUEST_URL__"), param, __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult.class);
        }
        catch (Exception e)
        {
            return new __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult(jp.co.matsuesoft.CommonLib.ProxyClientBase.ProxyResultStatusTypeString.NGinClient, "Error Occured while request: " + e);
        }
    }
