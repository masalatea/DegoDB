        #region __FUNCTION_NAME__
        public bool __FUNCTION_NAME__(__PARAMS__)
        {
            SqlCommand command = new SqlCommand("insert into __INSERT_TARGET_TABLE__ (__INSERT_TARGET_COLUMNS__) values(__INSERT_VALUES__)", connection);
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
