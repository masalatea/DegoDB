        #region __FUNCTION_NAME__
        public bool __FUNCTION_NAME__(__PARAMS__)
        {
            SqlCommand command = new SqlCommand("delete from __DELETE_TARGET_TABLE____WHERE__", connection);
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
