        #region Enum Translation for __CLASS_NAME__
        public static string __FUNC_TO_GET_STRING_FROM_ENUM__(__CLASS_NAME__ enumValue)
        {
            return System.Enum.GetName(typeof(__CLASS_NAME__), enumValue);
        }
        public static __CLASS_NAME__ __FUNC_TO_GET_ENUM_FROM_STRING__(string enumValueString)
        {
            return (__CLASS_NAME__)System.Enum.Parse(typeof(__CLASS_NAME__), enumValueString, true);
        }
        public static string[] __CLASS_NAME__Strings = { __CLASS_STRINGS__ };
        #endregion

