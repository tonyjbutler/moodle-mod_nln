
Readme file for the NLN Materials Moodle Browser (codename: Noodle)
====== ==== === === === ========= ====== ======= ========== =======

Introduction
------------
Noodle is a plug-in module for Moodle systems that allows Moodle users to find and use NLN Materials with a minimum of effort. Noodle was developed by Xtensis.co.uk - the developers of the NLN materials delivery site, as an additional service to our users.
The benefits include:
For administrators:
- no need to create and maintain a repository.
- works alongside but completely independently of local repositories.
- simple installation. Does not require "IMS Repository" module or its variants.
- materials are still hosted on the nln.ac.uk site, so users will see all updates and fixes, and you may save bandwidth

For practitioners:
- no need to log-in separately
- powerful search and browse functionality, specifically tailored for the NLN Materials (eg. browse by level)
- easy access to supporting information, such as tutor guides, LO-specific FAQ questions etc.
- no need to leave the Moodle interface
- no need to understand Scorm and related technologies
- no need to deal with any downloading/unzipping/uploading/or installing of files

Full information about Noodle, including any updates and new versions, is available at [http://www.nln.ac.uk/?p=Noodle]

This is version 1.1.


Version Info
------- ----
V0.1 - first release.
V0.2 - fixes compatibility with Moodle 1.8, by a modification in line 74 of resource.class.php, checking that the function build_navigation() exists.
V0.3 - fixes a bug that caused resources not to be added to the database in some environments.
V0.4 - fixes a compatibility problem with PHP4 (line 167 of resource.class.php). This version works with PHP4 and PHP5.
V1.0 - a significant update, with significant updates to this readme, and changes to both browse_start.php and resource.class.php that:
   - within the Moodle resource add/edit page, fixes the issue of the name field dragging back focus when the "browse" button is clicked
   - fixes a harmless typo in one of language strings given in step 4 of the installation instructions
   - adds the ability to launch the browser at a text search result (for integration with other modules, such as MrCute). (See customisation section below.)
   - adds the ability to customise the text of the "add to Moodle course" button within Noodle. (See customisation section below.)
   - uses a new URL within browse_start.php (noodle.nln.ac.uk rather than www.nln.ac.uk). (Both point to the same location, but the new URL helps us with tracking usage.)
V1.1 - due to changes in the NLN site on July 23rd 2011 (see the FAQ at http://nln.ac.uk/support/?p=FAQ#i_Transition for more info). Since the site no longer requires authentication, this has been removed from this version. However, V1.0 will continue to work fine, so there's no need to upgrade from V1.0 to V1.1.

Note that since the bulk of functionality happens on the NLN site, changes in functionality may occur within the Noodle pop-up without requiring a new version of Noodle or a new download. Any significant changes of functionality will be explained on the Noodle page of the NLN website.


License
-------
This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program. If not, see [http://www.gnu.org/licenses/].

By installing this software package and using it (or any derivative) to connect to the NLN site, you are also agreeing to the NLN Materials usage license ON BEHALF OF ALL MOODLE USERS WHO WILL BE GIVEN ACCESS TO IT. For more information please see [http://www.nln.ac.uk/?p=Noodle]. 

The GPL license confers no rights to the www.nln.ac.uk site itself (including the pages from the site exposed by Noodle).


Support
-------
Noodle was developed by Xtensis during their role as developers of the service, and was released free of charge as a (hopefully) useful additional service to the NLN Materials community. As of July 23rd, Xtensis have no ongoing involvement in developing or supporting the NLN service, and as such, there is no ongoing formal support for Noodle. A dedicated thread on the Moodle.org forums has been set up at [http://moodle.org/mod/forum/discuss.php?d=105505].


Installation
------------
Note, if you are upgrading an existing Noodle installation, please see the "Version info" section above.

Installation is simple:
1. Create a new folder in your Moodle install called "nln" within "/mod/resource/type/".
2. Unzip the contents of this zip into it.
3. Finally, open the file "/lang/en_utf8/resource.php" and add the following five lines before the final "?>".

$string['resourcetypenln'] = 'NLN Learning Object';
$string['nln_browse'] = 'Browse the NLN Materials';
$string['nln_browsedescrip'] = 'Click this button to view the NLN Materials browser, which lets you browse, preview, and select an NLN Learning Object';
$string['nln_guid'] = 'NLN Learning Object ID';
$string['nln_required'] = 'Please select an NLN Learning Object by clicking the button below. If you do not wish to add an NLN LO, click the Cancel button below.';

These provide the text for the various custom bits of interface exposed by Noodle - feel free to edit them if you wish. Respectively, they represent:
	1. The entry in the drop-down list of resource types that can be added to a course
	2. The caption of the browse button on the resource page
	3. The pop-up hint when hovering over the browse button
	4. The caption next to the read-only edit box that contains the NLN Learning Object's unique ID
	5. A message to be displayed if the user tries to "OK" the resource page without choosing an NLN LO

Note that as this affects a file within Moodle itself, whenever you upgrade to a new Moodle version this change will be reset, so you will need to repeat Step 3 whenever you move to a new Moodle version.

Finally, you may wish to visit the "resource defaults" page, to review/edit the default properties for resource pop-up windows (and whether to use a pop-up or embed the resource within the Moodle interface). If you have never visited this page and saved the changes, you may find that no defaults have been set at all. To visit the resource defaults page, from your Moodle home page find the "Site administration" block and navigate through the menu to Modules/Activities/Resource.


Updating Moodle
-------- ------
If upgrading Moodle itself to a new version, any existing Noodle installation should continue to work EXCEPT that Moodle's language string file will likely be overwritten, so you will probably need to repeat step 3 of the installation procedure given below.

Informing Moodle users
--------- ------ -----
After installing, we suggest notifying your Moodle course editors and letting them know what's available. You can find simplified instructions - which presume no prior knowledge of the NLN materials or the main NLN site - by clicking the "About the NLN Materials Browser" link at the foot of the first page within Noodle. It is suggested that Moodle admins take the concise instructions given there and distribute them, with any relevant modifications, to their users.
 

Technical Description
--------- -----------
Moodle has a flexible architecture for adding new resource types. The installation of Noodle simply adds a new available resource type, available for practitioners by choosing "NLN Learning Object" from the options available in the "add a resource" drop-down list. Installation requires no modifications to the database structure within Moodle, and does not interfere with any local repository, or any NLN materials already downloaded/installed/deployed via any other method. When a practitioner adds a resource of the new "nln" type, a new row is added to the "resources" table. The unique identifier of the LO that was chosen by the practitioner using the NLN browser is stored in the resource's "reference" field. The "popup" and "options" fields describe the display options, using much the same values as the built-in file/web resource type.

The installation contains the following:
 - readme.txt - this file.
 - resource.class.php - defines the new NLN resource type to Moodle
 - browse_start.php - is launched in a pop-up window, and, while showing a loading screen, passes the configuration values to, and launches, the special version of the NLN site.
 - browse_end.php - launched at the end of the browsing process, uses client-side scripting to populate the main Moodle window with the ID, title and description of the selected NLN LO.
(Previous versions had a nln_config.php file, which held authentication parameters, and is no longer required.)

