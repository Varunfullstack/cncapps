/**
 *
 * @param {object} o
 * @param {path} p
 */
import moment from "moment";

export function get(o, p) {
    return p.split(".").reduce((a, v) => a[v], o) || '';
}

export function getServiceRequestWorkTitle(serviceRequest) {
    if (serviceRequest.isBeingWorkedOn) {
        return "Request being worked on currently";
    }
    if (serviceRequest.status == "I") {
        return "Request not started yet";
    }
    return "Work on this request";
}

export function getWorkIconClassName(serviceRequest) {

    const commonClasses = "fa-play fa-2x pointer";
    if (serviceRequest.isBeingWorkedOn) {
        return `being-worked-on fad ${commonClasses}`;
    }
    if (serviceRequest.status == "I") {
        return `not-yet-started fad ${commonClasses}`
    }
    return `start-work fal ${commonClasses}`;
}

export function sort(array, path, order = "asc") {
    return array.sort((a, b) => {
        if (
            get(a, path) > get(b, path) ||
            get(a, path) == null ||
            get(a, path) == undefined
        )
            return order == "asc" ? 1 : -1;
        if (
            get(a, path) < get(b, path) ||
            get(b, path) == null ||
            get(a, path) == undefined
        )
            return order == "asc" ? -1 : 1;
        else return 0;
    });
}

export function makeid(length = 5) {
    var result = "";
    var characters =
        "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    var charactersLength = characters.length;
    for (var i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
}

export function exportCSV(items, fileName, header = []) {
    const replacer = (key, value) => {
        // specify how you want to handle null values here
        value = value == null ? "" : value;
        if (value != null) {
            value.toString().replace(value, "\n");
            value.toString().replace(value, "\r");
        }
        return value;
    };
    if (items.length > 0) {
        if (header.length == 0) header = Object.keys(items[0]);
        let csv = items.map((row) =>
            header
                .map((fieldName) => JSON.stringify(row[fieldName], replacer))
                .join(",")
        );
        csv.unshift(header.join(","));
        csv = csv.join("\r\n");
        const file = new Blob([csv], {type: "text/plain;charset=utf-8"});
        if (window.navigator.msSaveOrOpenBlob)
            // IE10+
            window.navigator.msSaveOrOpenBlob(file, filename);
        else {
            const a = document.createElement("a"),
                url = URL.createObjectURL(file);
            a.href = url;
            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            setTimeout(function () {
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            }, 0);
        }
    }

}

export function distinct(array, propertyName) {
    const d = (value, index, self) => {
        return self.map(a => a[propertyName]).indexOf(value[propertyName]) == index;
    }
    return array.filter(d);
}

export const Colors = {
    AMBER: '#FFF5B3',
    RED: '#F8A5B6',
    GREEN: '#BDF8BA',
    BLUE: '#b2daff',
    PURPLE: '#dcbdff',
    ORANGE: '#FFE6AB',
}
export const params = new URLSearchParams(window.location.search);

export function pick(obj, values) {
    const temp = {...obj};
    let newObj = {};
    for (let i = 0; i < values.length; i++)
        newObj[values[i]] = temp[values[i]];
    return newObj;
}

export const SRQueues = [
    {id: 1, name: "Helpdesk", teamID: 1, code: "HD"},
    {id: 2, name: "Escalations", teamID: 2, code: "ES"},
    {id: 3, name: "Small Projects", teamID: 4, code: "SP"},
    {id: 5, name: "Projects", teamID: 5, code: "P"},
    {id: 4, name: "Sales", teamID: 7, code: "S"},
]
export const TeamType = {
    Helpdesk: 1,
    Escalations: 2,
    Small_Projects: 4,
    Projects: 5,
    Directors: 6,
    Sales: 7
}

/**
 *
 * @param {array} items
 * @param {string} propertyName
 */
export function groupBy(items, propertyName) {
    return items.reduce(function (prev, current) {
        // get group index and group by renewalType
        const index = prev
            ? prev.findIndex((g) => g.groupName == current[propertyName])
            : -1;
        if ((prev && prev.length == 0) || index == -1) {
            const obj = {
                groupName: current[propertyName],
                items: [current],
            };
            prev.push(obj);
        } else if (index >= 0) {
            prev[index].items.push(current);
        }
        return prev;
    }, []);
}

/**
 *
 * @param {string} length
 * @param {int of pixels} length
 */
export function padEnd(value, length, char) {
    // space length 3.05615234375
    var canvas = document.createElement('canvas');
    canvas.style.display = "none";
    var ctx = canvas.getContext("2d");
    ctx.font = "11px Arial";
    var width = ctx.measureText(value).width;

    const spaceCount = (length - width) / 3.05615234375;
    for (let i = 0; i < spaceCount; i++)
        value += char;
    canvas.remove();

    return value;
}

/**
 *
 * @param {array} items
 * @param {string} propertyName
 */
export function maxLength(items, propertyName) {
    const newItems = items.map(item => item[propertyName]);
    var maxLength = 0;
    if (newItems.length > 0) {
        var canvas = document.createElement('canvas');
        canvas.style.display = "none";
        var ctx = canvas.getContext("2d");
        ctx.font = "11px Arial";
        for (let i = 0; i < newItems.length; i++) {
            var width = ctx.measureText(newItems[i]).width;
            if (width > maxLength)
                maxLength = width;

        }

        canvas.remove();
    }
    return maxLength;
}

export const Chars = {
    WhiteSpace: "&nbsp;"
}
export const isEmptyTime = (time) => {
    return time == "" || time == null || time == "00:00" || time == "";
}
export const MYSQL_DATE_FORMAT = 'YYYY-MM-DD';

export function getBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function () {
            resolve(reader.result);
        };
        reader.onerror = function (error) {
            reject(error);
        };
    })
}

export function getContactElementName(contact) {
    let name = `${contact.firstName} ${contact.lastName} ${contact.position ? `(${contact.position})` : ''}`;
    let suffix = "";
    if (contact.supportLevel == 'main') {
        suffix = " *";
    } else if (contact.supportLevel == 'supervisor') {
        suffix = ' - Supervisor';
    } else if (contact.supportLevel == 'delegate') {
        suffix = ' - Delegate';
    }
    return `${name}${suffix}`
}

export function poundFormat(number) {
    if (number === undefined || number === null) {
        return null;
    }
    return `£${(+number).toFixed(2)}`
}

export function dateFormatExcludeNull(date, fromFormat = "YYYY-MM-DD", toFormat = "DD/MM/YYYY") {
    if (date === undefined || date === null) {
        return "";
    }
    return moment(date, fromFormat).format(toFormat);
}
export function equal(obj1,obj2){
    return JSON.stringify(obj1)===JSON.stringify(obj2);
}