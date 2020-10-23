import React, {Component} from 'react'
import SitesList from "./SitesList";


class CustomerSitesComponent extends Component {
    constructor(props) {
        super(props);
    }

    render() {
        const {customerId} = this.props;
        return (
            <div className="tab-pane fade show"
                 id="nav-sites"
                 role="tabpanel"
                 aria-labelledby="nav-sites-tab"
            >
                <SitesList customerId={customerId}/>
            </div>
        );
    }
}

export default CustomerSitesComponent