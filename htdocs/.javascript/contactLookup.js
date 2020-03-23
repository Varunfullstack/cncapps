$(function () {
    let contactPopupURL = null;
    $(document).on('change', 'input[contact-lookup]', null, () => {
        const value = event.target.value;
        if (!value || !value.trim()) {
            return;
        }
        const $elm = $(event.target);
        const data = $elm.data();
        contactPopupURL = new URL('/Contact.php', window.location.origin);

        contactPopupURL.searchParams.append('action', 'contactPopup');
        contactPopupURL.searchParams.append('htmlFmt', 'popup');

        if (!data.result) {
            throw new Error('Result element must be provided');
        }

        contactPopupURL.searchParams.append('parentIDField', data.result);
        contactPopupURL.searchParams.append('parentDescField', event.target.id);

        if (data.contactId) {
            contactPopupURL.searchParams.append('contactID', data.contactId);
        }

        if (!data.supplierId && !data.customerId) {
            throw new Error('Customer ID or Supplier ID must be provided');
        }
        if (data.customerId) {
            contactPopupURL.searchParams.append('customerID', data.customerId);
        }

        if (data.supplierId) {
            contactPopupURL.searchParams.append('supplierID', data.supplierId);
        }

        if (data.siteNo) {
            contactPopupURL.searchParams.append('siteNo', data.siteNo);
        }

        contactPopupURL.searchParams.append('contactName', value);

        window.open(
            contactPopupURL.toString(),
            'contact',
            'scrollbars=yes,resizable=yes,height=700,width=500,copyhistory=no, menubar=0'
        )
    })
});