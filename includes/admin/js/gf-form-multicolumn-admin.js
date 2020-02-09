function gfmc_validate_form_columns (error, form, has_product, has_option) {
  let result = check_start_and_end_quantities(form)
  if (result !== 0) {
    if (result > 0) {
      return result + ' too many Column Starts to Column Ends. Please review and remove the excess Column Starts.'
    } else {
      return -result + ' too many Column Ends to Column Starts. Please review and remove the excess Column Ends.'
    }
  }
}

function check_start_and_end_quantities (form) {
  let columnStartCounter = 0
  let columnEndCounter = 0
  jQuery.each(form.fields, (field, value) => {
    if (value.type === 'column_start') {
      columnStartCounter++
    }
    if (value.type === 'column_end') {
      columnEndCounter++
    }
  })
  return columnStartCounter - columnEndCounter
}
