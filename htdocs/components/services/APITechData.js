class APITechData {
    _baseURL;
    _token;
    _apiHeaders;
    constructor(data)
    {
       this._baseURL='https://partnerapi.tdstreamone.eu/'; //production
       this._baseURL='https://eu-uat-papi.tdmarketplace.net/'; //testing
       this._token=data.access_token;
       this._SOIN=data.SOIN;
       this._signature=data.signature;
    }
    setHeader()
    {
        this._apiHeaders = new Headers();
        myHeaders.append("Content-Type", "application/json");
        myHeaders.append("Authorization", `Bearer ${this._token}`);
        myHeaders.append("SOIN", this._SOIN);
        myHeaders.append("TimeStamp", 1000);
        myHeaders.append("Signature", this._signature);
        myHeaders.append("Accept", 'application/json');
        myHeaders.append("Access-Control-Allow-Origin", '*');

    }

    getProductList(page)
    {
        var requestOptions = {
            method: 'GET',
            headers: this._apiHeaders,
            //body: urlencoded,      
               
          };

        return fetch(`${this._baseURL}catalog/products/${page}`,requestOptions);
    }
}
export default APITechData;