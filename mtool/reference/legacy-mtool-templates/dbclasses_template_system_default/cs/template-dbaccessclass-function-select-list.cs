        #region __FUNCTION_NAME__
        public __LIST_DATA_CLASS_NAME__ __FUNCTION_NAME__(__PARAMS__)
        {
            __LIST_DATA_CLASS_NAME__ result = new __LIST_DATA_CLASS_NAME__();

            SqlCommand command = new SqlCommand("select __SELECT_BY_DISTINCT____SELECT_COLUMNS__ from __SELECT_FROM____WHERE____GROUP_BY____HAVING____ORDER_BY____SELECT_LIMIT__", connection);
__SET_PARAMETER__
            SqlDataReader reader = null;
            try
            {
                reader = command.ExecuteReader();
                while (reader.Read())
                {
                    __DATA_CLASS_NAME__ thisItem = new __DATA_CLASS_NAME__();
__STORE_DATA_CODE__
                    result.Add(thisItem);
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
            return result;
        }
        #endregion
