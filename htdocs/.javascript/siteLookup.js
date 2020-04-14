$(function () {
    let contactPopupURL = null;
    $(document).on('change', 'input[site-lookup]', null, () => {
        const value = event.target.value;
        if (!value || !value.trim()) {
            return;
        }
        const $elm = $(event.target);
        const data = $elm.data();
        contactPopupURL = new URL('/Site.php', window.location.origin);

        contactPopupURL.searchParams.append('action', 'popupSite');
        contactPopupURL.searchParams.append('htmlFmt', 'popup');

        if (!data.result) {
            throw new Error('Result element must be provided');
        }

        contactPopupURL.searchParams.append('parentIDField', data.result);
        contactPopupURL.searchParams.append('parentDescField', event.target.id);

        if (data.customerID) {
            contactPopupURL.searchParams.append('customerID', data.customerID);
        }
        contactPopupURL.searchParams.append('siteDesc', value);

        window.open(
            contactPopupURL.toString(),
            'site',
            'scrollbars=yes,resizable=yes,height=700,width=500,copyhistory=no, menubar=0'
        )
    })
});