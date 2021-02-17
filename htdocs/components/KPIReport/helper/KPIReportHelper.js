import {groupBy} from "../../utils/utils";
import moment from "moment";

export class KPIReportHelper {
    getWeeks(data, property) {
        let gdata = data.map((d) => {
            const dt = moment(d.date);
            return {value: d[property], date: d.date, week: dt.format('WYYYY')};
        });
        gdata = groupBy(gdata, "week").map((g, i) => {
            g.week = g.items[0].date;
            g.value = g.items.reduce(
                (prev, current) => prev + parseInt(current.value),
                0
            );
            return g;
        });

        return gdata;
    }

    getMonths(data, property) {
        let gdata = data.map((d) => {
            const dt = moment(d.date);
            return {
                value: d[property],
                date: d.date,
                month: dt.format("MMM YYYY"),
            };
        });

        gdata = groupBy(gdata, "month").map((g, i) => {
            g.month = g.groupName;
            g.value = g.items.reduce(
                (prev, current) => prev + parseInt(current.value),
                0
            );
            return g;
        });

        return gdata;
    }

}