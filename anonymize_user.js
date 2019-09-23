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

function anonymize_groups_cleaning() {
  var anonymize_user = cj('#AnonymizeUser .form-select');
  var select_data = cj('#SelectData .form-select');

  if(anonymize_user) {
    anonymize_user.val('no');
  }
  if(select_data) {
    select_data.val('no');
  }

  cj('#block_useraccount').val('yes');
  cj('#remove_drupalroles').val('yes');
  cj('#remove_name').val('no');
  cj('#remove_namefromcasetitles').val('no');
  cj('#remove_personaldata').val('no');
  cj('#remove_additionaldata').val('yes');
  cj('#remove_jobtitle').val('no');
  cj('#remove_passportinformation').val('yes');
  cj('#remove_incaseofemergency').val('yes');
  cj('#remove_bankinformation').val('no');
  cj('#remove_nationality').val('no');
  cj('#remove_medical').val('yes');
  cj('#remove_flight').val('yes');
  cj('#remove_expertdata').val('yes');
  cj('#remove_addresses').val('no');
  cj('#remove_mailaddresses').val('no');
  cj('#remove_phonenumbers').val('no');
  cj('#remove_workhistory').val('yes');
  cj('#remove_education').val('yes');
  cj('#remove_languages').val('yes');
  cj('#remove_groups').val('no');
  cj('#remove_contactsegments').val('no');
  cj('#remove_documents').val('yes');

}