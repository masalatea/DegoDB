        #region Property:__PROPERTY_NAME__ for Nullable
        public __PROPERTY_DATA_TYPE__ __PROPERTY_NAME__NonNull
        {
            get
            {
                if (this.__PROPERTY_NAME__ == null)
                {
                    return (__PROPERTY_DATA_TYPE__)DegoDBCommonLib.Default.DefaultValueBasedOnDataTypeIfNull.GetDefaultValueBasedOnDataTypeForGet(typeof(__PROPERTY_DATA_TYPE__));
                }
                return (__PROPERTY_DATA_TYPE__)this.__PROPERTY_NAME__;
            }
        }
        #endregion
