        #region __FUNCTION_NAME__
        public bool __FUNCTION_NAME__(__PARAMS__)
        {
            SqlCommand command = new SqlCommand("update __UPDATE_TARGET_TABLE__ SET __SET____WHERE__", connection);
__SET_PARAMETER__
            try
            {
                command.ExecuteNonQuery();

                return true;
            }
            catch (Exception ex)
            {
                System.Diagnostics.Debug.WriteLine(ex.Message);
                return false;
            }
        }
        #endregion
