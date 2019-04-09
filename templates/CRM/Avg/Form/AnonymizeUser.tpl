<div class="crm-submit-buttons">
{$back_to_contact}
</div>

<div class="content">
  <h3><div class="label font-red">With the submit of this form, all data in the fieldgroups below that are marked as 'yes', will be permanently deleted for: <b>{$display_name}</b></div></h3>
</div>
<div class="content">
This form will permanently anonymize the contact fields that are marked as "Yes".
<br />
Below you can select which fieldgroups you want to anonymize. After that, press the anonymize button on the bottom to permanently anonymize this user.
<br />
<br />
Please be patient after pressing the Anonymize button. It can take up a couple of seconds before all data is processed.
<br />
<br />
<div class="label font-red"><b>WARNING:</b> This action cannot be undone! Once you have anonymized the user, the data for this user is permanently deleted.</div>
<br />
<div class="crm-submit-buttons">
{$set_all_buttons_to_yes} {$set_all_buttons_to_no}
</div>
{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}</div>
    <div class="clear"></div>
  </div>
{/foreach}

<div class="content">
  <h3><div class="label font-red">With the submit of this form, all data in the fieldgroups below that are marked as 'yes', will be permanently deleted for: <b>{$display_name}</b></div></h3>
</div>

<div class="crm-submit-buttons">
{$back_to_contact}
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
