/* Function to set all dropdown groups to yes */
function anonymize_groups_yes() {
  var anonymize_user = cj('#AnonymizeUser .form-select');
  var select_data = cj('#SelectData .form-select');

  if(anonymize_user) {
    anonymize_user.val('yes');
  }
  if(select_data) {
    select_data.val('yes');
  }
}

function anonymize_groups_no() {
  var anonymize_user = cj('#AnonymizeUser .form-select');
  var select_data = cj('#SelectData .form-select');

  if(anonymize_user) {
    anonymize_user.val('no');
  }
  if(select_data) {
    select_data.val('no');
  }
}