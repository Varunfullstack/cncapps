import APICustomerLicenses from "./APICustomerLicenses.js";

export class StreamOneService {
    apiCustomerLicenses;
    key = "StreamOneOrders";
    lastFetchTime = 0;

    constructor() {
        this.apiCustomerLicenses = new APICustomerLicenses();
    }

    fetchAllOrders() {
        return new Promise(async (resolutionFunc, rejectionFunc) => {
            if (this.chekTimeExpire() || !this.hasOrders()) {
                this.clear();

                const res = await this.apiCustomerLicenses.getAllSubscriptions(1);

                if (res.Result == "Success") {
                    const pages = res.BodyText.totalPages;

                    this.appendOrders(res.BodyText.subscriptions);
                    for (let i = 2; i < pages; i++) {
                        const resChild = await this.apiCustomerLicenses.getAllSubscriptions(
                            i
                        );
                        if (resChild.Result == "Success")
                            this.appendOrders(resChild.BodyText.subscriptions);
                    }
                }
                localStorage.setItem("lastFetchTime", Date.now() / 1000);
            }
            resolutionFunc(this.getOrders());
        });
    }

    clear = () => {
        localStorage.removeItem(this.key);
    };

    appendOrders(orders) {
        const lcitems = localStorage.getItem(this.key);
        let items = JSON.parse(lcitems) || [];
        orders = orders.map((order) => order[Object.keys(order)[0]]);
        items = [...items, ...orders];
        localStorage.setItem(this.key, JSON.stringify(items));

    }

    getOrders() {
        const lcitems = localStorage.getItem(this.key);
        return JSON.parse(lcitems) || [];
    }

    hasOrders() {
        const lcitems = localStorage.getItem(this.key);
        const items = JSON.parse(lcitems) || [];
        return items.length > 0;
    }

    getOrdersByEmail(email) {
        //1595593770262 1595593788826
        const orders = this.getOrders();
        if (orders.length > 0)
            return orders.filter((o) => o.endCustomerEmail == email);
    }

    chekTimeExpire() {
        const lasttime = localStorage.getItem("lastFetchTime");
        return Date.now() / 1000 - (lasttime || 0) > 60 * 60;
    }
}

export default StreamOneService;
