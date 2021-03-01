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
}
