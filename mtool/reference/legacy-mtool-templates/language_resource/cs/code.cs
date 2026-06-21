
// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

namespace __CS_NAMESPACE__
{
    // THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
    // [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。
    
    public static class __CLASS_NAME__
    {
        private static bool IsJapanese()
        {
            string current_culture = System.Globalization.CultureInfo.CurrentUICulture.Name;
            return string.Equals(current_culture, "ja", System.StringComparison.CurrentCultureIgnoreCase) ||
                string.Equals(current_culture, "ja-JP", System.StringComparison.CurrentCultureIgnoreCase) ||
                string.Equals(current_culture, "JP", System.StringComparison.CurrentCultureIgnoreCase);
        }
        private static bool IsTraditionalChinese()
        {
            string current_culture = System.Globalization.CultureInfo.CurrentUICulture.Name;
            return string.Equals(current_culture, "zh-Tw", System.StringComparison.CurrentCultureIgnoreCase);
        }
        private static bool IsChinese()
        {
            string current_culture = System.Globalization.CultureInfo.CurrentUICulture.Name;
            return string.Equals(current_culture, "zh", System.StringComparison.CurrentCultureIgnoreCase);
        }
        private static bool IsKorean()
        {
            string current_culture = System.Globalization.CultureInfo.CurrentUICulture.Name;
            return string.Equals(current_culture, "ko", System.StringComparison.CurrentCultureIgnoreCase);
        }

__LANGUAGE_RESOURCE_LIST__

        // == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
        // == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
    }
    // == START OF EDITABLE AREA FOR BOTTOM ==
    // == END OF EDITABLE AREA FOR BOTTOM ==
}
