# Changelog
This project changes will be shown here.

## [unreleased]
### Fixed
- Issue 566 - Change Request Cancel button doesn't delete activity if it's been autosaved already

## [v2.4.2] - 2019-04-12
### Fixed
- Fix issue with duplicated emails in contacts.

## [v2.4.1] - 2019-04-09
### Fixed
- Archived passwords not observing access levels
- Saving contacts when there are duplicate email addresses transposes the email onto other contacts. See attached video.
- Emails from quote reminders are stuck in the queue.
### Changed
- LDAP config

## [v2.4.0] - 2019-03-27
### Changed
- Issue 477 - Asset list export & upload exported file to website
- Issue 510 - Include Average SR open length for 7 dayers report
- Issue 484 - Improve Password generation functionality
- Issue 502 - PO Status Report v6
- Issue 516 - Sales Quote reminder not creating PDF document
### Fixed
- Issue 498 - ContactAudit doesn't show contact support level
- Issue 491 - Improve the way that new requests are posted
- Issue 513 - 2nd Site Backup Validation Completed email formatting
- Issue 514 - 3CX Address Book Export to be fixed
- Issue 517 - Fix Hours background formatting on home page
### Added
- Issue 501 - Deleting contacts confirmation

## [v2.3.5] - 2019-03-22
### Fixed
- ProcessPendingLeavers not working with dates in the past

## [v2.3.4] - 2019-03-21
### Removed
- Suspend WebAnalytics

## [v2.3.3] - 2019-03-14
### Changed
- Remove “Please fax back…..” from generated PDF's and show, “Please return a signed copy to sales@cnc-ltd.co.uk” instead

## [v2.3.2] - 2019-03-14
### Fixed
- Awaiting Customer email subjects are incorrect

## [v2.3.1] - 2019-03-01
### Fixed
- Customer Review Meeting fails to generate files

## [v2.3.0] - 2019-02-28
### Added
- Issue 479 - Domain verification and Labtech computer customer assignment verification
- Issue 475 - Additional folders for default customer creation v2 & Bitlocker Key export
### Fixed
- Issue 495 - Date generated fields in renewal items get deleted when a comment is added and saved

## [v2.2.2] - 2019-02-27
### Fixed
- Show customer support contact

## [v2.2.1] - 2019-02-26
### Fixed
- Timezone Issue
- Goods In Issue
- Open for over 7 Days Issue

## [v2.2.0] - 2019-02-25
### Changed
- Issue 474 - Request Dashboards visual enhancements
- Issue 464 - Contact Validation Change
- Issue 448 - Allow Sales Requests to be editable
- Issue 459 - Time Request page auto refresh
- Issue 485 - Move Passwords from Sales to Technical menu
- Issue 430 - PO Status Report enhancements v5
- Issue 468 - Sales Renewal enhancements
### Fixed
- Issue 471 - Sales quote signature error validation
- Issue 458 - Customer Analysis Report SQL query
- Issue 482 - Starter / Leaver database fields
### Added
- Issue 450 - Sales Meeting Booking Request
- Issue 470 - Automated schedule to 'delete' pending user contacts
- Issue 446 - Add new CNC graphic to quick quotes
- Issue 467 - Leased line contract expiry notification
- Issue 483 - Add Password level descriptions
- Issue 480 - Current Projects shown on Sales Order page
- Issue 489 - Contract Extraction
- Issue 404 - Error Log Handling Improvements

## [v2.1.4] - 2019-02-20
### Fixed
- Fixed Invalid character in customer email

## [v2.1.3] - 2019-02-15
### Fixed
- Fixed Error when logging SRs for a delegate
- Issue 472 - PortalProcessRequests.php - documents that are being sent out

## [v2.1.2] - 2019-02-15
### Changed
- Change Customer Review Meeting sending email address

## [v2.1.1] - 2019-02-12
### Removed
- Remove debug log shown when editing an activity and showing a form error.

## [v2.1.0]
### Changed
- Issue 137 - CNC029 - Password archiving & security
- Issue 436 - Changing contracts in SR, permissions required
### Added
- Issue 442 - Customer Starter & Leaver questions

## [v2.0.7] - 2019-02-08 
### Fixed 
- Fix issue with counting open activities
 
## [v2.0.6] - 2019-02-08
### Fixed
- Unnecessary email sending on SR updates

## [v2.0.5] - 2019-02-04
### Fixed
- Fix issue where OthersEmails flags where getting ignored

## [v2.0.4] - 2019-02-04
### Fixed
- Fix Issue with logged requests showing html characters in the reason

## [v2.0.3] - 2019-02-04
### Fixed
- Fixed Issues with to be logged requests not picking up the body of the email.
- Fixed issue after logging a new request being sent to a blank page.

## [v2.0.2] - 2019-01-31
### Fixed 
- Fixed issue with sending emails to customers for Service Requests

## [v2.0.1]
### Fixed
- Can't create new users due to the Primary Main Contact being required

## [v2.0.0] - 2019-01-31
### Changed
- Issue 215 - Contact Management within CNCAPPS
- Issue 358 - Review automatic email creation process so that it's compatible with the new contacts in 215
- Issue 408 - Contact Validation Improvements
### Fixed
- Issue 352 - CNCWEB contacts bugs
### Added
- Issue 347 - Customer Review Management documents
- Issue 345 - Creation of Primary Main Contact 

## [v1.16.1] - 2019-01-29
### Fixed
- Search SRs take you to the Sales Order, not Service Request

## [v1.16.0] - 2019-01-24
### Added
- Issue 429 - SD Manager Dashboard show P1 sections
- Issue 410 - 7 dayers on dashboard
- Issue 409 - Time Request Dashboard
- Issue 428 - Allow Logging directly into the Sales queue
- Issue 411 - Non referred customers with no contracts report
- Issue 435 - Additional folders for default customer creation
### Changed
- Issue 413 - Default home page graphs to your team
- Issue 421 - Increase Questionnaire field length
- Issue 422 - Decimal places for fix times in System Header
- Issue 423 - Upcoming Customer Review Meeting
- Issue 414 - Contact table change defaults
- Issue 390 - Project List Enhancements
- Issue 438 - Edit Sales Invoice email to include reference to portal
- Issue 418 - SR Completion Contract Changing
- Issue 437 - Split Upcoming Visits query into own function
- Issue 439 - SalesRequestDashboard SR link in new tab
### Fixed
- Issue 420 - Email missing header information
- Issue 378 - Creating item defaults to first site
- Issue 419 - Customer Review Meeting Frequency doesn't show in PDF export for Every 2 Months
- Issue 415 - First Time Fix clarification
- Issue 432 - Contact changes in SR don't update phone details

## [v1.15.2] - 2019-01-11
### Fixed
- PoStatusReport doesn't show the right amount of results.
- PoStatusReport show visits when it shouldn't.

## [v1.15.1] - 2019-01-09 
### Fixed
- Issue 417 - Despatching RenBroadband items incorrect outstanding quantity
- Fix problem with TopUp value in customer page

## [v1.15.0] - 2018-12-27
### Fixed
- Issue 376 - Contact notes being copied to other contacts
- Issue 359 - Management Review Summary shows blank To: field
- Issue 372 - Email display header missing
- Issue 360 - SD Dashboard Assignment display issue
- Issue 380 - Typo in Reports Menu
- Issue 377 - Sales Requests status lost
- Issue 393 - Clicking Start Work with new SR puts it into the wrong queue
- Issue 389 - Prevent links for contacts that have no support flag enabled
- Issue 405 - Creating SR from To Be Logged sometimes loses text
### Changed 
- Issue 365 - Performance table formatting improvements
- Issue 391 - Change MailQueueAlert.php email destinations
- Issue 388 - Contact Title Database field length increase
- Issue 366 - Automatically raised SRs set to no Initial time
- Issue 377 - Sales Requests status lost
- Issue 399 - Staff Appraisal formatting improvements
- Issue 398 - Improve SR manager dashboard view
- Issue 407 - Tweak SR manager Dashboard view
### Added
- Issue 392 - Customer Meeting Review Frequency Change
- Issue 364 - Improve Items Not Received v3
- Issue 382 - Upcoming visits dashboard on the home page
- Issue 346 - Automate Asset List Export
- Issue 402 - Show recent SRs for contact
- Issue 369 - Allow a contact to be special attention
- Issue 363 - Contact export to CSV for 3CX phone system importing
### Removed
- Issue 403 - Remove extra | in menu

## [v1.14.5] - 2018-12-11
### Changed
- Show employee's start date in DD-MM-YYYY format

## [v1.14.4] - 2018-12-10
### Changed
- Change Proposed salary and bonus to be number input to prevent users from typing non numeric characters

## [v1.14.3] - 2018-12-06
### Fixed
- Staff details encrypted fields are not being saved / displayed!

## [v1.14.2] - 2018-12-05
### Fixed
- Project management role is no longer present in the User edit page

## [v1.14.1] - 2018-12-04
### Fixed
- Staff Appraisal start date field value loads with the wrong format

## [V1.14.0]- 2018-12-03
### Added
- Issue 373 - Staff Appraisal Process

## [v1.13.2] - 2018-11-23
### Fixed
- Contract expiry dates incorrect
- Remove debugging information

## [v1.13.1] - 2018-11-20
### Changed
- Allow more than 50 characters in project updates
### Fixed
- Fix issue with banners not showing when project end date was not set
- Fix issue with banners showing incorrect projects

## [v1.13.0] - 2018-11-19
### Added
- Issue 119 - CNC012 - Option to display a custom logo to top of questionnaire page
- Issue 211 - CNC040 - Direct Debit Specification
### Changed
- Issue 341 - Project lifecycle and management
- Issue 321 - Permissions to edit Initial activity type

## [v1.12.0] - 2018-09-27
### Changed
- Issue 353 - Sales request wording change
- Issue 356 - Remove the word Contracts from Call Activity page
- Issue 357 - Change Request reply, remove Further Details Required
- Issue 348 - Show priority on Existing SRs page
- Issue 354 - Improve loading speed of home page
### Fixed
- Issue 351 - &nbsp in SR from Sales order

## [v1.11.1] - 2018-09-17
### Fixed
- Fix issue with Visit Confirmation emails.

## [v1.11.0] - 2018-09-11
### Fixed
- Issue 315 - Monitored SR formatting issues.
- Issue 328 - Spell checking within fields
- Issue 337 - FOC activities email URL text error
- Issue 340 - Hidden from customer flag showed as cleared under certain conditions.
### Added
- Issue 317 - Customer 3rd party contact information
- Issue 318 - Create Change Request Dashboard
- Issue 334 - Add total to Service Requests Open For More Than 7 Days on screen
- Issue 319 - Implement Sales Request Approval Process
- Issue 339 - Team Lead Dashboard view filtering by teams
- Issue 325 - Calculate and display contract expiry date
- Issue 324 - Official Order Number for Contracts
### Changed
- Issue 304 - Enhance First Time Fix Reporting
- Issue 320 - Further Increase length of mail queue recipient database field
- Issue 322 - Change Renewal Report Heading Expires to Invoiced Until
- Issue 327 - SQL crash searching for customer with apostrophe in the name
- Issue 326 - Enhance Items Not Received Process
- Issue 331 - Renewal item creation warning
- Issue 273 - Allow for contracts & T&Cs to be manually sent to a customer.
- Issue 335 - Improve visit confirmation email addressing
- Issue 338 - Change date picker on additional pages
- Issue 268 - Customer Review Meeting formatting improvements

## [v1.10.2] - 2018-08-20
### Fixed
- Issue 333 - Prepay Top Up value lost when saving customer

## [v1.10.1] - 2018-08-03
### Fixed
- Fixed issue with First Time Fixes totals

## [v1.10.0] - 2018-08-03
### Added
- Issue 259 - Contact changes audit log
- Issue 309 - 2S Replication Check Exclusion feature
### Fixed
- Issue 308 - Bugs from release 1.9
### Changed
- Issue 314 - Increase length of mail queue recipient database field
- Issue 313 - Monthly Customer Profitability Report email group update
- Issue 307 - Dashboard figures to load asynchronously, daily fixed & first time fixes

## [v1.9.2] - 2018-07-27
### Fixed
- Fixed problem with XX engineers in XX hours query
- jQuery DatePicker to show Monday as first day of the week
- Fixed issue with conflicting jQuery versions
### Added
- Added Monthly First Time Fix figures
- Added Weekly Fixed and Reopened figures
- Create script to send emails from Labtech database

## [v1.9.1] - 2018-07-26
### Fixed
- 2nd Site Backup out of date calculations
- Fix and improve holiday time logging calculations
- Fix problem with item description quote page

## [v1.9.0] - 2018-07-25
### Changed
- Issue 285 - Change length limit to customer item 'Item Text' field
- Issue 267 - Auto Service Completion for Starters & Leavers
- Issue 277 - Move reports to Technical menu for SD Managers
- Issue 272 - Contract Analysis Report error handling
- Issue 270 - Technical Change Request to default to Awaiting CNC
- Issue 278 - Consider a negative 2nd Site date check to be a failure
- Issue 281 - Add headings for the contract page popup.
- Issue 293 - Team Performance Calculations Update To Include Previous Month
- Issue 291 - Technical Change Request formatting change
- Issue 274 - Set system updated SRs to status Awaiting CNC
- Issue 294 - User performance graph tweaks
- Issue 284 - Investigate length of fields allowed in quotes
- Issue 296 - Change Open SR Report so non main contacts only see their SRs
- Issue 249 - 2nd Site Replication check process
- Issue 302 - 7 Day report to include awaiting status 
- Issue 298 - Single Fixed Explanation within a Service Request
- Issue 232 - Improve Uploading Files to Service Request Activities
### Fixed
- Issue 275 - Permissions error for PortalDocument
- Issue 271 - CustomerAnalysis Report error handling
- Issue 279 - Contracts not populating on Search page
- Issue 300 - Error adding passwords from Sales Password page
- Issue 276 - Occasional formatting issues for Monitored SR emails 
### Added
- Issue 280 - Restore ability to create customer documentation folder
- Issue 121 - CNC014 - Add ability to log a new SR directly into a team
- Issue 297 - First Time Fix figures
- Issue 269 - Live fixed SR target & total.
- Issue 266 - Helpdesk Manager Dashboard View
### Removed
- Issue 282 - Remove DNS records from domain name
- Issue 283 - Remove Documentation link from Quotation Page

## [v1.8.5]
### Fixed
- Issue 299 - Show link to create customer folder if it doesn't exist

## [v1.8.4] - 2018-07-04
### Fixed
- Issue 292 - Skip Sales Order on Activity.php shows debug information

## [v1.8.3] - 2018-06-29
### Fixed
- Issue 290 - T&M processing error for multiple activity

## [v1.8.2] - 2018-06-28
### Fixed
- Issue 289 - Fix issues 
 
## [v1.8.1] - 2018-06-26
### Fixed
- Issue 287 - Fix problem with sales orders

## [v1.8.0] - 2018-06-26 
### Added
- Issue 195 - Graph to show user productivity
- Issue 264 - Add option for Monthly in customer review meeting frequency
### Changed
- Issue 236 - Enhance Sales By Customer report 
- Issue 239 - New navigation buttons in the activity pages
- Issue 243 - Amend Contract Document details
### Removed
- Issue 260 - Remove Spend/Category from the Reports Menu
## Fixed
- Issue 262 - Problems With Customer CRM losing information
- Issue 261 - Find out why activities end up with caa_status of O and resolve

## [v1.7.5] - 2018-06-06
### Fixed
- Issue 265 - Daily Report sending some customer data to wrong people

## [v1.7.3] - 2018-05-30
### Fixed
- Issue 258 - CustomerCRM searching and saving issues - hotfix required

## [v1.7.2] - 2018-05-30
### Fixed
- Issue 257 - When running the Customer Review Meeting process it no longer creates the Renewal Report PDF. This can go 
straight into live cncapps once fixed

## [v1.7.1] - 2018-05-29
### Fixed
- Issue 257 - Bugs from release 1.7.0

## [v1.7.0] - 2018-05-25
### Added
- Issue 246 - Add descriptions to Report pages
- Issue 237 - ServiceRequestReport export tweak
- Issue 244 - Show ItemsNotYetReceivedEmail as a menu link
- Issue 245 - Outstanding SRs email to display on screen as well
### Changed
- Issue 233 - Amend Create Renewal Sales Order email destination
- Issue 238 - SR Response and number amendment
- Issue 240 - Standardise date displays in forms
- Issue 252 - Ability to remove 'entire SR hidden from customer'
### Fixed
- Issue 229 - Purchase Authorisation Supplier pop out
- Issue 253 - Error uploading a file to customer page, over 8MB warning
### Removed
- Issue 235 - Remove Awaiting Completion from menu, in the Technical heading
- Issue 241 - Remove Customer Review Meeting from the Accounts menu

## [v1.6.3] - 2018-05-23
### Fixed
- Issue 250 - Standardtext.php missing

## [v1.6.2] - 2018-05-21
### Fixed
- Issue 234 - Permissions bug for sales order address page

## [v1.6.1] - 2018-05-18
### Fixed
- Issue 231 - Bugs from release 1.6.0

## [v1.6.0] - 2018-05-16
### Added 
- Issue 189 - Add version number to menu
- Issue 183 - Add the ability to add time to a service request when in the managers queue
- Issue 186 - Time limit request bug with IMT team
- Issue 184 - Request 038 - Daily Open SR Report for customers
- Issue 193 - Monitor SR feature to receive updates automatically
- Issue 201 - Create items not yet received process
- Issue 202 - UK National Holiday notification for Non UK customers
- Issue 221 - Fixed pending closure on Service Request page
- Issue 219 - Implement CNCAPPS security
- Issue 217 - 2nd Site Validation test run
- Issue 222 - Amend initial email content based on 24x7 support process

### Changed
- Issue 176 - Change background colours on service request page
- Issue 194 - Old tables in the database that aren't used?
- Issue 2-197 - Add Awaiting Completed to technical Menu
- Issue 200 - Amend Unprinted Purchase Orders email
- Issue 203 - Assigning a service request to qsysytem user does not record who assigned it
- Issue 112 - CNC005
- Issue 208 - Remove blank space from display of phone numbers
- Issue 212 - Remove old items from menu
- Issue 216 - Change list of activity types that are visible in SRs
- Issue 224 - Change order of engineer list in drop down on Current Service Requests
- Issue 225 - Record absence, force absent time to overwrite
- Issue 169 - CNC034 - Review Meeting Automation

### Fixed
- Issue 161 - Customer Review Meeting Total SRs potential adding up issue
- Issue 179 - Uploading a document in an activity has the same affect as clicking the 'update' button
- Issue 187 - Special Attention customers are not showing as a red background
- Issue 181 - Prevent a search all!
- Issue 186 - Time limit request bug with IMT team
- Issue 191 - Amend the value of the hours spent on critical SR activity email
- Issue 192 - Passwords page crashes when incomplete customer name is used
- Issue 206 - Goods In supplier tab out does not work
- Issue 207 - Export Prepay Service Request calendar incorrectly linked
- Issue 209 - Current Documentation Folder & Open Folder does not open when using Chrome
- Issue 218 - 2nd Site Backup Calculations

### Removed
- Issue 223 - Remove Comment link from Activity Page

## [v1.5.2] - 2018-04-11
### Changed
- Issue 210 - Request time pop up box text change

## [v1.5.1] - 2018-03-27
### Added
- Issue 188 - New P5 SR for SSL hardcoded change

##[v1.5.0]- 2018-03-20
### Added
- Issue 185 - Make change so that all pdf's generated are only PRINTABLE

## [v1.4.0] - 2018-03-08
### Fixed
- Issue 175 - CNC037 - tidy up imported emails
- Issue 155 - CNC034 - Profitability Report for PrePay counts as negative

## [v1.3.0] - 2018-03-07
### Added
- Issue 174 - CNC036 - Record backup success as percentage

### Changed
- Issue 171 - CNC035 - Display Calendar link on all activities

### Fixed
- Issue 172 - CNC036 - Sales Order link not shown when editing an activity
- Issue 173 - Remove Type field 'required' status from the Activity edit page
- Issue 178 - When updating a activity the activity type limit checks don't work anymore
- Issue 150 - The URI gets too large when working with the timer on and typing notes
- Issue 167 - Fix problem with days not showing in allocation time window

## [v1.2.1]
### Fixed
- Fix issue with time allocated emails not getting sent

## [v1.2.0]
### Added
- Issue 167 - CNC033 - Hard limits

## [v1.1.0]
### Added
- Issue 144 - CNC031 - Contract and numbers report
- Issue 122 - CNC015 - Check activity length and flag if remote support and under 5 minutes
- Issue 131 - CNC023 - When editing customer contacts make the main contact appear in red text
- Issue 130 - CNC022 - Include Internal Notes that are saved with SSL auto renewals in the service request that’s created.

## Changed
- Issue 160 - Increase size of field for customer name
- Issue 154 - Change the text in a calendar subject item
- Issue 152 - CNC033 - Add decimal places to team performance fix hours

### Fixed
- Issue 142 - Pre-pay activity over £100 - where is the email address set?
- Issue 124 - CNC016 - SR that doesn't have a site set it will crash with an SQL error
- Issue 159 - Email Subject Change for: SR 434149 has been updated by another user
- Issue 158 - Certain characters are displaying incorrectly in emails

## [v1.0.5] - 2017-01-30
### Fixed
- Issue 165 - T&M Authorisation Bug

## [v1.0.4] - 2017-01-29
### Fixed
- Issue 162 - Typo in subject line of email - Critial SR Activity For

## [v1.0.3] - 2017-01-17
### Added
- Issue 120 - CNC013 - Allow searching for activities based on time spent

### Fixed
- Issue 164 - Create a system where a log of the scheduled tasks runs are located
- Issue 151 - T&M Authorisation Process Error

## [v1.0.2] - 2017-12-21
### Added
- Issue 149 - Create a Daily Report for P5 with sales orders to be sent to nosales@

### Changed
- Issue 125 - CNC017 - New SR from SO to Imt queue
- Issue 129 - CNC021 - Add who fixed the SR to escalated email

### Fixed
- Issue 56  - CNC004 

## [v1.0.1] - 2017-12-14
### Added
- Copy Username in password List.

## [v1.0.0] - 2017-07-15
### Added
- Issue 115 - CNC008 - Open SR Activities report for managers
- Issue 126 - CNC018 - upload a file when in an activity or on the fixed screen
- Issue 116 - CNC009 - Copy to clipboard button for the passwords
- Issue 114 - CNC007 - On home page no link to projects
- Issue 54 - CNC002 - Email link within activity add subject
 
### Changed
- Issue 143 - CNC030 - Customer Analysis Report row layout
- Issue 117 - CNC010 - Customer or contact notes should be visible when logging the SR
- Issue 133 - CNC025 - Within customer contacts, only allow 1 statements contact

### Fixed
- Issue 136 - CNC028 - Change Request emails arriving incorrectly
- Issue 139 - Issue with despatching
- Issue 138 - Selecting Site on Add Customer Item 
- Issue 110 - The renewal report no longer group together the heading like Internet Service, PC Application Software
- Issue 108 - Purchase Invoice Authorisation VAT calculation
- Issue 109 - Suppliers Contact lookup 
- Issue 60 - The Date Icon do not work on the Sales Order Page in IE
- Issue 59 - Contracts menu shows errors
- Issue 58 - Invoices menu show errors, some file missing
- Issue 57 - Warnings and errors show after login in
- Issue 55 - CNC003 - Time breaching emails from HD not sending/working
- Issue 53 - CNC001 - Remove activity number from search screen
- Issue 52 - PurchaseOrder page the select date button doesn't work
- Issue 51 - Customer Page the notes don't show right away
- Issue 50 - SalesOrder ticking the box to toggle all related checkboxes won't toggle them
- Issue 49 - SalesOrder Page shows errors
- Issue 48 - F5 Toggle Doesn't Work anymore