        public __DATA_TYPE__ __PARAM_NAME__NonNull
        {
            get
            {
                if (this.__PARAM_NAME__ == null)
                {
                    return (__DATA_TYPE__)DegoDBCommonLib.Default.DefaultValueBasedOnDataTypeIfNull.GetDefaultValueBasedOnDataTypeForGet(typeof(__DATA_TYPE__));
                }
                return (__DATA_TYPE__)this.__PARAM_NAME__;
            }
        }
