package __JAVA_PACKAGE_NAME__;

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

public class __CLASS_BASE_NAME__ProxyClient__FUNCTION_NAME__AsyncTaskLoader  extends android.content.AsyncTaskLoader<jp.co.matsuesoft.CommonLib.ProxyClientBase.ProxyClientResultBase> {
    private java.net.URI RequestURI;
    private jp.co.matsuesoft.CommonLib.ProxyClientBase.ProxyClientGeneralRequest<__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams, __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult> Req;
    private __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams Param;

    public __CLASS_BASE_NAME__ProxyClient__FUNCTION_NAME__AsyncTaskLoader(android.content.Context context, java.net.URI requestURI, jp.co.matsuesoft.CommonLib.ProxyClientBase.ProxyClientGeneralRequest<__CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams, __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult> req, __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams param) {
        super(context);
        this.RequestURI = requestURI;
        this.Req = req;
        this.Param = param;
        // == START OF EDITABLE AREA FOR DEFAULT CONSTRUCTOR ==
        // == END OF EDITABLE AREA FOR DEFAULT CONSTRUCTOR ==
    }

    @Override
    public jp.co.matsuesoft.CommonLib.ProxyClientBase.ProxyClientResultBase loadInBackground() {
        // == START OF EDITABLE AREA FOR LOAD IN BACKGROUND ==
        // == END OF EDITABLE AREA FOR LOAD IN BACKGROUND ==
        try {
            return Req.Execute(this.RequestURI, this.Param, __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__ProxyResult.class);
        } catch (java.io.IOException e) {
            return new jp.co.matsuesoft.CommonLib.ProxyClientBase.ProxyClientResultBase(jp.co.matsuesoft.CommonLib.ProxyClientBase.ProxyResultStatusTypeString.NGinClient, "Error Occured while request: " + e);
        }
    }
    
    // == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
    // == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==
