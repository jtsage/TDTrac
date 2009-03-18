<?php

$helpnode['error'][] = "<h2>Error</h2><p>This help item has not yet been written.  Sorry.</p>\n";

$helpnode['home'][] = "<h2>TDTrac Overview</h2>\n";
$helpnode['home'][] = "<p>TDTrac is a web based show budget and payroll hours tracker, built by a TD, for other TD\'s, freelance designers, and anyone else who finds it useful. TDTrac is completely free, released as open source.  We also offer hosting plans for a nominal fee (no domain required!) so you don't need to pay for webspace elsewhere.\n";
$helpnode['home'][] = "<br /><br />TDTrac Features:\n";
$helpnode['home'][] = "<ul><li>Track as many show budgets as you wish</li>\n";
$helpnode['home'][] = "<li>Budgets can be organized by vendor, category, or even amount spent</li>\n";
$helpnode['home'][] = "<li>Track payment pending budget items - things ordered but not cleared through any account or credit card you use</li>\n";
$helpnode['home'][] = "<li>Track reimbursable budget items through the reciept of payment from your reimburser</li>\n";
$helpnode['home'][] = "<li>Track as many active or inactive employees as you wish</li>\n";
$helpnode['home'][] = "<li>Track which employee hours have been paid and which are outstanding</li>\n";
$helpnode['home'][] = "<li>Configurable hourly or daily pay rate for budgeting purposes</li>\n";
$helpnode['home'][] = "<li>Configurable hourly or daily pay methods for accurate work force accounting</li>\n";
$helpnode['home'][] = "<li>Allow your employees to add thier own hours, while being notified on your next login</li>\n";
$helpnode['home'][] = "<li>Optionally allow your employees to add budget expenses, or even view the current budget</li></ul></p>\n";
$helpnode['home'][] = "<h2>Menu Items</h2>\n";
$helpnode['home'][] = "<ul><li>Payroll Tracking<ul>\n";
$helpnode['home'][] = "<li>Add Hours Worked - Add Hours or Days worked by an employee.</li>\n";
$helpnode['home'][] = "<li>View Hours Worked - View Hours or Days worked by an employee or employees in a date range.</li>\n";
$helpnode['home'][] = "<li>View Hours Worked (unpaid) - View all Hours or Days worked by employees that have not yet been paid.</li></ul></li>\n";
$helpnode['home'][] = "<li>Budget Tracking<ul>\n";
$helpnode['home'][] = "<li>Add Budget Expense</li>\n";
$helpnode['home'][] = "<li>View Budgets - View budgets on a per show basis, including labor budget</li>\n";
$helpnode['home'][] = "<li>View Budgets (payment pending items only, all shows) - View payment pending budget items</li>\n";
$helpnode['home'][] = "<li>View Budgets (reimbursment items only, all shows) - View all reimbursment budget items, paid or unpaid</li>\n";
$helpnode['home'][] = "<li>View Budgets (reimbursment recieved items only, all shows) - View reimbursed, paid budget items</li>\n";
$helpnode['home'][] = "<li>View Budgets (reimbursment not recieved items only, all shows) - View unreimbursed, still waiting budget items</li></ul></li>\n";
$helpnode['home'][] = "<li>Show Information<ul>\n";
$helpnode['home'][] = "<li>Add Show - Add a show</li>\n";
$helpnode['home'][] = "<li>View Shows - View shows available for use</li></ul></li>\n";
$helpnode['home'][] = "<li>User Managment<ul>\n";
$helpnode['home'][] = "<li>Add User - Add a user to the system</li>\n";
$helpnode['home'][] = "<li>View Users - View and edit system users</li>\n";
$helpnode['home'][] = "<li>Edit Permissions - Edit group based permissions</li>\n";
$helpnode['home'][] = "<li>View Permissions - View current group based permissions</li>\n";
$helpnode['home'][] = "<li>Add / Edit Groups - Add, Edit, or rename groups (warning: this is slightly unsupported right now)</li></ul></li>\n";

$helpnode['msg-read'][] = "<h2>Inbox View</h2>\n";
$helpnode['msg-read'][] = "<p>The Inbox view shows any system messages that have been sent to you.<ul>\n";
$helpnode['msg-read'][] = "<li>Clear Messages Link<ul><li>Clears all current inbox messages.  There is no 'mark as read', read messages are deleted</li></ul></li>\n";
$helpnode['msg-read'][] = "<li>Per Message Delete Link<ul><li>Removes that message from inbox.  There is no 'mark as read', read messages are deleted</li></ul></li></ul></p>\n";

$helpnode['msg-view'][] = "<h2>Outbox View</h2>\n";
$helpnode['msg-view'][] = "<p>The Inbox view shows any system messages that you have sent, but have not been removed by the recipient.<ul>\n";
$helpnode['msg-view'][] = "<li>Per Message Nuke Link<ul><li>Removes that message from outbox.  This is an admin only function. There is no 'mark as read', read messages are deleted</li></ul></li></ul></p>\n";

$helpnode['add-hours'][] = "<h2>Add Hours Form</h2>\n";
$helpnode['add-hours'][] = "<p><ul><li>Employee<ul><li>The employee's name who you wish to add hours for.  When logged in as an employee, only that login name will show here</li></ul></li>\n";
$helpnode['add-hours'][] = "<li>Show<ul><li>The associated show name for these hours worked.</li></ul></li>\n";
$helpnode['add-hours'][] = "<li>Date<ul><li>The date of the hours worked.  The 'today' link will fill in the current day.  The 'cal' link presents a javascript popup calendar.</li></ul></li>\n";
$helpnode['add-hours'][] = "<li>Hours Worked<ul><li>Number of hours worked.  In day rate mode, number of days worked (title will update accordingly</li></ul></li></ul></p>\n";

$helpnode['view-hours'][] = "<h2>View Hours Form</h2>\n";
$helpnode['view-hours'][] = "<p><strong>View By Employee</strong> - Show hours worked by the specified employee in the date range.<ul><li>Employee<ul><li>The employee's name who you wish to view hours for. Only active employees are shown.  When logged in as an employee, only that login name will show here</li></ul></li>\n";
$helpnode['view-hours'][] = "<li>Start Date<ul><li>The start date of the hours worked, included in the set.  The 'cal' link presents a javascript popup calendar.</li></ul></li>\n";
$helpnode['view-hours'][] = "<li>End Date<ul><li>The end date of the hours worked, included in the set.  The 'today' link will fill in the current day.  The 'cal' link presents a javascript popup calendar.</li></ul></li></ul></p>\n";
$helpnode['view-hours'][] = "<p><strong>View By Date</strong> - Show all employees that worked during the specified dates<ul>\n";
$helpnode['view-hours'][] = "<li>Start Date<ul><li>The start date of the hours worked, included in the set.  The 'cal' link presents a javascript popup calendar.</li></ul></li>\n";
$helpnode['view-hours'][] = "<li>End Date<ul><li>The end date of the hours worked, included in the set.  The 'today' link will fill in the current day.  The 'cal' link presents a javascript popup calendar.</li></ul></li></ul></p>\n";
$helpnode['view-hours'][] = "<p><strong>View All Un-Paid Hours</strong> - Show all hours that are pending payment to employees.</p>\n";
$helpnode['view-hours'][] = "<h2>View Hours Report</h2>\n";
$helpnode['view-hours'][] = "<p>Shows hours as per above criteria.  Data shown is the date worked, hours worked that day, the show the hours are associated with, and whether the hours have been paid to the employee yet.\n";
$helpnode['view-hours'][] = "<ul><li>Edit Link<ul><li>Edit the hours entry</li></ul></li>\n";
$helpnode['view-hours'][] = "<li>Delete Link<ul><li>Delete the hours entry (with confirmation)</li></ul></li>\n";
$helpnode['view-hours'][] = "<li>E-Mail to self Link<ul><li>E-Mail a copy of this information the e-mail address listed in your user profile</li></ul></li></ul></p>\n";

$helpnode['edit-hours'][] = "<h2>Edit Hours Form</h2>\n";
$helpnode['edit-hours'][] = "<p><ul><li>Employee<ul><li>The employee's name who you wish to edit hours for.  When editting, this cannot be changed.</li></ul></li>\n";
$helpnode['edit-hours'][] = "<li>Show<ul><li>The associated show name for these hours worked.</li></ul></li>\n";
$helpnode['edit-hours'][] = "<li>Date<ul><li>The date of the hours worked.  The 'today' link will fill in the current day.  The 'cal' link presents a javascript popup calendar.</li></ul></li>\n";
$helpnode['edit-hours'][] = "<li>Hours Worked<ul><li>Number of hours worked.  In day rate mode, number of days worked (title will update accordingly</li></ul></li>\n";
$helpnode['edit-hours'][] = "<li>Hours Paid Out<ul><li>Toggle whether the hours have been paid to the employee.</li></ul></li></ul></p>\n";





?>
