# Changelog
This project changes will be shown here.

## [v4.5.4] - 2020-09-02
### Added
- Add the ability to add items to Credit Notes


## [v4.5.3] - 2020-09-01
### Fixed
- Fixed issue with totals for recurring table showing wrong values in Sales order page

## [v4.5.2] - 2020-08-28
### Fixed
- Fixed issue with updating lines
- Fix issue with O365 Mailbox report not showing correctly in Renewal Report

## [v4.5.1] - 2020-08-28
### Fixed
- Fixed issue with user performance data not considering cncLoggedHours

## [v4.5.0] - 2020-08-28
### Added
- Issue 999 - Sales Order insert in place feature
### Changed
- Issue 997 - Improve Edit GUI to make it more intuitive
- Issue 990 - Current Service Request menu link
- Issue 1000 - Link manual O365 Mailbox Report to Software Subscription
- Issue 987 - Show CNC logged time in the user graphs

## [v4.4.2] - 2020-08-27
### Fixed
- Fix issue with inserting after reordering the lines

## [v4.4.1] - 2020-08-26
### Added
- Show the Part Number in the edit item page
### Changed
- Changed the way lines are dragged in sales orders to allow for copy/select line text
- Changed the size of the trash icon in sales order lines
### Fixed
- Fixed issue with Renewals Sales orders not assigning the recurring flag correctly
- Fixed issue with deleting lines in Sales Orders
- Fixed issue when adding an itemtype

## [v4.4.0] - 2020-08-25
### Added
- Issue 961 - Team Performance at the start of the month
- Issue 971 - Ability to allocate Sales Requests to people
- Issue 901 - Webroot API for Billing and Reporting
- Issue 521 - Split up renewal items from quotes
- Issue 965 - StreamOne missing license email notification addition
### Changed
- Issue 964 - Make Office 365 Backup unit numbers field read only because it's linked to an API
- Issue 899 - Link dependant Sales Items
- Issue 979 - Purchase Order Date Formatting
- Issue 974 - New Method to edit Items
- Issue 988 - Rename 7 Dayers
- Issue 994 - "Please reply above the line" text in SR emails to customers
### Fixed
- Issue 968 - Clear Special Attention Status Once Expired
- Issue 970 - StreamOne Page Permissions

## [v4.3.5] - 2020-08-18
### Fixed
- When Despatching, it can change the order of items.

## [v4.3.4] - 2020-08-10
### Fixed
- Fixed sale price calculation from UpdatePriceItemFromStreamOne

## [v4.3.3] - 2020-08-04
### Fixed
- Fixed Issues with StreamOne addOns New licenses
- Fixed Issues with Customer Page not saving StreamOne email

## [v4.3.2] - 2020-08-04
### Fixed
- Fixed Issues with StreamOne New licenses

## [v4.3.1] - 2020-08-03
### Fixed
- Fixed Issues with StreamOne licenses

## [v4.3.0] - 2020-07-31
### Added
- Issue 951 - Expenses Breakdown Year to Date
- Issue 861 - Scheduled requests to have option of being linked to a Sales Order
- Issue 890 - Monitor Sales Order to get notifications when it's signed.
### Changed
- Issue 938 - Increase number of favourites in menu
- Issue 950 - Include team in time allocation
- Issue 939 - Sortable SR Scheduler Columns
- Issue 945 - Move Invoices menu to Sales
- Issue 947 - Creating new customers, set SLA as required fields
- Issue 943 - Remove drives under 1 GB from Customer Review Meeting Agenda
- Issue 940 - Include Signable instructions in quote email body
### Fixed
- Issue 933 - User Time Logs sometimes showing as holiday
- Fix issue with pending leavers process not using the new "active" field for contacts

## [v4.2.7] - 2020-07-29
### Fixed
- Fixed SpecialAttentionCustomersReport.php not loading

## [v4.2.6] - 2020-07-27
### Fixed
- Overtime flag incorrectly being carried forward.

## [v4.2.5] - 2020-07-17
### Fixed
- Fixed issue with contracts not showing

## [v4.2.4] - 2020-07-16
### Fixed
- Fixed Office 365 export issues
- Fixed logout text showing in side bar when it is collapsed
- Fixed collapse button showing the wrong when the side bar starts as collapsed

## [v4.2.3] - 2020-07-15
### Changed
- Split up SD Monitor into different bits and pieces so that they can run independently.
### Fixed
- Fixed issue with despatching some renewal items.
- LeasedLinesContractExpiryNotification doesn't generate initial activity for the SR
- When a change request is raised, set the end time to 4 minutes after the start time 

## [v4.2.2] - 2020-07-10
### Fixed
- Fixed issue with time requests missing information

## [v4.2.1] - 2020-07-09
### Fixed
- Fixed email link not having subject
- Fixed issue that prevented from editing Sales orders lines
- Fixed ExpenseDashboard Permissions
- Fixed issue with Double overtime submissions
### Changed
- When an activity updates its start/end time, if it had any overtime approved, now it gets reset.
- Edit link when displaying activities now shows a tooltip if it's not enabled.

## [v4.2.0] - 2020-07-08
### Added
- Issue 903 - My Settings Page
- Issue 924 - Add Fix SLA to customer page
- Issue 921 - Record the source of a SR, phone, email or portal
- Issue 895 - Leased line contract expiry notification
- Issue 539 - Office 365 license API with Techdata

### Changed
- Issue 818 - Confirm Report Logic / calculations
- Issue 925 - Improve Double Quote handling in passwords fields
- Issue 615 - Contacts have Active flag
### Fixed
- Issue 927 - Imported customers not assigning default delivery address
- Issue 904 - Renewal Report Unit numbers not matching
- Issue 892 - Lead Status Layout Change
- Issue 822 - Combining SRs for Sales Order ordering issue
- Issue 930 - Too many email customer icons on Activity Page

## [v4.1.9] - 2020-06-30
### Fixed
- Office365LicensesExport.php not correctly counting number of licensed mailboxes.

## [v4.1.8] - 2020-06-26
### Fixed
- Fix issue in activityCreate1 when the customer name has an apostrophe

## [v4.1.7] - 2020-06-15
### Added
- Fix visit confirmation ICS file

## [v4.1.6] - 2020-06-12
### Added
- Added a script that allows importing customers from a csv file.
- Added cncdev2 environment

## [v4.1.5] - 2020-06-11
### Changed
- Changed office version text

## [v4.1.4] - 2020-06-10
### Changed
- Show contact phone number in activity
- Enable Update button for activity type 51

## [v4.1.3] - 2020-06-09
### Fixed
- Fixed issue with questionnaireReport
- Fixed issue with Office365Licenses and OSSupportDates not getting the menu open
### Changed
- Prevent any user that is NOT SD Manager to click "update" button while editing activity
- Changed API Stats query to exclude hidden from customer of the Reopened figures

## [v4.1.2] - 2020-06-03
### Fixed
- SRScheduler doesn't show SR's
- Current Activity report doesn't reload automatically

## [v4.1.1] - 2020-05-29
### Fixed
- Contact Export blank page on type = customers
- Search Activity page doesn't show the contracts correctly after selecting customer
 
## [v4.1.0] - 2020-05-28
### Added
- Issue 865 - What3words integration
- Issue 850 - Include Sharepoint & Teams Storage in Office 365 Mailbox Export
- Issue 820 - Alert Main Primary Contact when O365 mailboxes size are at the limit
### Changed
- Issue 840 - PO Status v10
- Issue 686 - Meeting agendas change criteria for flag for review SRs
- Issue 882 - SR History Popup date format
- Issue 876 - Colour code password levels
- Issue 879 - Menu Scroll Bar Visual tweak
- Issue 871 - Change PDF security on the invoices
- Issue 877 - Contract PDF formatting changes
- Issue 872 - Booking Sales Visits with Future Dates

## [v4.0.3] - 2020-05-28
### Fixed
- CKEditor not showing while processing change request
- CKEditor not showing while creating SR from sales order
- Current Activity Report - Pending Reopen not showing as table

## [v4.0.2] - 2020-05-27
### Fixed
- User.php shows wrong length of service value
- CustomerItem.php displayCI permissions should be Technical or Sales
- Added technical permission to contracts pages
- Brought back environment colors!

## [v4.0.1] - 2020-05-26
### Fixed
- Current Activity Report bigger headings
- Current Activity Remember expanded headings
- Pending reopened section not showing in Current Activity Report
- Star for flagging as favourite shows in the wrong place in technical menu

## [v4.0.0] - 2020-05-25
### Changed
- Issue 620 - New side menu design in CNCAPPS

## [v3.14.6] - 2020-05-21
### Fixed
- Wrong content in the emails that get sent out as part of ServiceDeskAutoCompletion.php for Pending Closure
- Some engineers travel missing from Expense Dashboard

## [v3.14.5] - 2020-05-18
### Fixed
- Fixed issue with installation date readonly on RenHosting and RenBroadband
- Fixed issue that prevents to set installation date when checking Direct Debit
- Fixed issue trying to export contacts
- Fixed issue with automatically approved overtime not getting an approved duration value

## [v3.14.4] - 2020-05-13
### Fixed
- Fixed issue with awaiting CNC not being set correctly 

## [v3.14.3] - 2020-05-12
### Fixed
- Fixed issue when trying to send Sales Order reminder
- Fixed issue with Office 365 license matching, changed it to be exact match

## [v3.14.2] - 2020-05-11
### Fixed
- Fixed issue with RenContract installation date input having readonly attribute
- Fixed Customer Review Meeting Documents upload not working
- Fixed issue with customerProblem reason field being smaller than automated_request reason field

## [v3.14.1] - 2020-05-11
### Fixed
- Fixed issue with searching in CustomerItem.php

## [v3.14.0] - 2020-05-08
### Changed
- Issue 833 - Additional pages to lock out contract choices
- Issue 834 - Show SR number on Fixed Page
- Issue 837 - Exclude Operational Tasks from top of SR History pop up
- Issue 839 - 3CX Call Reporting Enhancements
- Issue 841 - OBRS comestics
- Issue 843 - Include Manager Name on Time, Change & Sales requests feedback
- Issue 847 - Moving SRs between teams requires reason
- Issue 845 - O365 Mailbox Export Authentication & Formatting Improvements
- Issue 823 - Customer CRM Page improvements
- Issue 863 - Fixed activity hidden when final activity hidden
### Fixed
- Issue 832 - Review Meeting Booked Meetings not showing as booked
- Issue 835 - Customer Item Renewal Status does not work
- Issue 848 - Contact Validation for site phone number reporting incorrect sites
- Issue 849 - You have open requests emails sometimes listing wrong To: field
- Issue 853 - Overtime & Expenses Tweaks
- Issue 856 - Password Services - One Per Customer not setting zero in database
- Issue 864 - Contract Expiry Date in Customer Profitability Report Tweak

## [v3.13.6] - 2020-05-05
### Fixed
- Fixed issue with "Allow SRs to be logged against this contract" checkbox not working properly

## [v3.13.5] - 2020-05-04
### Fixed
- Fixed issue with 4th May 2020 not being a bank holiday..moved to 8th May 2020

## [v3.13.4] - 2020-05-01
### Fixed
- Fixed issue with saving new contact

## [v3.13.3] - 2020-04-30
### Fixed
- Fixed issue when setting problems to fixed.

## [v3.13.2] - 2020-04-30
### Fixed
- Fixed issue where awaitingCustomer flag in SR is not working correctly

## [v3.13.1] - 2020-04-29
### Fixed
- Fixed issue with trying to set open hours not working as expected
- Fixed issue with resetting awatingCustomer flag

## [v3.13.0] - 2020-04-21
### Added
- Issue 799 - Customer Contract Take Up Matrix
### Changed
- Issue 826 - Force future date when setting SR to Awaiting Customer
- Issue 827 - Automated Asset Export validation of machines not fully checked in
### Fixed
- Issue 828 - Pending Time Request emails to include approval level
- Issue 829 - Offsite Backup Replication Status missing 2020
- Issue 830 - Awaiting CNC status not being reset correctly

## [v3.12.3] - 2020-04-20
### Fixed
- Fix typo in the Current Asset List Extract

## [v3.12.2] - 2020-04-17
### Fixed
- PrepayAdjustment.php Customer Dropdown is numbers, not names.
- Office365StorageReports.php Does not appear to show any results, but I can see data in the table.
- CustomerReviewMeetingDocuments.php. No Review Meeting Users are being shown
- createSalesRequest.php: Picking any customer / template, click send, and you get an error:
### Changed
- RenewalReport.php (and the other pages that have the customer page) we need to tweak the search so that if it doesn't find a match, it then goes back to the database and then searches for all customers, including the referred ones.
- Pending reopen section: add time and date of the email like we have in To Be Logged
- Pending reopen section: Could we have an option to create a new SR if they try, and reopen an SR with something not related.
- Current Mailbox Extract add legend to explain the meaning of the colored rows
- TimeRequestDashboard.php Add a new column to the left of Chargeable Hours called Approval Level
### Removed
- Remove debug info from Dispatch page


## [v3.12.1] - 2020-04-15
### Changed
- When editing Activities, apply the same lockdown of contracts in the dropdown Contract box as we have on the Fixed SR page.
- Automated Asset Export, remove any double spaces from the data. Example is customer 2955, some extra (hidden) spaces in these columns.
### Fixed
- RenewalReport.php typing in a customer name, click search, and you get an error.
- Unable to save this page. https://cncapps.cnc-ltd.co.uk/Customer.php?action=dispEdit&customerID=5711
- CustomerItem.php type in a customer name, Bennett Griffin for example, click search, it gives error.
- CustomerItem.php Clear button does not work.
- See https://cncapps.cnc-ltd.co.uk/sendmailqueue.php in live. Not sure why it's given that error, must relate to the Special Attention status of a customer.
- We appear to have lost the Furlough option from the drop down list in Customer.php?action=dispEdit&customerID=2065
- CurrentActivityReport.php clicking on the circled takes you to the first activity, but it used to take you to the last one. I didn't think we'd changed this? Can it go to last activity please.
### Removed
- Automated Asset Export, remove the isServer column.

## [v3.12.0] - 2020-04-14
### Added
- Issue 762 - 3CX Call Reporting
- Issue 754 - Allow manual running of O365 mailbox report
- Issue 749 - Ability to create scheduled and regular SRs
- Issue 793 - Add Flag as Sent on Sales Quotes generated as esignable
- Issue 802 - Starters & Leavers Report with links
- Issue 782 - O365 Mailbox script to include Active Sync details
- Issue 637 - Double Hard Time Limits
### Changed
- Issue 750 - Set SR to awaiting CNC when Future date has been reached
- Issue 772 - Date of Expense Tweak
- Issue 805 - Automatically mark visible P1 SRs as critical
- Issue 774 - SR Choice of contracts when closing
- Issue 785 - Tidy Current Mailbox Extract Licenses page
- Issue 801 - Sales Request submitted emails to include Initial Link
- Issue 787 - History of SR Pop up
- Issue 806 - Move Support Level column on customer page
- Issue 768 - Approval required for reopening of SRs by customer
- Issue 788 - Toggle higher level passwords
- Issue 794 - Target Special Attention Emails
- Issue 795 - Show Special Attention when logging a new SR
- Issue 761 - Report on CWA Agent and O365 licenses with Meeting Agenda
- Issue 809 - Top align sales requests & change requests
- Issue 979 - Order confirmation email to customer when creating P5
- Issue 784 - Asset export information to include manufacturer warranty
- Issue 811 - Sales Requests Need Complete Date on Approval or Denial
- Issue 511 - Activity Search Examples
- Issue 816 - Service Requests linked to sales order can only be closed as T&M
- Issue 814 - Visual Changes
- Issue 817 - Customer Contracts Listed in Portal Documents
- Issue 812 - Customer search box improvements
- Issue 771 - Allocation of SRs that get reopened
- Issue 796 - Show 3 year contracts on Customer Profitability Report
### Fixed
- Issue 775 - SR Fixed Yesterday Total Value Tweak
- Issue 773 - Office 365 Backup Licenses Not Updating Contracts
- Issue 760 - Feedback on incorrect staff encryption key
- Issue 776 - New SR Start Work Time adjustment tweak
- Issue 815 - Inactive Sites not showing
- Issue 813 - 3CX Call Reporting Number matching & validation


## [v3.11.10] - 2020-04-01
### Fixed
- Fix issue with auto approve overtime
- Show prepay SR count in historic and total charts from customer review meeting

## [v3.11.9] - 2020-03-30
### Added
- Issue 786 - Create new Furlough Contact Support Level

## [v3.11.8] - 2020-03-30
### Changed
- Show any user that has approved overtime/expenses, pending overtime/expenses and has any YTD overtime/expenses in
the expenseDashboard running totals page.

## [v3.11.7] - 2020-03-26
### Fixed
- Expenses/Overtime pending approval list

## [v3.11.6] - 2020-03-25
### Fixed
- Fixed issue with getOvertime DB function failing to handle weekends for engineer travel

## [v3.11.5] - 2020-03-25
### Added
- Option to cancel & delete expenses when creating.
### Changed
- Factor in weekends as not in office time.
### Fixed
- Expenses and Overtime visual / auto processing tweaks.

## [v3.11.4] - 2020-03-13
### Changed
- Solarwinds reporting tweaks

## [v3.11.3] - 2020-03-11
### Changed 
- Reset root cause when SR is reopened
- Change end date of Historic Total SRs chart in Customer Agenda Meeting to match the selected end date
- Change DownloadSignedSalesOrderDocuments task to now be part of the signable hook
- Change how overtime gets presented in the dashboard and how the approved amount is entered
### Fixed
- Fix an issue when cancelling overtime approved prompt it was approving it anyways.

## [v3.11.2] - 2020-03-09
### Added 
- SRCountByPerson internal api endpoint
- SRCountByRootCause internal api endpoint
- SRCountByLocation internal api endpoint
### Fixed
- Fix issue with yearlySicknessThresholdWarning input not showing in the header edit page, preventing it from saving

## [v3.11.1] - 2020-03-09 
### Changed
- Remove explanation min chars if SR is hidden from customer.
- Add "Default Fixed Explanation" for Root Causes.

## [v3.11.0] - 2020-03-06
### Added
- Issue 757 - Notify when quote is signed within Signable
- Issue 756 - SD Dashboard to show active SRs
- Issue 755 - Sickness total notification
### Changed
- Issue 667 - Review Meeting Agenda Enhancements
- Issue 764 - Add numbers to questionnaire graphs
- Issue 748 - Overtime questions
- Issue 763 - Fixed Explanation Rules
### Fixed
- Issue 758 - Customer Renewal Report Total fields formatting issue
- Issue 753 - Project team logging target not showing on user graph

## [v3.10.4] - 2020-03-03
### Added
- Issue 711 - API for 3CX phone system

## [v3.10.3] - 2020-02-04
### Added 
- Add multiple choice questionnaire question

## [v3.10.2] - 2020-01-23
### Changed
- Improved error handling of Office365LicensesExport.
- Allow people to create passwords with a level higher than the one they have.
- Show SLA values for customer table in TeamPerformanceReport
- Raise SR's for failed backup checks with "Path error or No Images"
### Fix
- Fix issue trying to upload files with filenames larger than 100 characters

## [v3.10.1] - 2020-01-22
### Fixed
- Fixed issue with getOvertime db function 

## [v3.10.0] - 2020-01-21
### Changed
- Issue 698 - Office 365 exports - include OneDrive storage
- Issue 732 - Notify on Sickness & allow half days
- Issue 737 - Create SR from SO, make fields required
- Issue 701 - Password Service - default access level
- Issue 730 - Project Update Status - flag when no update
- Issue 742 - Team Performance - update for Small Projects
- Issue 695 - Online order signing improvements.
- Issue 740 - Change 'Open Consultants' Actitivies' to alert Team Lead
- Issue 720 - SR Statistics enhancement
- Issue 719 - Change Request changes
- Issue 731 - O365 Backup Audit Log v2
- Issue 738 - Overtime & Expenses visual change
### Fixed
- Issue 741 - Part numbers only showing a single digit on Goods in
- Issue 743 - Some Direct Debit Items not showing as DD in Renewal Report
- Issue 744 - Moving SRs from Sales to Projects isn't possible

## [v3.9.7] - 2020-01-14
### Fixed
- Fixed issue with AutomatedAssetListExport.php

## [v3.9.6] - 2020-01-08
### Fixed
- Fixed issue with Projects team graphs not showing for people on the Projects Team
### Changed
- Changed permissions to make sure expense dashboard shows to the appropriate users.
- Show Quick Quote Parameters in sales orders when Initial
- Fix issue with manually assigned time not going to the right team
- Fix issue with Activity column not showing the right numbers
- Fix issue with SLA values in customer page not showing as floats.

## [v3.9.5] - 2020-01-06
### Fixed
- Fixed issue with sales value in sales orders having a comma on thousands multiples. 

## [v3.9.4] - 2020-01-03
### Fixed
- Fixed issue with decimals

## [v3.9.3] - 2020-01-03
### Added
- Added Type of sales request to the dashboard
- Added the ability to create signable quotes for all the sales orders except completed
### Changed
- Changed SD Manager to match the new queue naming convention
- Changed Sales Orders actions to have a specific order
- Changed Signable API Message to accommodate to the new team naming convention
### Fixed
- Fixed issue with Contact Validation not validating sites correctly
- Fixed issue with CustomerAnalysisReport not showing the correct values
- Fixed issue with Root Cause showing as a number while displaying an activity
- Fixed issue with column names while running TeamPerformanceUpdate
- Fixed issue with manual time allocation going to the wrong team

## [v3.9.2] - 2020-01-02
### Fixed
- Fixed issue with time being allocated to the wrong team, when the team of the requestor is Small Projects.
- Fixed issue with despatching.
- Fixed issue with elapsed column not showing correctly on Search Service Requests
- Fixed issue with SR Statistics having a wrong number of reopen issues
- Fixed issue with import requests not importing correctly.
### Changed
- Change order of queues in CurrentActivityReport

## [v3.9.1] - 2019-12-31
### Fixed
- Issue with current service requests updated column
- Issue with processing T&M SR
- Formatting DailyReport outstandingIncidents

## [v3.9.0] - 2019-12-30
### Added
- Issue 689 - Solarwinds cloud O365 backup API
- Issue 706 - Create Sales Request - allow attachments
- Issue 625 - Overtime & Expenses approval
- Issue 538 - Reporting for customer SRs / performance analysis
- Issue 712 - Add ability to unlink SR from Sales Order & Project
### Changed
- Issue 703 - New calendar
- Issue 702 - Renewal Report to show users & direct debit
- Issue 713 - Show 1 decimal place on home page Team Performance
- Issue 715 - Create SR from Sales Order formatting improvements
- Issue 710 - Project date sorting
- Issue 716 - 3CX Address Book Export reduce to 500 rows
- Issue 697 - CNC Company changes.

## [v3.8.5] - 2019-12-18
### Fixed
- Fix issue with FetchBitLocker not pulling certain customers because of labtech quirks

## [v3.8.4] - 2019-12-11
### Fixed
- Fixed issue with Asset List export disk size calculations

## [v3.8.3] - 2019-12-11
### Fixed
- if a customer is referred you must not be able to raise an SR for that customer
- CreateRenewalsSalesOrdersManager.php, it doesn't do anything anymore
- CheckPendingCompletionSR.php looks like the fixed date and days since fixed is pulling from initial, not the fixed activity
- Renewals Update - Allow sorting by "Next Invoice Period"

## [v3.8.2] - 2019-12-09
### Fixed
- The Renewal Report PDF has some incorrect values
- Create Renewal Sales Orders tweak
- Add staff appraisal 4 levels question
- Fix staff appraisal issues

## [v3.8.1] - 2019-12-04
### Fixed
- Creating a new Service Request from the new Create Sales order, it's putting in the value $internalNotes.
- Offsite Replication Checks Suspended Until doesn't show the value entered.

## [v3.8.0] - 2019-11-29
### Added 
- Issue 680 - Quick Create Sales Request
- Issue 659 - Record daily replication / backup targets in database
- Issue 677 - Sales Passwords list
- Issue 672 - Office 365 licensing billing improvements
- Issue 681 - Mailbox Export Enhancements
### Changed
- Issue 638 - Allow Multiple dropping of files when uploading documents
- Issue 535 - PrePay statements, alert if hidden charges on the SR
- Issue 666 - System Header & Review Meeting Text Formatting
- Issue 673 - O365 Report on Spare Licenses not saving
- Issue 670 - Mailbox Export script identify Equipment Mailboxes
- Issue 664 - Colour OS Support Dates
- Issue 669 - Asset list extract remove duplicated logged in users names
- Issue 661 - Review Meetings show primary active site
- Issue 668 - Phone number cleansing
- Issue 682 - Item Type Amendments
- Issue 685 - Prevent Creation of Quotes without a signature
- Issue 675 - OBRS suspension logic for passed check
### Fixed
- Issue 663 - Password Service Sorting not working
- Issue 592 - Security Improvements
- Issue 683 - Fixed SR manual completion monitor
- Fixed issue with Office 365 Licenses not saving "Report on spare licences" when unchecked
 
## [v3.7.0] 
### Added
- Issue 654 - Set all contacts to support level None
### Changed
- Issue 559 - Improve OpenCallEmail.php code
- Issue 633 - Set retention rules for log files
- Issue 606 - Sweetcode in URL
- Issue 612 - Show holiday on performance graphs
- Issue 583 - Contact Extraction Improvements
### Fixed
- Issue 635 - Duplication of contracts within the ratio page
### Removed
- Issue 652 - Disable myphpadmin from being used / allowing logins

## [v3.6.3] - 2019-09-23
### Fixed
- Fixed issue with contactValidation.php
- Fixed Seven Dayer Performance seems to be using wrong date

## [v3.6.2] - 2019-09-20
### Fixed
- Fixed issue with Replication Status reopening Backup SR's when it should only reopen/create issues for Replication

## [v3.6.1] - 2019-09-13
### Fixed
- Fixed issue with 'Select Days' in DailyReport.php?action=outstandingIncidents&onScreen=true&dashboard=true&daysAgo=7
- Fixed issue with Unallocated O365 licenses Email
- Fixed issue with process updating sevenDayerPerformanceLog setting the wrong date

## [v3.6.0] - 2019-09-12
### Added
- Allow Monitor / Critical SR at the point of logging
### Changed
- Issue 649 - Offsite Backup Replication Checks Enhancements
- Issue 641 - Office 365 License Export v2
- Issue 488 - Consider all SRs that are P1-P3 as critical once time is over XX hours.
- Issue 644 - Quote Reminder Tweaking
- Issue 627 - SD Manager P5 team filtering
- Issue 646 - 7 dayer targets

## [v3.5.3] - 2019-09-09
### Fixed
- Issue with skip sales order in Activity.php when the SR is not related to a contract

## [v3.5.2] - 2019-09-04
### Fixed
- PrePay SRs not being billed

## [v3.5.1] - 2019-08-15
### Fixed
- Home page team performance for SR quantities don't always appear to have the correct colour.
- Remove quantity target and colors from home page

## [v3.5.0] - 2019-08-14
### Added
- Issue 605 - Sales Quotation & Online Ordering
- Issue 623 - Full asset list export to secure area
### Changed
- Issue 642 - Starter Leaver Management Sorting
- Issue 639 - New CNC Address change
### Fixed
- Issue 630 - PO Status v9
- Issue 634 - Change team targets to either green or red

## [v3.4.6] - 2019-08-05
### Fixed
- SR Pending Closure email shows rootCause placeholder instead of the actual rootCause
- CustomerReviewMeetingDocuments.php, last item is never removed until page refresh

## [v3.4.5] - 2019-07-31
### Fixed
- PrePay Statement Export Not Posting Top Ups

## [v3.4.4] - 2019-07-30
### Fixed
- Customer.php, the link to 'Create Customer Folder' doesn't work because of the relative path.
### Changed
- When editing an activity, the buttons for Parts Used & Sales Requests, shrink the font so that it's the same as the other buttons on that page.

## [v3.4.3] - 2019-07-26
### Changed
- On SDManagerDashboard.php please move the sorting order so that Critical Requests sit between “current open P1 requests” and “oldest updated SRs”.
- Allow "parts used" and "Sales request" from within editing activity page
### Fixed
- StartersAndLeaversReport.php average cost for the Total row
- When editing customer item the CNC Serverguard section, it lists out the 3 virtual machines, but when you go into these items, it does not show that the CNC ServerGuard checkbox is checked
- Email address with an apostrophe caused the automated import process to fail
- Fix issue with Review Meeting agenda documents not generated correctly 

## [v3.4.2] - 2019-07-25
### Added
- Add links below the Add button on Office365Licenses.php
### Changed 
- Change the description of the portal document from O365 Licenses to Current Mailbox List
- On Office365Licenses.php sort on Friendly Name alphabetically ascending.
- Make all mentions to cnc-ltd.co.uk have be actual links with https
### Fixed
- When exporting the license information for customer 6478, Gavin Travica should be red in the export as he has a 51200 mailbox limit but it's not showing as that.

## [v3.4.1] - 2019-07-24
### Fixed
- Running task e:\sites\cncapps\htdocs\call_url.php https://cncapps.cnc-ltd.co.uk/DailyReportCMD.php?action=outstandingIncidents&daysAgo=7 gives error:
  HTTP Error: 500'daysAgo' is not recognized as an internal or external command, operable program or batch file.
- Users are reporting they can't see their own performance graphs on the home page. 
-  Error 500 processing a time request. Fatal error: Uncaught Error: Call to undefined method BUActivity::allocateAdditionalTime() 

## [v3.4.0] - 2019-07-23
### Changed
- Issue 198 - Deliver the website over HTTPS
- Remove ports from urls

## [v3.3.0] - 2019-07-23
### Added
- Issue 601 - Storing Local PC CNC Admin Passwords
- Issue 597 - Allocate time from SD Manager Dashboard
- Issue 520 - Office 365 login and scripting
- Issue 598 - Sales Order quote sending enhancements
### Changed
- Issue 596 - Email import formatting improvements
- Issue 603 - Include Site on SR History Popup
- Issue 600 - Change Request reminders
- Issue 609 - 7 dayer formatting improvements
- Issue 607 - Customer Review Meeting Enhancements
- Issue 611 - Hide passwords for referred customers
### Fixed
- Issue 604 - Contact Validation doesn't run on scheduled task
- Issue 591 - URL too long with To Be Logged
- Issue 595 - Stuck email enhancements
- Issue 608 - Starters And Leavers Report Enhancements
- Issue 590 - PO Status Report v8
- Issue 610 - Service requests fixed yesterday Negative Duration

## [v3.2.1] - 2019-07-01
### Fixed
- Fixed problem with deleting Portal Documents from customer page

## [v3.2.0] - 2019-06-20
### Added
- Issue 558 - Overnight contact validation check
- Issue 527 - Starters & Leavers Report
### Changed
- Issue 587 - Change Bitlocker encryption field lookup
- Issue 576 - Delegate emails copy only to supervisor that raised / authorised them
### Fixed
- Issue 586 - Utility emails going into To Be Logged as No Primary Contact
- Issue 585 - HTTP links in Standard text
- Issue 589 - Unprinted Purchase Orders & P5 with no Sales Orders email tweaks

## [v3.1.6] - 2019-06-11
### Fixed
- Prepay Export not showing customer name

## [v3.1.5] - 2019-06-11
### Fixed
- PO Status not sending emails
- Sales Request process after fixed
- Notes reverting back (not reproducible)
- Several improvements in Customer Meeting Agenda document
- Uploading MS-Word document to Sales Order
- Uploading Portal documents to customer's page
- PrePay export not showing the right logo

## [v3.1.4] - 2019-06-05
### Fixed
- Clicking On print gives error while in Purchase order page
- Cosmetic changes on Review Meeting Agenda Document
- Fixed error while creating a new Customer
- Fixed issue with Sales Order for SSL certificate renewals item type
- Fixed footer row for Display Contract And Numbers Report

## [v3.1.3] - 2019-06-03
### Fixed 
- Email sending issues

## [v3.1.2] - 2019-05-31
### Fixed
- Fix issue downloading Portal Documents
- Fix issue trying to save customer contacts
- Fix issue preventing to edit several fields on RenBroadband
- Fix issue with line arrows in Sales Orders
- Fix issue with Activity search, when searching Activity Type = Initial and Contract T&M
- Fix issue with Backup validations where the item type would determine if the server is excluded or not
### Changed
- Changed fields order in Activity search

## [v3.1.1] - 2019-05-29
### Fixed
- Fixed issue with MySQL functions not having "characteristic" information

## [v3.1.0] - 2019-05-25
### Added
- Issue 519 - Windows 10 version EOL Build notifications
- Issue 487 - Auto add time to SR when fixing and within XX minutes under budget
- Issue 515 - Report showing contacts with support & on the contract
### Changed
- Issue 531 - Create configuration settings for the new Design Environment 
- Issue 524 - Automated Request table field change
- Issue 533 - PO Status Report v7
- Issue 545 - Password level modification
- Issue 560 - Offsite Backup Image Validation Process
- Issue 557 - Change Request Process GUI enhancement
- Issue 550 - Rename 2nd Site links
- Issue 547 - Time Request Denied Process
- Issue 532 - Secondary LDAP server
- Issue 529 - P5 default times when generated from Sales Order
- Issue 564 - SD Manager Dashboard Show Critical SR
- Issue 561 -  Asset List Export - show if disk is encrypted
- Issue 525 - Activity Search page, move the columns of "fix time", "activities" after "Fixed By"
- Issue 568 - Backup & Replication permissions
- Issue 567 - Customer Review Meeting Agenda SR count
### Fixed
- Issue 551 - Contact Audit Log missing support level
- Issue 552 - Contact Position field extension
- Issue 554 - Block 'Work' Button for referred customers
- Issue 526 - Special Attention Emails missing To field
- Issue 566 - Change Request Cancel button doesn't delete activity if it's been autosaved already
- Issue 546 - Prevent zNot in Use root cause
### Removed
- Issue 530 - Remove reference to faxing on generating a sales order

## [v3.0.0] - 2019-05-24
### Changed
- Issue 492 - Upgrade PHP & mySQL to the later versions

## [v2.4.3] - 2019-05-22
### Fixed
- Implementation team targets are not showing correctly for users on the home page

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