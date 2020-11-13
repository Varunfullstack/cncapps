(function () {
    const currentDocument = document.currentScript.ownerDocument;


    class QuestionItem extends HTMLElement {


        constructor() {
            super();

            this.addEventListener('click', e => {
                this.toggleCard();
            })


        }

        render(userData) {
            //   // Fill the respective areas of the card using DOM manipulation APIs
            //   // All of our subComponents elements reside under shadow dom. So we created a this.shadowRoot property
            //   // We use this property to call selectors so that the DOM is searched only under this subtree
            //   this.shadowRoot.querySelector('.card__full-name').innerHTML = userData.name;
            //   this.shadowRoot.querySelector('.card__user-name').innerHTML = userData.username;
            //   this.shadowRoot.querySelector('.card__website').innerHTML = userData.website;
            //   this.shadowRoot.querySelector('.card__address').innerHTML = `<h4>Address</h4>
            // ${userData.address.suite}, <br />
            // ${userData.address.street},<br />
            // ${userData.address.city},<br />
            // Zipcode: ${userData.address.zipcode}`
            const customerStringElement = this.shadowRoot.querySelector('customerString');
            customerStringElement.addEventListener('focus', this.saveCustomerString);
            customerStringElement.addEventListener('blur', this.validateCustomerString);
            customerStringElement.addEventListener('keypress', this.validateCustomerStringOnReturn)

        }

        toggleCard() {
            // let elem = this.shadowRoot.querySelector('.card__hidden-content');
            // let btn = this.shadowRoot.querySelector('.card__details-btn');
            // btn.innerHTML = elem.style.display == 'none' ? 'Less Details' : 'More Details';
            // elem.style.display = elem.style.display == 'none' ? 'block' : 'none';
        }


        connectedCallback() {
            const shadowRoot = this.attachShadow({mode: "open"});

            const template = currentDocument.querySelector('#question-item-template');
            const instance = template.content.cloneNode(true);
            shadowRoot.appendChild(instance);

            const userId = this.getAttribute('user-id');

        }

        saveCustomerString() {
            this.savedCustomerString = this.shadowRoot.querySelector("customerString").value
        }

        validateCustomerString() {
            if (Trim(this.shadowRoot.getElementById("customerString").value) != "") {
                if (this.shadowRoot.getElementById("customerString").value != this.savedCustomerString) {
                    window.open('{urlCustomerPopup}&customerString=' +
                        escape(document.getElementById("customerString").value) +
                        '&parentIDField=customerID' +
                        '&parentDescField=customerString',
                        'customers', 'scrollbars=yes,resizable=no,width=300,height=300,copyhistory=no, menubar=0')
                }
            } else {
                document.searchForm.customerID.value = "";
            }
        }

        validateCustomerStringOnReturn() {
            if (event.keyCode == 13) {
                validateCustomerString();
            }
        }
    }


    customElements.define('question-item', QuestionItem);
})();