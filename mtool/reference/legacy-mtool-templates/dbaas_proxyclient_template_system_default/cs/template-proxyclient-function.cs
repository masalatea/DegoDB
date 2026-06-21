        #region __FUNCTION_NAME__
        public async System.Threading.Tasks.Task<__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult> __FUNCTION_NAME__(__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams param)
        {
            try
            {
                DegoDBCommonLib.ProxyClientGeneralRequest<__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams, __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult> request = new DegoDBCommonLib.ProxyClientGeneralRequest<__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams, __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult>();
                __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult proxyResultObj = await request.Execute(
                    new System.Uri(new System.Uri(this.BaseURL), "__REQUEST_URL__"), param);
                return proxyResultObj;
            }
            catch (System.Exception e)
            {
                return new __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult(DegoDBCommonLib.ProxyResultStatusTypeString.NGinClient, "Error Occured while request: " + e.Message);
            }
        }
        #endregion
