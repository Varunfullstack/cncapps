(function () {
    $(function () {
        const autocompleteURL = "/Customer.php?action=searchName";
        $('input[type="text"][customer-search]').each(function () {
            const that = this;
            // we are going to pull options out of attributes
            let defaultOptions = {
                itemsToShow: 40
            }

            const itemsToShowAttr = this.getAttribute('itemsToShow');
            if (itemsToShowAttr) {
                defaultOptions.itemsToShow = 40;
            }
            $(this).autocomplete({
                minLength: 0,
                source: function (request, responseCB) {
                    const data = {};
                    data.term = request.term;
                    $.ajax(
                        autocompleteURL,
                        {
                            method: 'POST',
                            dataType: 'json',
                            data: data
                        }
                    ).then(response => {
                        if (response.length > 40) {
                            response = response.slice(0, 40);
                            response.unshift({
                                id: -1,
                                name: 'Keep typing to filter, there are more results not shown here'
                            });
                        }
                        responseCB(response.map(x => ({label: x.name, value: x.id})));
                    })

                },
                delay: 200,
                select: (event, ui) => {
                    event.preventDefault();
                    event.target.value = ui.item.label;
                    event.ui = ui;
                    const eventToDispatch = new CustomEvent('autocompleteselect', {detail: ui});
                    that.dispatchEvent(eventToDispatch);
                }
            })

            this.addEventListener('focus', function (event) {
                $(event.target).autocomplete("search", $(event.target).val());
            })
        })
    })
})();