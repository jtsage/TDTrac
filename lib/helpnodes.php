<?php
/**
 * TDTrac Help Nodes
 * 
 * Contains help node raw data.
 * @package tdtrac
 * @version 1.3.1
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
$helpnode['hours']['index']['data'][] = "Add Hours Worked - Add Hours or Days worked by an employee.";
$helpnode['hours']['index']['data'][] = "View Hours Worked - View Hours or Days worked by an employee or employees in a date range.";
$helpnode['hours']['index']['data'][] = "View Hours Worked (unpaid) - View all Hours or Days worked by employees that have not yet been paid.";
$helpnode['hours']['index']['data'][] = "Send 'please submit hours' Reminder to Employees - Send a reminder e-mail to selected employees for them to log in and submit hours.";

$helpnode['budget']['index']['title'] = "Budget Tracking";
$helpnode['budget']['index']['data'][] = "Add Budget Expense - Add a budget expense.";
$helpnode['budget']['index']['data'][] = "View Budgets - View budgets on a per show basis, including labor budget";
$helpnode['budget']['index']['data'][] = "View Budgets (payment pending items only, all shows) - View payment pending budget items";
$helpnode['budget']['index']['data'][] = "View Budgets (reimbursment items only, all shows) - View all reimbursment budget items, paid or unpaid";
$helpnode['budget']['index']['data'][] = "View Budgets (reimbursment recieved items only, all shows) - View reimbursed, paid budget items";
$helpnode['budget']['index']['data'][] = "View Budgets (reimbursment not recieved items only, all shows) - View unreimbursed, still waiting budget items";

$helpnode['shows']['index']['title'] = "Show Information";
$helpnode['shows']['index']['data'][] = "Add Show - Add a show";
$helpnode['shows']['index']['data'][] = "View Shows - View shows available for use";

$helpnode['todo']['index']['title'] = "ToDo Lists";
$helpnode['todo']['index']['data'][] = "Add ToDo Item - Add a new todo item.";
$helpnode['todo']['index']['data'][] = "View ToDo Items - View full todo list by show.";
$helpnode['todo']['index']['data'][] = "View Personal ToDo Items - View all todo items assigned to your user.";


$helpnode['user']['index']['data'][] = "<li>User Managment<ul>";
//$helpnode['index']['data'][] = "<li>Add User - Add a user to the system</li>";
//$helpnode['index']['data'][] = "<li>View Users - View and edit system users</li>";
//$helpnode['index']['data'][] = "<li>Edit Permissions - Edit group based permissions</li>";
//$helpnode['index']['data'][] = "<li>View Permissions - View current group based permissions</li>";
//$helpnode['index']['data'][] = "<li>Add / Edit Groups - Add, Edit, or rename groups (warning: this is slightly unsupported right now)</li></ul></li>";

$helpnode['main-budget'] = $helpnode['index']['data'];
$helpnode['main-hours'] = $helpnode['index']['data'];
$helpnode['main-show'] = $helpnode['index']['data'];
$helpnode['main-perms'] = $helpnode['index']['data'];

$helpnode['mail-perms'][] = "<h3>TDTracMail Subject Code</h3>";
$helpnode['mail-perms'][] = "<p>This contains the easy interface to update the subject line code, and the to: address for the tdtracmail addon, a simple way to add reciepts to your tracking.  Simply send email to [the address you choose]@[your hostname] (please ask your administrator which host to use), and include the code in the subject line.  All reciepts are automaticly resized, and will await addition to the database by any user with the 'addbudget' permission.</p>";

$helpnode['msg-read'][] = "<h3>Inbox View</h3>";
$helpnode['msg-read'][] = "<p>The Inbox view shows any system messages that have been sent to you.<ul>";
$helpnode['msg-read'][] = "<li>Clear Messages Link<ul><li>Clears all current inbox messages.  There is no 'mark as read', read messages are deleted</li></ul></li>";
$helpnode['msg-read'][] = "<li>Per Message Delete Link<ul><li>Removes that message from inbox.  There is no 'mark as read', read messages are deleted</li></ul></li></ul></p>";

$helpnode['msg-view'][] = "<h3>Outbox View</h3>";
$helpnode['msg-view'][] = "<p>The Inbox view shows any system messages that you have sent, but have not been removed by the recipient.<ul>";
$helpnode['msg-view'][] = "<li>Per Message Nuke Link<ul><li>Removes that message from outbox.  This is an admin only function. There is no 'mark as read', read messages are deleted</li></ul></li></ul></p>";

$helpnode['add-hours'][] = "<h3>Add Hours Form</h3>";
$helpnode['add-hours'][] = "<p><ul><li>Employee<ul><li>The employee's name who you wish to add hours for.  When logged in as an employee, only that login name will show here</li></ul></li>";
$helpnode['add-hours'][] = "<li>Show<ul><li>The associated show name for these hours worked.</li></ul></li>";
$helpnode['add-hours'][] = "<li>Date<ul><li>The date of the hours worked.  The 'today' link will fill in the current day.  The 'cal' link presents a javascript popup calendar.</li></ul></li>";
$helpnode['add-hours'][] = "<li>Hours Worked<ul><li>Number of hours worked.  In day rate mode, number of days worked (title will update accordingly</li></ul></li></ul></p>";

$helpnode['view-hours'][] = "<h3>View Hours Form</h3>";
$helpnode['view-hours'][] = "<p><strong>View By Employee</strong> - Show hours worked by the specified employee in the date range.<ul><li>Employee<ul><li>The employee's name who you wish to view hours for. Only active employees are shown.  When logged in as an employee, only that login name will show here</li></ul></li>";
$helpnode['view-hours'][] = "<li>Start Date<ul><li>The start date of the hours worked, included in the set.  The 'cal' link presents a javascript popup calendar.</li></ul></li>";
$helpnode['view-hours'][] = "<li>End Date<ul><li>The end date of the hours worked, included in the set.  The 'today' link will fill in the current day.  The 'cal' link presents a javascript popup calendar.</li></ul></li></ul></p>";
$helpnode['view-hours'][] = "<p><strong>View By Date</strong> - Show all employees that worked during the specified dates<ul>";
$helpnode['view-hours'][] = "<li>Start Date<ul><li>The start date of the hours worked, included in the set.  The 'cal' link presents a javascript popup calendar.</li></ul></li>";
$helpnode['view-hours'][] = "<li>End Date<ul><li>The end date of the hours worked, included in the set.  The 'today' link will fill in the current day.  The 'cal' link presents a javascript popup calendar.</li></ul></li></ul></p>";
$helpnode['view-hours'][] = "<p><strong>View All Un-Paid Hours</strong> - Show all hours that are pending payment to employees.</p>";
$helpnode['view-hours'][] = "<h3>View Hours Report</h3>";
$helpnode['view-hours'][] = "<p>Shows hours as per above criteria.  Data shown is the date worked, hours worked that day, the show the hours are associated with, and whether the hours have been paid to the employee yet.";
$helpnode['view-hours'][] = "<ul><li>Edit Link<ul><li>Edit the hours entry</li></ul></li>";
$helpnode['view-hours'][] = "<li>Delete Link<ul><li>Delete the hours entry (with confirmation)</li></ul></li>";
$helpnode['view-hours'][] = "<li>E-Mail to self Link<ul><li>E-Mail a copy of this information the e-mail address listed in your user profile</li></ul></li></ul></p>";

$helpnode['edit-hours'][] = "<h3>Edit Hours Form</h3>";
$helpnode['edit-hours'][] = "<p><ul><li>Employee<ul><li>The employee's name who you wish to edit hours for.  When editting, this cannot be changed.</li></ul></li>";
$helpnode['edit-hours'][] = "<li>Show<ul><li>The associated show name for these hours worked.</li></ul></li>";
$helpnode['edit-hours'][] = "<li>Date<ul><li>The date of the hours worked.  The 'today' link will fill in the current day.  The 'cal' link presents a javascript popup calendar.</li></ul></li>";
$helpnode['edit-hours'][] = "<li>Hours Worked<ul><li>Number of hours worked.  In day rate mode, number of days worked (title will update accordingly</li></ul></li>";
$helpnode['edit-hours'][] = "<li>Hours Paid Out<ul><li>Toggle whether the hours have been paid to the employee.</li></ul></li></ul></p>";

$helpnode['remind-hours'][] = "<h3>Remind Employees to Submit Hours</h3>";
$helpnode['remind-hours'][] = "<p>Select the employees you wish to have recieve a server-generated e-mail asking them to log in and submit their hours before the specified due date for the pay period between the two dates listed.</p>";

$helpnode['add-show'][] = "<h3>Add Show Form</h3>";
$helpnode['add-show'][] = "<p>Allows the addition of a new show or job name for budget and payroll items.<ul><li>Show Name<ul><li>The Name of the show or job.</li></ul></li>";
$helpnode['add-show'][] = "<li>Show Company<ul><li>The name of the associated company for this show or job.  Largely unused now, may be used in the future for per-company budget and labor reports.</li></ul></li>";
$helpnode['add-show'][] = "<li>Show Venue<ul><li>The venue or location associated with this show or job.  Currently for informational purposes only</li></ul></li>";
$helpnode['add-show'][] = "<li>Show Dates<ul><li>The dates for this show or job.  Currently for informational purposes only.</li></ul></li></ul></p>";

$helpnode['edit-show'][] = "<h3>Edit Show Form</h3>";
$helpnode['edit-show'][] = "<p>Allows editing the detail of a show or job for budget and payroll items.<ul><li>Show Name<ul><li>The Name of the show or job.</li></ul></li>";
$helpnode['edit-show'][] = "<li>Show Company<ul><li>The name of the associated company for this show or job.  Largely unused now, may be used in the future for per-company budget and labor reports.</li></ul></li>";
$helpnode['edit-show'][] = "<li>Show Venue<ul><li>The venue or location associated with this show or job.  Currently for informational purposes only</li></ul></li>";
$helpnode['edit-show'][] = "<li>Show Dates<ul><li>The dates for this show or job.  Currently for informational purposes only.</li></ul></li></ul></p>";

$helpnode['view-show'][] = "<h3>View Shows</h3>";
$helpnode['view-show'][] = "<p>Displays all shows or job names, past or present, in the reverse order of which they were added.  This is always the sort order for shows.<ul><li>Edit Link<ul><li>Allows editing the show name, venue, company, and dates.</li></ul></li></ul></p>";

$helpnode['add-user'][] = "<h3>Add User Form</h3>";
$helpnode['add-user'][] = "<p>Allows adding a user to the system.<ul><li>User Name<ul><li>The username, or login name for the user.</li></ul></li>";
$helpnode['add-user'][] = "<li>Password<ul><li>An initial password for the user.  Users are encouraged to change their password on first successful login.</li></ul></li>";
$helpnode['add-user'][] = "<li>First Name<ul><li>The user's first name.</li></ul></li>";
$helpnode['add-user'][] = "<li>Last Name<ul><li>The user's last name.</li></ul></li>";
$helpnode['add-user'][] = "<li>Phone<ul><li>The user's phone number.  For informational purposes only.</li></ul></li>";
$helpnode['add-user'][] = "<li>E-Mail<ul><li>The user's e-mail address, used in the 'e-mail to self' links throughout the site.</li></ul></li>";
$helpnode['add-user'][] = "<li>Group<ul><li>The user's permission group.  Special group 'employee' limits the add hours form to only the logged in user.  Special group 'admin' gives full access to all features.</li></ul></li></ul></p>";

$helpnode['edit-user'][] = "<h3>Edit User Form</h3>";
$helpnode['edit-user'][] = "<p>Allows adding a user to the system.<ul><li>User Name<ul><li>The username, or login name for the user.</li></ul></li>";
$helpnode['edit-user'][] = "<li>Password<ul><li>An initial password for the user.  Users are encouraged to change their password on first successful login.</li></ul></li>";
$helpnode['edit-user'][] = "<li>Payrate<ul><li>The user's pay rate, by day or by hour dependant on install configuration.</li></ul></li>";
$helpnode['edit-user'][] = "<li>First Name<ul><li>The user's first name.</li></ul></li>";
$helpnode['edit-user'][] = "<li>Last Name<ul><li>The user's last name.</li></ul></li>";
$helpnode['edit-user'][] = "<li>Phone<ul><li>The user's phone number.  For informational purposes only.</li></ul></li>";
$helpnode['edit-user'][] = "<li>E-Mail<ul><li>The user's e-mail address, used in the 'e-mail to self' links throughout the site.</li></ul></li>";
$helpnode['edit-user'][] = "<li>Group<ul><li>The user's permission group.  Special group 'employee' limits the add hours form to only the logged in user.  Special group 'admin' gives full access to all features.</li></ul></li>";
$helpnode['edit-user'][] = "<li>User Active<ul><li>The user's status.  Inactive users appear in no reports, pick lists, or are allowed to login.</li></ul></li>";
$helpnode['view-user'][] = "<li>Add / Edit / View only Own Hours<ul><li>Limit user to only viewing, adding, and editing thier own hours.</li></ul></li>";
$helpnode['edit-user'][] = "<li>User on Payroll<ul><li>The user's payroll status. Active payroll users appear in the add payroll hours picklist.</li></ul></li>";
$helpnode['edit-user'][] = "<li>Admin Notify on Employee Add of Payroll<ul><li>When toggeled on, a user in the employee group adding hours will trigger a message sent to this user to let them know of the action.  Particularly useful for admins, shop or shift managers, etc.</li></ul></li></ul></p>";

$helpnode['view-user'][] = "<h3>View Users</h3>";
$helpnode['view-user'][] = "<p>Displays all system users, sorted by last name.<ul><li>User Name<ul><li>The username, or login name for the user.</li></ul></li>";
$helpnode['view-user'][] = "<li>Payrate<ul><li>The user's pay rate, by day or by hour dependant on install configuration.</li></ul></li>";
$helpnode['view-user'][] = "<li>Phone<ul><li>The user's phone number.  For informational purposes only.</li></ul></li>";
$helpnode['view-user'][] = "<li>E-Mail<ul><li>The user's e-mail address, used in the 'e-mail to self' links throughout the site.</li></ul></li>";
$helpnode['view-user'][] = "<li>Group<ul><li>The user's permission group.  Special group 'employee' limits the add hours form to only the logged in user.  Special group 'admin' gives full access to all features.</li></ul></li>";
$helpnode['view-user'][] = "<li>User Active<ul><li>The user's status.  Inactive users appear in no reports, pick lists, or are allowed to login.</li></ul></li>";
$helpnode['view-user'][] = "<li>User on Payroll<ul><li>The user's payroll status. Active payroll users appear in the add payroll hours picklist.</li></ul></li>";
$helpnode['view-user'][] = "<li>Add / Edit / View only Own Hours<ul><li>Limit user to only viewing, adding, and editing thier own hours.</li></ul></li>";
$helpnode['view-user'][] = "<li>Admin Notify on Employee Add of Payroll<ul><li>When toggeled on, a user in the employee group adding hours will trigger a message sent to this user to let them know of the action.  Particularly useful for admins, shop or shift managers, etc.</li></ul></li>";
$helpnode['view-user'][] = "<li>Edit Link<ul><li>Allows editting of the user's details.</li></ul></li></ul></p>";

$helpnode['edit-perms'][] = "<h3>Edit Permissions Pick List</h3>";
$helpnode['edit-perms'][] = "<p>Allows the choice of which group to edit permissions for.</p>";
$helpnode['edit-perms'][] = "<h3>Permissions Descriptions</h3>";
$helpnode['edit-perms'][] = "<p><ul><li>addshow<ul><li>Can Add New Shows / Jobs</li></ul></li>";
$helpnode['edit-perms'][] = "<li>viewshow<ul><li>Can View Current and Past Shows / Jobs</li></ul></li>";
$helpnode['edit-perms'][] = "<li>editshow<ul><li>Can Edit Shows / Jobs information</li></ul></li>";
$helpnode['edit-perms'][] = "<li>addbudget<ul><li>Can Add Expenses</li></ul></li>";
$helpnode['edit-perms'][] = "<li>editbudget<ul><li>Can Edit or Delete Expenses Information</li></ul></li>";
$helpnode['edit-perms'][] = "<li>viewbudget<ul><li>Can View budget details, including the labor cost overview</li></ul></li>";
$helpnode['edit-perms'][] = "<li>addhours<ul><li>Can Add Hours for employees on payroll<ul><li><strong>NOTE:</strong> they can only add hours for themselves if set in user record</li></ul></li></ul></li>";
$helpnode['edit-perms'][] = "<li>edithours<ul><li>Can Edit or Delete Hours for employees on payroll<ul><li><strong>NOTE:</strong> they can only edit thier own hours if set in user record</li></ul></li></ul></li>";
$helpnode['edit-perms'][] = "<li>viewhours<ul><li>Can View Labor reports<ul><li><strong>NOTE:</strong> they can only view thier own hours if set in user record</li></ul></li></ul></li>";
$helpnode['edit-perms'][] = "<li>adduser<ul><li>Can Add New Users / Employees to the program</li></ul></li>";
$helpnode['edit-perms'][] = "<li>NOTE<ul><li>Editing permissions, Adding, and Editing groups is restricted to people in the 'admin' group.</li></ul></li></ul></p>";

$helpnode['view-perms'][] = "<h3>View Permissions Pick List</h3>";
$helpnode['view-perms'][] = "<p>Shows permissions by group.  Only permissions set as 'true' are shown.  False is the default state.</p>";
$helpnode['view-perms'][] = "<h3>Permissions Descriptions</h3>";
$helpnode['view-perms'][] = "<p><ul><li>addshow<ul><li>Can Add New Shows / Jobs</li></ul></li>";
$helpnode['view-perms'][] = "<li>viewshow<ul><li>Can View Current and Past Shows / Jobs</li></ul></li>";
$helpnode['view-perms'][] = "<li>editshow<ul><li>Can Edit Shows / Jobs information</li></ul></li>";
$helpnode['view-perms'][] = "<li>addbudget<ul><li>Can Add Expenses</li></ul></li>";
$helpnode['view-perms'][] = "<li>editbudget<ul><li>Can Edit or Delete Expenses Information</li></ul></li>";
$helpnode['view-perms'][] = "<li>viewbudget<ul><li>Can View budget details, including the labor cost overview</li></ul></li>";
$helpnode['view-perms'][] = "<li>addhours<ul><li>Can Add Hours for employees on payroll<ul><li><strong>NOTE:</strong> they can only add hours for themselves if set in user record</li></ul></li></ul></li>";
$helpnode['view-perms'][] = "<li>edithours<ul><li>Can Edit or Delete Hours for employees on payroll<ul><li><strong>NOTE:</strong> they can only edit thier own hours if set in user record</li></ul></li></ul></li>";
$helpnode['view-perms'][] = "<li>viewhours<ul><li>Can View Labor reports<ul><li><strong>NOTE:</strong> they can only view thier own hours if set in user record</li></ul></li></ul></li>";
$helpnode['view-perms'][] = "<li>adduser<ul><li>Can Add New Users / Employees to the program</li></ul></li>";
$helpnode['view-perms'][] = "<li>NOTE<ul><li>Editing permissions, Adding, and Editing groups is restricted to people in the 'admin' group.</li></ul></li></ul></p>";

$helpnode['groups'][] = "<h3>Groups on TDTrac</h3>";
$helpnode['groups'][] = "<p>This page allows the renaming of groups and addition of new groups.  Groups are nothing more than permission sets.  You may have as many groups as you like, and as of version 1.2.1, groups names are largely meaningless, they have no intrinsic permissions.  You cannot rename the admin group, as it is a system group.</p>";

$helpnode['rcpt'][] = "<h3>Reciept View</h3>";
$helpnode['rcpt'][] = "<p>This page allows management of e-mailed reciepts.  You may add a new record of the reciept, or associate it with an old menu item.  See the help under the Add Budget Item for a description of all the fields here</p>";

$helpnode['pwremind'][] = "<h3>Password Reminder</h3>";
$helpnode['pwremind'][] = "<p>This will send the login details associated with the entered e-mail address to that e-mail.  If you do not recieve an e-mail, or can't remember your e-mail, please contact your administrator.  If you are the administrator, and this installation is hosted on tdtrac.com, please contact the management via the homepage.</p>";

$helpnode['add-budget'][] = "<h3>Add Budget Expense Form</h3>";
$helpnode['add-budget'][] = "<p>Allows the addition of a new budget item<ul><li>Show<ul><li>Name of the show or job.</li></ul></li>";
$helpnode['add-budget'][] = "<li>Date<ul><li>Date of charge.  The 'today' link will fill in the current day, the 'cal' link displays a javascript calendar.</li></ul></li>";
$helpnode['add-budget'][] = "<li>New Vendor<ul><li>The name of the vendor - or:</li></ul></li>";
$helpnode['add-budget'][] = "<li>Old Vendor<ul><li>The name of the vendor, from past charges.  Displayed with most popular vendors first.  Overrides the above.</li></ul></li>";
$helpnode['add-budget'][] = "<li>New Category<ul><li>The name of a category for the charge - or:</li></ul></li>";
$helpnode['add-budget'][] = "<li>Old Category<ul><li>The name of a category from past charges.  Displayed with most popular categories first.  Overrides the above.</li></ul></li>";
$helpnode['add-budget'][] = "<li>Description<ul><li>A description of the charge.</li></ul></li>";
$helpnode['add-budget'][] = "<li>Price<ul><li>The amount of the charge, in dollars.</li></ul></li>";
$helpnode['add-budget'][] = "<li>Pending Payment<ul><li>Toggle on for charges that have been approved, but not yet cleared on any credit card of bank account.</li></ul></li>";
$helpnode['add-budget'][] = "<li>Reimbursment Charge<ul><li>Toggle on for charges that need to be reimbursed to cash or personal credit cards.</li></ul></li>";
$helpnode['add-budget'][] = "<li>Reimbursment Recieved<ul><li>Toggle on for reimbursment charges that have been paid out.</li></ul></li></ul></p>";

$helpnode['edit-budget'][] = "<h3>Edit Budget Expense Form</h3>";
$helpnode['edit-budget'][] = "<p>Allows the editing of a budget item<ul><li>Show<ul><li>Name of the show or job.</li></ul></li>";
$helpnode['edit-budget'][] = "<li>Date<ul><li>Date of charge.  The 'today' link will fill in the current day, the 'cal' link displays a javascript calendar.</li></ul></li>";
$helpnode['edit-budget'][] = "<li>New Vendor<ul><li>The name of the vendor - or:</li></ul></li>";
$helpnode['edit-budget'][] = "<li>Old Vendor<ul><li>The name of the vendor, from past charges.  Displayed with most popular vendors first.  Overrides the above.</li></ul></li>";
$helpnode['edit-budget'][] = "<li>New Category<ul><li>The name of a category for the charge - or:</li></ul></li>";
$helpnode['edit-budget'][] = "<li>Old Category<ul><li>The name of a category from past charges.  Displayed with most popular categories first.  Overrides the above.</li></ul></li>";
$helpnode['edit-budget'][] = "<li>Description<ul><li>A description of the charge.</li></ul></li>";
$helpnode['edit-budget'][] = "<li>Price<ul><li>The amount of the charge, in dollars.</li></ul></li>";
$helpnode['edit-budget'][] = "<li>Pending Payment<ul><li>Toggle on for charges that have been approved, but not yet cleared on any credit card of bank account.</li></ul></li>";
$helpnode['edit-budget'][] = "<li>Reimbursment Charge<ul><li>Toggle on for charges that need to be reimbursed to cash or personal credit cards.</li></ul></li>";
$helpnode['edit-budget'][] = "<li>Reimbursment Recieved<ul><li>Toggle on for reimbursment charges that have been paid out.</li></ul></li></ul></p>";

$helpnode['del-hours'][] = "<h3>Delete Payroll Expense</h3>";
$helpnode['del-hours'][] = "<p>Confirmation for payroll expense delete.</p>";

$helpnode['del-budget'][] = "<h3>Delete Budget Expense</h3>";
$helpnode['del-budget'][] = "<p>Confirmation for budget expense delete.</p>";

$helpnode['view-budget'][] = "<h3>View Budget Expenses Pick List</h3>";
$helpnode['view-budget'][] = "<p>Choose the show you wish to see associated budget expenses for</p>";
$helpnode['view-budget'][] = "<h3>Budget Expense Report</h3>";
$helpnode['view-budget'][] = "<p>This shows all items, vendors, categories, descriptions, prices, pending status, and reimbursment status of each shows budgets.  It also shows the show or job details, and a shortened labor summary for the show or job.</p>";

$helpnode['view-budget-special'][] = "<h3>Budget Expense Special Report</h3>";
$helpnode['view-budget-special'][] = "<p>This shows all items, vendors, categories, descriptions, prices, pending status, and reimbursment status of all shows budgets.</p>";
$helpnode['view-budget-special'][] = "<p>Additionally, this view is limited to one of the following:<ul><li>Pending payment budget items</li><li>All Reimbursment type charges</li><li>Reimbursment still needed charges</li><li>Reimbursment recieved items</li></p>";

