import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIKPIReport extends APIMain { 
    getSRFixed(startDate,endDate,customerID){
        return this.get(`${ApiUrls.KPIReport}SRFixed&from=${startDate}&to=${endDate}&customerID=${customerID}`);
    }

    getPriorityRaised(startDate,endDate,customerID){
        return this.get(`${ApiUrls.KPIReport}priorityRaised&from=${startDate}&to=${endDate}&customerID=${customerID}`);
    }

    getServiceRequestsRaisedByContract(startDate, endDate, customerID){
        return this.get(`${ApiUrls.KPIReport}serviceRequestsRaisedByContract&from=${startDate}&to=${endDate}&customerID=${customerID}`);
    }

    getQuotationConversion(startDate,endDate,customerID){
        return this.get(`${ApiUrls.KPIReport}quotationConversion&from=${startDate}&to=${endDate}&customerID=${customerID}`);
    }

    getDailyStats(from,to,customerID){
        return this.get(`${ApiUrls.KPIReport}dailyStats&from=${from}&to=${to}&customerID=${customerID}`);
    }
    
    getDailySource(from,to,customerID){
        return this.get(`${ApiUrls.KPIReport}dailySource&from=${from}&to=${to}&customerID=${customerID}`);
    }
    getEngineerMonthlyBilling(startDate,endDate){
        return this.get(`${ApiUrls.KPIReport}engineerMonthlyBilling&from=${startDate}&to=${endDate}`).then(data=>{
            data.map(d=>{
                d.date=d.inh_date_printed_yearmonth.substring(0,4)+'-'+d.inh_date_printed_yearmonth.substring(4,6)+'-01';
                d.engineer=d.inl_desc.replace(" - Consultancy","");
                return d;
            });
            return data;
        });
    }
    getDailyContact(from,to){
        return this.get(`${ApiUrls.KPIReport}dailyContact&from=${from}&to=${to}`);
    }
    getGrossProfit(filter){
        //from,to,customerID,stockCat
        let url=`${ApiUrls.KPIReport}grossProfit&from=${filter.from}&to=${filter.to}`;

        if(filter.customerID)
            url += "&customerID="+filter.customerID;

        if(filter.stockCat)
            url += "&stockCat="+filter.stockCat;

        return this.get(url);
    }
}