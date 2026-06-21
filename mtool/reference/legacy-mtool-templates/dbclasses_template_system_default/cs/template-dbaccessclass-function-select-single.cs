        #region __FUNCTION_NAME__
        public __DATA_CLASS_NAME__ __FUNCTION_NAME__(__PARAMS__)
        {
            __DATA_CLASS_NAME__ thisItem = null;

            SqlCommand command = new SqlCommand("select __SELECT_COLUMNS__ from __SELECT_FROM____WHERE____GROUP_BY____HAVING__", connection);
__SET_PARAMETER__
            SqlDataReader reader = null;
            try
            {
                reader = command.ExecuteReader();
                while (reader.Read())
                {
                    thisItem = new __DATA_CLASS_NAME__();
__STORE_DATA_CODE__
                    break;
                }
            }
            catch (Exception ex)
            {
                System.Diagnostics.Debug.WriteLine(ex.Message);
            }
            finally
            {
                if (reader != null)
                {
                    reader.Close();
                }
            }
            return thisItem;
        }
        #endregion
