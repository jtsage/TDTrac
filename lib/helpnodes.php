<?php
/**
 * TDTrac Help Nodes
 * 
 * Contains help node raw data.
 * @package tdtrac
 * @version 1.4.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
$helpnode['error']['title'] = "Error";
$helpnode['error']['data'][] = "This help item has not yet been written.  Sorry.";

$helpnode['index']['title'] = "TDTrac Overview";
$helpnode['index']['data'][] = "TDTrac is a web based show budget and payroll hours tracker, built by a TD, for other TD's, freelance designers, and anyone else who finds it useful. TDTrac is completely free, released as open source.  We also offer hosting plans for a nominal fee (no domain required!) so you don't need to pay for webspace elsewhere.";
$helpnode['index']['data'][] = "TDTrac Features:";
$helpnode['index']['data'][] = "<ul><li>Track as many show budgets as you wish</li>" .
	"<li>Budgets can be organized by vendor, category, or even amount spent</li>" .
	"<li>Track payment pending budget items - things ordered but not cleared through any account or credit card you use</li>" .
	"<li>Track reimbursable budget items through the reciept of payment from your reimburser</li>" .
	"<li>E-Mail scanned or photographed reciepts to the tracker for safe keeping</li>" .
	"<li>Track as many active or inactive employees as you wish</li>" .
	"<li>Track which employee hours have been paid and which are outstanding</li>" .
	"<li>Configurable hourly or daily pay rate for budgeting purposes</li>" .
	"<li>Configurable hourly or daily pay methods for accurate work force accounting</li" .
	"<li>Allow your employees to add thier own hours, while being notified on your next login</li" .
	"<li>Optionally allow your employees to add budget expenses, or even view the current budget</li></ul>";

$helpnode['hours']['index']['title'] = "Payroll Tracking";
$helpnode['hours']['index']['data'][] = "<dl><dt>Add Hours Worked</dt><dd>Add Hours or Days worked by an employee.</dd>" .
	"<dt>View Hours Worked</dt><dd>View Hours or Days worked by an employee or employees in a date range.</dd>" .
	"<dt>View Hours Worked (unpaid)</dt><dd>View all Hours or Days worked by employees that have not yet been paid.</dd>" .
	"<dt>Send 'please submit hours' Reminder to Employees<dt><dd>Send a reminder e-mail to selected employees for them to log in and submit hours.</dd></dl>";

$helpnode['budget']['index']['title'] = "Budget Tracking";
$helpnode['budget']['index']['data'][] = "<dl><dt>Add Budget Expense</dt><dd>Add a budget expense.</dd>" .
	"<dt>View Budgets</dt><dd>View budgets on a per show basis, including labor budget</dd>" .
	"<dt>View Budgets (payment pending items only, all shows)</dt><dd>View payment pending budget items</dd>" .
	"<dt>View Budgets (reimbursment items only, all shows)</dt><dd>View all reimbursment budget items, paid or unpaid</dd>" .
	"<dt>View Budgets (reimbursment recieved items only, all shows)</dt><dd>View reimbursed, paid budget items</dd>" .
	"<dt>View Budgets (reimbursment not recieved items only, all shows)</dt><dd>View unreimbursed, still waiting budget items</dd></dl>";

$helpnode['shows']['index']['title'] = "Show Information";
$helpnode['shows']['index']['data'][] = "<dl><dt>Add Show</dt><dd>Add a show</dd>" .
	"<dt>View Shows</dt><dd>View shows available for use</dd></dl>";

$helpnode['todo']['index']['title'] = "ToDo Lists";
$helpnode['todo']['index']['data'][] =	"<dt>Add ToDo Item</dt><dd>Add a new todo item.</dd>" .
	"<dt>View ToDo Items</dt><dd>View full todo list by show.</dd>" .
	"<dt>View Personal ToDo Items</dt><dd>View all todo items assigned to your user.</dd>";


$helpnode['user']['index']['title'] = "User Managment";
$helpnode['user']['index']['data'][] = "<dl><dt>Add User</dt><dd>Add a user to the system</dd>" .
	"<dt>View Users</dt><dd>View and edit system users</dd>" .
	"<dt>Edit Permissions</dt><dd>Edit group based permissions</dd>" .
	"<dt>View Permissions</dt><dd>View current group based permissions</dd>" .
	"<dt>Set TDTracMail Subject Code</dt><dd>Set TDTracMail pickup subject code (passphrase)</dd>" .
	"<dt>Add / Edit Groups</dt><dd>Add, Edit, or rename groups (warning: this is slightly unsupported right now)</dd></dl>";
	
$helpnode['user']['mail']['title'] = "TDTracMail Subject Code";
$helpnode['user']['mail']['data'][] = "This contains the easy interface to update the subject line code, and the to: address for the tdtracmail addon, a simple way to add reciepts to your tracking.  Simply send email to [the address you choose]@[your hostname] (please ask your administrator which host to use), and include the code in the subject line.  All reciepts are automaticly resized, and will await addition to the database by any user with the 'addbudget' permission.";

$helpnode['mail']['index']['title'] = "Inbox View";
$helpnode['mail']['index']['data'][] = "The Inbox view shows any system messages that have been sent to you.";
$helpnode['mail']['index']['data'][] = "<dl><dt>Clear Messages Link</dt><dd>Clears all current inbox messages.  There is no 'mark as read', read messages are deleted</dd>" .
	"<dt>Per Message Delete Link</dt><dd>Removes that message from inbox.  There is no 'mark as read', read messages are deleted</dd></dl>";

$helpnode['mail']['view']['title'] = "Outbox View";
$helpnode['mail']['view']['data'][] = "The Inbox view shows any system messages that you have sent, but have not been removed by the recipient.";
$helpnode['mail']['view']['data'][] = "<dl><dt>Per Message Nuke Link</dt><dd>Removes that message from outbox.  This is an admin only function. There is no 'mark as read', read messages are deleted</dd></dl>";

$helpnode['hours']['view']['title'] = "View Payroll";
$helpnode['hours']['view']['data'][] = "<strong>View By Employee</strong> - Show hours worked by the specified employee in the date range.";
$helpnode['hours']['view']['data'][] = "<strong>View By Date</strong> - Show all employees that worked during the specified dates";
$helpnode['hours']['view']['data'][] = "<strong>View All Un-Paid Hours</strong> - Show all hours that are pending payment to employees.";
$helpnode['hours']['view']['data'][] = "Each report contains edit and delete links, along with links to e-mail the report to yourself";

$helpnode['hours']['edit']['title'] = "Edit Hours Form";
$helpnode['hours']['edit']['data'][] = "Allows you to edit existing payroll items";
$helpnode['hours']['edit']['data'][] = "<dl><dt>Employee</dt><dd>The employee's name who you wish to edit hours for.  When editting, this cannot be changed.</dd>" .
	"<dt>Show</dt><dd>The associated show name for these hours worked.</dd>" .
	"<dt>Date</dt><dd>The date of the hours worked.  The 'today' link will fill in the current day.  The 'cal' link presents a javascript popup calendar.</dd>" .
	"<dt>Hours Worked</dt><dd>Number of hours worked.  In day rate mode, number of days worked (title will update accordingly</dd>" .
	"<dt>Hours Paid Out</dt><dd>Toggle whether the hours have been paid to the employee.</dd></dl>";

$helpnode['hours']['add']['title'] = "Add Hours Form";
$helpnode['hours']['add']['data'] = array("Allows you to add new payroll items", $helpnode['hours']['edit']['data'][1]);

$helpnode['hours']['del']['title'] = "Delete Hours Form";
$helpnode['hours']['del']['data'] = array("This is a confirmation screen for removing payroll items", $helpnode['hours']['edit']['data'][1]);

$helpnode['hours']['remind']['title'] = "Remind Employees to Submit Hours";
$helpnode['hours']['remind']['data'][] = "Select the employees you wish to have recieve a server-generated e-mail asking them to log in and submit their hours before the specified due date for the pay period between the two dates listed.";

$helpnode['shows']['add']['title'] = "Add Show Form";
$helpnode['shows']['add']['data'][] = "Allows the addition of a new show or job name for budget and payroll items.";
$helpnode['shows']['add']['data'][] = "<dl><dt>Show Name</dt><dd>The Name of the show or job.</dd>" .
	"<dt>Show Company</dt><dd>The name of the associated company for this show or job.  Largely unused now, may be used in the future for per-company budget and labor reports.</dd>" .
	"<dt>Show Venue</dt><dd>The venue or location associated with this show or job.  Currently for informational purposes only</dd>" .
	"<dt>Show Dates</dt><dd>The dates for this show or job.  Currently for informational purposes only.</dd>" .
	"<dt>Show Record Open</dt><dd>This allows new budget and payroll items to be added, otherwise the show is hidden</dd></dl>";

$helpnode['shows']['edit']['title'] = "Edit Show Form";
$helpnode['shows']['edit']['data'] = array("Allows editing of current shows or jobs for budget and payroll items", $helpnode['shows']['add']['data'][1]);

$helpnode['shows']['view']['title'] = "View Shows";
$helpnode['shows']['view']['data'][] = "Displays all shows or job names, past or present, in the reverse order of which they were added.  This is always the sort order for shows.";
$helpnode['shows']['view']['data'][] = "<dl><dt>Edit Link</dt><dd>Allows editing the show name, venue, company, and dates.</dd></dl>";

$helpnode['user']['edit']['title'] = "Edit User Form";
$helpnode['user']['edit']['data'][] = "Allows editing of a user to the system.";
$helpnode['user']['edit']['data'][] = "<dl><dt>User Name</dt><dd>The username, or login name for the user.</dd>" .
	"<dt>Password</dt><dd>An initial password for the user.  Users are encouraged to change their password on first successful login.</dd>" .
	"<dt>Payrate</dt><dd>The user's pay rate, by day or by hour dependant on install configuration.</dd>" .
	"<dt>First Name</dt><dd>The user's first name.</dd>" .
	"<dt>Last Name</dt><dd>The user's last name.</dd>" .
	"<dt>Phone</dt><dd>The user's phone number.  For informational purposes only.</dd>" .
	"<dt>E-Mail</dt><dd>The user's e-mail address, used in the 'e-mail to self' links throughout the site.</dd>" .
	"<dt>Group</dt><dd>The user's permission group.  Special group 'employee' limits the add hours form to only the logged in user.  Special group 'admin' gives full access to all features.</dd>" .
	"<dt>User Active</dt><dd>The user's status.  Inactive users appear in no reports, pick lists, or are allowed to login.</dd>" .
	"<dt>Add / Edit / View only Own Hours</dt><dd>Limit user to only viewing, adding, and editing thier own hours.</dd>" .
	"<dt>User on Payroll</dt><dd>The user's payroll status. Active payroll users appear in the add payroll hours picklist.</dd>" .
	"<dt>Admin Notify on Employee Add of Payroll</dt><dd>When toggeled on, a user in the employee group adding hours will trigger a message sent to this user to let them know of the action.  Particularly useful for admins, shop or shift managers, etc.</dd></dl>";

$helpnode['user']['add']['title'] = "Add User Form";
$helpnode['user']['add']['data'][] = "Allows adding a user to the system";
$helpnode['user']['add']['data'][] = $helpnode['user']['edit']['data'][1];

$helpnode['user']['view']['title'] = "View Users";
$helpnode['user']['view']['data'][] = "Displays all system users, sorted by last name";
$helpnode['user']['view']['data'][] = $helpnode['user']['edit']['data'][1];
$helpnode['user']['view']['data'][] = "The edit link allows administrators to edit each user";

$helpnode['user']['perms']['title'] = "Permissions System";
$helpnode['user']['perms']['data'][] = "Allows editing and view of the permissions system. Permissions are group based throughout the site.  Only members of the 'admin' group may edit permissions.";
$helpnode['user']['perms']['data'][] = "<strong>Description of permissions</strong>";
$helpnode['user']['perms']['data'][] = "<dl><dt>addshow</dt><dd>Can Add New Shows / Jobs</dd>" .
	"<dt>viewshow</dt><dd>Can View Current and Past Shows / Jobs</dd>" .
	"<dt>editshow</dt><dd>Can Edit Shows / Jobs information</dd>" .
	"<dt>addbudget</dt><dd>Can Add Expenses</dd>" .
	"<dt>editbudget</dt><dd>Can Edit or Delete Expenses Information</dd>" .
	"<dt>viewbudget</dt><dd>Can View budget details, including the labor cost overview</dd>" .
	"<dt>addhours</dt><dd>Can Add Hours for employees on payroll<ul><li><strong>NOTE:</strong> they can only add hours for themselves if set in user record</li></ul></dd>" .
	"<dt>edithours</dt><dd>Can Edit or Delete Hours for employees on payroll<ul><li><strong>NOTE:</strong> they can only edit thier own hours if set in user record</li></ul></dd>" .
	"<dt>viewhours</dt><dd>Can View Labor reports<ul><li><strong>NOTE:</strong> they can only view thier own hours if set in user record</li></dd>" .
	"<dt>adduser</dt><dd>Can Add New Users / Employees to the program</dd></dl>";

$helpnode['user']['groups']['title'] = "Groups on TDTrac";
$helpnode['user']['groups']['data'][] = "This page allows the renaming of groups and addition of new groups.  Groups are nothing more than permission sets.  You may have as many groups as you like, and as of version 1.2.1, groups names are largely meaningless, they have no intrinsic permissions.  You cannot rename the admin group, as it is a system group.";

$helpnode['user']['password']['title'] = "Password Reminder";
$helpnode['user']['password']['data'][] = "This will send the login details associated with the entered e-mail address to that e-mail.  If you do not recieve an e-mail, or can't remember your e-mail, please contact your administrator.  If you are the administrator, and this installation is hosted on tdtrac.com, please contact the management via the homepage.";

$helpnode['rcpt']['index']['title'] = "Reciept Managment";
$helpnode['rcpt']['index']['data'][] = "This page allows management of e-mailed reciepts.  You may add a new record of the reciept, or associate it with an old menu item.  See the help under the Add Budget Item for a description of all the fields here";

$helpnode['budget']['edit']['title'] = "Edit Budget Expense Form";
$helpnode['budget']['edit']['data'][] = "Allows the editing of a budget item";
$helpnode['budget']['edit']['data'][] = "<dl><dt>Show</dt><dd>Name of the show or job.</dd>" .
	"<dt>Date</dt><dd>Date of charge.  The 'today' link will fill in the current day, the 'cal' link displays a javascript calendar.</dd>" .
	"<dt>Vendor</dt><dd>The name of the vendor - this field will autocomplete from past entries</dd>" .
	"<dt>Category</dt><dd>The name of a category for the charge - this field will autocomplete from past entries</dd>" .
	"<dt>Description</dt><dd>A description of the charge.</dd>" .
	"<dt>Price</dt><dd>The amount of the charge, in dollars.</dd>" .
	"<dt>Pending Payment</dt><dd>Toggle on for charges that have been approved, but not yet cleared on any credit card of bank account.</dd>" .
	"<dt>Reimbursment Charge</dt><dd>Toggle on for charges that need to be reimbursed to cash or personal credit cards.</dd>" .
	"<dt>Reimbursment Recieved</dt><dd>Toggle on for reimbursment charges that have been paid out.</dd></dl>";

$helpnode['budget']['add']['title'] = "Add Budget Expense Form";
$helpnode['budget']['add']['data'] = array("Allows add a new budget item", $helpnode['budget']['edit']['data'][1]);

$helpnode['budget']['del']['title'] = "Delete Budget Expense Form";
$helpnode['budget']['del']['data'] = array("Confirmation form prior to deleting a budget item", $helpnode['budget']['edit']['data'][1]);

$helpnode['budget']['view']['title'] = "Budget Views";
$helpnode['budget']['view']['data'][] = "This show all budget items for a configured time period or other condition.";
$helpnode['budget']['view']['data'][] = "Actions all have hover text to explain thier purpose, additionally you can e-mail the report to yourself";
$helpnode['budget']['view']['data'][] = "<strong>Special Reports</strong>";
$helpnode['budget']['view']['data'][] = "<ul><li>Pending payment budget items</li><li>All Reimbursment type charges</li><li>Reimbursment still needed charges</li><li>Reimbursment recieved items</li></ul>";

$helpnode['todo']['add']['title'] = "Add To-Do Item";
$helpnode['todo']['add']['data'][] = "Allows you to add a new to-do item";
$helpnode['todo']['add']['data'][] = "<dl><dt>Show</dt><dd>Show to associate item with</dd>" .
	"<dt>Priority</dt><dd>Priority of this item, used in sorting the list</dd>" .
	"<dt>Due Date</dt><dd>The date the task should be completed before it's considered overdue</dd>" .
	"<dt>Assigned To</dt><dd>The user that should complete the task</dd>" .
	"<dt>Description</dt><dd>A Description of the required task</dd>";

$helpnode['todo']['view']['title'] = "To-Do List View";
$helpnode['todo']['view']['data'][] = "Allows you to view todo lists; either by show, assigned user, or those items that are overdue.";
$helpnode['todo']['view']['data'][] = "A link is also provided to mark the task as done.  At this time, no functionality for editing existing items exists.";
$helpnode['todo']['view']['data'][] = "<strong>Description of fields</strong>";
$helpnode['todo']['view']['data'][] = $helpnode['todo']['add']['data'][1];

?>
