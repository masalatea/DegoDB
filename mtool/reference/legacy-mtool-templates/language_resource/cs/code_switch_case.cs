        public static string __KEY__		// __GROUP__
        {
            get
            {
                if (IsJapanese())
                {
                    return @"__JA_TEXT__";
                }
                else if (IsTraditionalChinese())
                {
                    return @"__TZH_TEXT__";
                }
                else if (IsChinese())
                {
                    return @"__ZH_TEXT__";
                }
                else if (IsKorean())
                {
                    return @"__KO_TEXT__";
                }
                else
                {
                    return @"__EN_TEXT__";
                }
            }
        }
