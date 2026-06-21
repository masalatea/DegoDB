    // ==========================================================================
    // __FUNCTION_NAME__ with AsyncTaskLoader pattern for Android
    // ==========================================================================
    // Steps to implement Activity or Fragment Class
    // 1. Please implements LoaderManager.LoaderCallbacks<jp.co.matsuesoft.CommonLib.ProxyClientBase.ProxyClientResultBase> in Activity or Fragment Class to receive result
    //    (If only one request, inherited result class may be able to use but it is better to use base class)
    // 
    // 2. Implements Loader<jp.co.matsuesoft.CommonLib.ProxyClientBase.ProxyClientResultBase> onCreateLoader(int id, Bundle args) function
    //    by calling
    //    __CLASS_BASE_NAME__ProxyClient proxy_client = new __CLASS_BASE_NAME__ProxyClient();
    //    return proxy_client.CreateAsyncTaskLoaderFor__FUNCTION_NAME__(this, args);
    //      or
    //    return proxy_client.CreateAsyncTaskLoaderFor__FUNCTION_NAME__(getActivity().getApplicationContext(), args);
    //    Note: Call based on id parameter if necessary. id is specified in step 4. If only one request, id is not a concern
    // 
    // 3. Impelemtnts public void onLoadFinished(Loader<jp.co.matsuesoft.CommonLib.ProxyClientBase.ProxyClientResultBase> loader, jp.co.matsuesoft.CommonLib.ProxyClientBase.ProxyClientResultBase data) function
    //    By casting parameter: data
    //    if (data instanceof __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult) {
    //        __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult proxy_result = (__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult)data;
    //        if (data.Status() == jp.co.matsuesoft.CommonLib.ProxyClientBase.ProxyResultStatusType.OK) {
    //            ....
    //        }
    //    }
    // 
    // How to call function from Activity or Fragment Class
    //    __CLASS_BASE_NAME__ProxyClient proxy_client = new __CLASS_BASE_NAME__ProxyClient();
    //    __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams param = new __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams();
    //    param.abc = ...   Set necessary parameter
    //    android.os.Bundle args = proxy_client.CreateBundleOfAsyncTaskLoaderFor__FUNCTION_NAME__(param);
    //    this.getLoaderManager().restartLoader(id, args, this);   or initLoader. id is used to distinguish query if there are multiple
    // 
    public android.os.Bundle CreateBundleOfAsyncTaskLoaderFor__FUNCTION_NAME__(__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams param) {
        android.os.Bundle args = new android.os.Bundle();
        args.putSerializable("Param", param);
        return args;
    }
    public android.content.Loader<jp.co.matsuesoft.CommonLib.ProxyClientBase.ProxyClientResultBase> CreateAsyncTaskLoaderFor__FUNCTION_NAME__(android.content.Context context, android.os.Bundle args) throws java.io.IOException, java.net.URISyntaxException
    {
        if(args != null) {
            __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams param = (__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams) args.getSerializable("Param");
            if (param != null) {
                java.net.URI baseURL = new java.net.URI(this.BaseURL);
                jp.co.matsuesoft.CommonLib.ProxyClientBase.ProxyClientGeneralRequest<__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams, __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult> req = new jp.co.matsuesoft.CommonLib.ProxyClientBase.ProxyClientGeneralRequest<__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams, __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult>();
                __CLASS_BASE_NAME__ProxyClient__FUNCTION_NAME__AsyncTaskLoader new_loader = new __CLASS_BASE_NAME__ProxyClient__FUNCTION_NAME__AsyncTaskLoader(context, baseURL.resolve("__REQUEST_URL__"), req, param);
                new_loader.forceLoad();
                return new_loader;
            }
        }
        return null;
    }
