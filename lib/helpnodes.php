<?php
/**
 * TDTrac Help Nodes
 * 
 * Contains help node raw data.
 * @package tdtrac
 * @version 3.0.0
 * @since 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
 
/* BASIC ERROR MESSAGE */
$helpnode['error']['title'] = "Error";
$helpnode['error']['data'][] = array("This help item has not yet been written.  Sorry.",null);

/* MAIN MENU HELP */

$helpnode['index']['index']['title'] = "TDTrac Overview";
$helpnode['index']['index']['data'][] = array("TDTrac is a web based show budget and payroll hours tracker, built by a TD, for other TD's, freelance designers, and anyone else who finds it useful.",null);

/* MODULE: MAIL   VERSION: 3.0.0 */

$helpnode['mail']['inbox']['title'] = "Inbox View";
$helpnode['mail']['inbox']['data'][] = array("The Inbox view shows any system messages that have been sent to you.", null);
$helpnode['mail']['inbox']['data'][] = array('Clear Messages Link', 'Clears all current inbox messages.');

/* MODULE: TODO  VERSION: 3.0.0 */

$helpnode['todo']['add']['title'] = "Add To-Do Item";
$helpnode['todo']['add']['data'][] = array("Allows you to add a new to-do item", null);
$helpnode['todo']['add']['data'][] = array('Show', 'Show to associate item with.');
$helpnode['todo']['add']['data'][] = array('Priority', 'Priority of this item, used in sorting the list.');
$helpnode['todo']['add']['data'][] = array('Due Date', 'The date the task should be completed');
$helpnode['todo']['add']['data'][] = array('Assigned To', 'The user that should complete the task');
$helpnode['todo']['add']['data'][] = array('Description', 'A Description of the required task');

$helpnode['todo']['edit']['title'] = "Edit To-Do Item";
$helpnode['todo']['edit']['data'] = $helpnode['todo']['add']['data'];
$helpnode['todo']['edit']['data'][0] = array("Allows you to edit a to-do item", null);

$helpnode['todo']['view']['title'] = "To-Do List View";
$helpnode['todo']['view']['data'][] = array("Allows you to view todo lists; either by show, assigned user, or those items that are overdue.", null);

$helpnode['todo']['index']['title'] = "ToDo Lists";
$helpnode['todo']['index']['data'][] = array("Allows you to view todo lists; either by show, assigned user, or those items that are overdue.", null);

/* MODULE: HOURS   VERSION: 3.0.0 */

$helpnode['hours']['index']['title'] = "Payroll Tracking";
$helpnode['hours']['index']['data'][] = array('Track employee payroll records', null);
$helpnode['hours']['index']['data'][] = array('Unpaid View', 'View all outstanding hours');
$helpnode['hours']['index']['data'][] = array('Show / User View', 'View all hours on a calendar');

$helpnode['hours']['view']['title'] = "View Payroll";
$helpnode['hours']['view']['data'][] = array('Track employee payroll records', null);
$helpnode['hours']['view']['data'][] = array('Color: Dark Grey', 'No Hours recorded');
$helpnode['hours']['view']['data'][] = array('Color: Light Grey', 'Paid Hours recorded');
$helpnode['hours']['view']['data'][] = array('Color: Orange', 'Unpaid Hours recorded');

$helpnode['hours']['edit']['title'] = "Edit Hours Form";
$helpnode['hours']['edit']['data'][] = array("Allows you to edit existing payroll items", null);
$helpnode['hours']['edit']['data'][] = array("Employee", "The employee's name to be paid.");
$helpnode['hours']['edit']['data'][] = array("Show", "The associated show name for these hours worked");
$helpnode['hours']['edit']['data'][] = array("Date", "The date of the hours worked.");
$helpnode['hours']['edit']['data'][] = array("Worked", "The amount of time worked");
$helpnode['hours']['edit']['data'][] = array("Hours Paid Out", "Toggle whether the hours have been paid");

$helpnode['hours']['add']['title'] = "Add Hours Form";
$helpnode['hours']['add']['data'] = $helpnode['hours']['edit']['data'];
$helpnode['hours']['add']['data'][0] = array("Allows you to add new payroll items",null);

$helpnode['hours']['remind']['title'] = "Remind Employees to Submit Hours";
$helpnode['hours']['remind']['data'][] = array("Select the employees you wish to have recieve a server-generated e-mail asking them to log in and submit their hours before the specified due date for the pay period provided", null);

/* MODULE: BUDGET   VERSION: 3.0.0 */

$helpnode['budget']['index']['title'] = "Budget Tracking";
$helpnode['budget']['index']['data'][] = array("View, Add and Edit budget expenses on a per-show basis", null);

$helpnode['budget']['edit']['title'] = "Edit Budget Expense Form";
$helpnode['budget']['edit']['data'][] = array("Allows the editing of a budget item", null);
$helpnode['budget']['edit']['data'][] = array("Show", "Name of the show or job");
$helpnode['budget']['edit']['data'][] = array("Date", "Date of charge.");
$helpnode['budget']['edit']['data'][] = array("Vendor", "The name of the vendor or store");
$helpnode['budget']['edit']['data'][] = array("Category", "The name of a category for the charge");
$helpnode['budget']['edit']['data'][] = array("Description", "A description of the charge.");
$helpnode['budget']['edit']['data'][] = array("Price", "The amount of the charge, in dollars.");
$helpnode['budget']['edit']['data'][] = array("Tax", "The amount of the tax, if applicable.");
$helpnode['budget']['edit']['data'][] = array("Pending Payment", "Toggle on for charges that are approved and unpaid");
$helpnode['budget']['edit']['data'][] = array("Reimbursment", "Toggle for a charge needing reimbursed to someone");
$helpnode['budget']['edit']['data'][] = array("Owed To", "Employee that is owed reimbursment, if applicable");

$helpnode['budget']['add']['title'] = "Add Budget Expense Form";
$helpnode['budget']['add']['data'] = $helpnode['budget']['edit']['data'];
$helpnode['budget']['add']['data'][0] = array("Allows the adding a new budget item", null);

$helpnode['budget']['view']['title'] = "Budget Views";
$helpnode['budget']['view']['data'][] = array("View budget expenses for a show, category, or individual item", null);
$helpnode['budget']['view']['data'][] = array("Full show budgets can be sent to yourself via E-mail", null);

/* MODULE: SHOWS   VERSION: 3.0.0 */

$helpnode['shows']['index']['title'] = "Show Information";
$helpnode['shows']['index']['data'][] = array("Add, Edit or Remove Shows or Jobs", null);

$helpnode['shows']['add']['title'] = "Add Show Form";
$helpnode['shows']['add']['data'][] = array("Allows the addition of a new show or job name", null);
$helpnode['shows']['add']['data'][] = array("Show Name", "The Name of the show or job.");
$helpnode['shows']['add']['data'][] = array("Show Company", "The name of the associated company for this show or job");
$helpnode['shows']['add']['data'][] = array("Show Venue", "The venue or location associated with this show or job.");
$helpnode['shows']['add']['data'][] = array("Show Opening", "The opening / completion date for this show or job.");
$helpnode['shows']['add']['data'][] = array("Show Record Open", "Close shows do not show in Add/Edit forms.");

$helpnode['shows']['edit']['title'] = "Edit Show Form";
$helpnode['shows']['edit']['data'] = $helpnode['shows']['add']['data'];
$helpnode['shows']['edit']['data'][0] = array("Allows editing of current shows or jobs.", null);

/* MODULE: ADMIN    VERSION: 3.0.0 */

$helpnode['admin']['index']['title'] = "Administrative Tasks";
$helpnode['admin']['index']['data'][] = array("User and Group Managment, Permissions Management, and TDTracMail Config (if present)", null);

$helpnode['admin']['mail']['title'] = "TDTracMail Configure";
$helpnode['admin']['mail']['data'][] = array("TDTracMail Configuration, if installed. If these are empty, TDTracMail is probably not configured", null);
$helpnode['admin']['mail']['data'][] = array("Address", "The pseudo-user to send reciepts to");
$helpnode['admin']['mail']['data'][] = array("Subject Code", "The required subject to add reciept");

$helpnode['admin']['users']['title'] = "View Users";
$helpnode['admin']['users']['data'][] = array("Displays all system users, sorted by last name", null);
$helpnode['admin']['users']['data'][] = array("Active", "User can login");
$helpnode['admin']['users']['data'][] = array("Payroll", "User appears on payroll forms");
$helpnode['admin']['users']['data'][] = array("A/V/E Own", "User can only add and edit their own hours");
$helpnode['admin']['users']['data'][] = array("Notify", "User is notified when non-admin adds hours");

$helpnode['admin']['useredit']['title'] = "Edit User Form";
$helpnode['admin']['useredit']['data'][] = array("Allows editing of a user to the system.",null);
$helpnode['admin']['useredit']['data'][] = array("User Name", "The username, or login name for the user.");
$helpnode['admin']['useredit']['data'][] = array("Password", "An initial password for the user.");
$helpnode['admin']['useredit']['data'][] = array("Payrate", "The user's pay rate, by day or by hour");
$helpnode['admin']['useredit']['data'][] = array("First Name", "The user's first name.");
$helpnode['admin']['useredit']['data'][] = array("Last Name", "The user's last name.");
$helpnode['admin']['useredit']['data'][] = array("Phone", "The user's phone number");
$helpnode['admin']['useredit']['data'][] = array("E-Mail", "The user's e-mail address");
$helpnode['admin']['useredit']['data'][] = array("Group", "The user's permission group");

$helpnode['admin']['useradd']['title'] = "Add User Form";
$helpnode['admin']['useradd']['data'] = $helpnode['admin']['useredit']['data'];
$helpnode['admin']['useradd']['data'][0] = array("Allows adding of a user to the system.",null);

$helpnode['admin']['groups']['title'] = "Groups on TDTrac";
$helpnode['admin']['groups']['data'][] = array("View, Edit or Add to system groups", null);

$helpnode['admin']['permsedit']['title'] = "Permissions System";
$helpnode['admin']['permsedit']['data'][] = array("Allows editing and view of the permissions system. Permissions are group based throughout the site.", null);
$helpnode['admin']['permsedit']['data'][] = array("*show", "Add / Edit / View Shows and Jobs");
$helpnode['admin']['permsedit']['data'][] = array("*budget", "Add / Edit / View Budget Expenses");
$helpnode['admin']['permsedit']['data'][] = array("*hours", "Add / Edit / View Payroll Items<br />&nbsp;&nbsp;&nbsp;&nbsp;Note: set A/V/E to in user config to limit");
$helpnode['admin']['permsedit']['data'][] = array("*todo", "Add / Edit / View Todo Items<br />&nbsp;&nbsp;&nbsp;&nbsp;Note: user can always view their own todo items");

?>
