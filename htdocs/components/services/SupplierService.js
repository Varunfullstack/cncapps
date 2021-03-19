const BASE_URL = '/Supplier.php';


export class SupplierService {
    static getSuppliersSummaryData() {
        return fetch(`${BASE_URL}?action=getSuppliers`).then(response => response.json()).then((res) => {
            if (res.status !== 'ok') {
                throw new Error('Failed to retrieve Suppliers');
            }
            return res.data;
        })
    }

    static async createSupplier(newSupplier) {
        return fetch(
            `${BASE_URL}?action=createSupplier`,
            {
                method: 'POST',
                body: JSON.stringify(newSupplier)
            }
        )
            .then(response => response.json())
            .then((res) => {
                if (res.status !== 'ok') {
                    throw new Error(`Failed to create Supplier: ${res.message ?? 'System Error'}`);
                }
            })
    }

    static async reactivateContact(contact, supplier) {
        return fetch(
            `${BASE_URL}?action=reactivateSupplierContact`,
            {
                method: 'POST',
                body: JSON.stringify({supplierId: supplier.id, supplierContactId: contact.id})
            }
        )
            .then(response => response.json())
            .then((res) => {
                if (res.status !== 'ok') {
                    throw new Error(`Failed to reactivate Contact: ${res.message ?? 'System Error'}`);
                }
            })
    }

    static async archiveContact(contact, supplier) {
        console.log(contact, supplier);

        return fetch(
            `${BASE_URL}?action=archiveSupplierContact`,
            {
                method: 'POST',
                body: JSON.stringify({supplierId: supplier.id, supplierContactId: contact.id})
            }
        )
            .then(response => response.json())
            .then((res) => {
                if (res.status !== 'ok') {
                    throw new Error(`Failed to archive Contact: ${res.message ?? 'System Error'}`);
                }
            })
    }

    static async getSupplierById(supplierId) {
        const response = await fetch(`${BASE_URL}?action=getSupplierData&supplierId=${supplierId}`);
        const jsonResponse = await response.json();
        if (!jsonResponse || jsonResponse.status !== 'ok') {
            throw new Error(`Failed to fetch supplier: ${jsonResponse.message ?? 'System Error'}`);
        }
        return jsonResponse.data;
    }

    static async updateSupplierContact(supplier, editingContact) {
        const response = await fetch(`${BASE_URL}?action=updateSupplierContact`,
            {
                method: 'POST',
                body: JSON.stringify({supplierId: supplier.id, ...editingContact})
            });
        const jsonResponse = await response.json();
        if (!jsonResponse || jsonResponse.status !== 'ok') {
            throw new Error(`Failed to update supplier contact: ${jsonResponse.message ?? 'System Error'}`);
        }
    }

    static async createSupplierContact(supplier, editingContact) {
        const response = await fetch(`${BASE_URL}?action=createSupplierContact`,
            {
                method: 'POST',
                body: JSON.stringify({supplierId: supplier.id, ...editingContact})
            });
        const jsonResponse = await response.json();
        if (!jsonResponse || jsonResponse.status !== 'ok') {
            throw new Error(`Failed to create supplier contact: ${jsonResponse.message ?? 'System Error'}`);
        }
    }
}
