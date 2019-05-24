# nl.pum.avg

This CiviCRM-extension can anonymize a contact or a list of contacts from the contact lists at once.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

![Screenshot](https://raw.github.com/PUMNL/nl.pum.avg/master/images/screenshot.png)

## Requirements

* PHP 5.6 (Tested, might work with other version, but not tested)
* CiviCRM 4.4.8 (Tested, might work with other version, but not tested)

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl nl.pum.avg@https://github.com/PUMNL/nl.pum.avg/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/PUMNL/nl.pum.avg.git
cv en avg
```

## Configuration after installation

There are 3 new permissions for this extension
* anonymize a single user
* anonymize users using batch
* clean inactive users using batch

Please make sure that you configure these permissions for the right roles after installation,
otherwise you might not see all available options for your role.

This extension can run the cleanup of users using a scheduled job.
To configure the scheduled job, goto Administer --> System Settings --> Scheduled Jobs --> Add New Scheduled Job -->
Enter a name and description, select the run frequency of your choice, and fill API call with entity: Avg, action: Clean
Then select whether the scheduled job should be active (so scheduled at selected frequency) or not.
![Screenshot](https://raw.github.com/PUMNL/nl.pum.avg/master/images/scheduled_job.png)

## Usage

To anonymize a single user: Open a contact card and select 'Actions'. There you can find an option 'Anonymize User'.
After that a screen will appear with a list of personal information groups and custom other custom groups.
For each group you can select whether the group should be anonymized or not (or use the 'Set all groups to 'Yes', to do that at once).
After that press the anonymize button on the bottom of the page.
Then all contact information of the specified user will be removed.

To anonymize multiple users at once: Open a contact list. Then select all users you want to anonymize, then under 'Actions', you can find the option 'Anonymize User', select it and press 'Go'.
For each group you can select whether the group should be anonymized or not (or use the 'Set all groups to 'Yes', to do that at once).
After that press the 'Anonymize users now' button on the bottom of the page.
Then all contact information for all selected users will be removed.

You can also create a report 'AnonymizeExitUsers' or 'CleanInactiveUsers', which will generate a report of exit or inactive users.
With these reports you can also anonymize users in a batch.

## To do

* Currently it's not possible yet to select which fields to anonymize using the reports, only from contact selection screens

## Documentation

