
// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

namespace __CS_NAMESPACE__
{
    // THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
    // [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。
    
    public class __CLASS_NAME____INHERIT_CLASS__System.ComponentModel.INotifyPropertyChanged
    {
        public __CLASS_NAME__()
        {
            // == START OF EDITABLE AREA FOR DEFAULT CONSTRUCTOR ==
            // == END OF EDITABLE AREA FOR DEFAULT CONSTRUCTOR ==
        }

__AUTOMATED_CODE_COMES_HERE__

        #region PropertyChanged
        public event System.ComponentModel.PropertyChangedEventHandler PropertyChanged;
        protected virtual void OnPropertyChanged(string name)
        {
            if (PropertyChanged == null) return;
            PropertyChanged(this, new System.ComponentModel.PropertyChangedEventArgs(name));
        }
        #endregion

        #region Shallow Clone
        public __CLASS_NAME__ CloneShallow()
        {
            return (__CLASS_NAME__)this.MemberwiseClone();
        }
        #endregion

        #region Copy Properties From
        internal void CopyPropertiesFrom(__CLASS_NAME__ source_obj)
        {
__COPY_FROM_VALUE__

__COPY_FROM_RAISE_EVENT__
        }
        #endregion

        // == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
        // == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
__GET_CONST_STRING__
    }
__CONST_DEFINITION__

    // == START OF EDITABLE AREA FOR BOTTOM ==
    // == END OF EDITABLE AREA FOR BOTTOM ==
}
