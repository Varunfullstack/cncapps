/**
 *
 * @param {object} o
 * @param {path} p
 */
import moment from "moment";
import APIKeywordMatchingIgnores from "../KeywordMatchingIgnoresComponent/services/APIKeywordMatchingIgnores";

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
    if(path)
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
    else 
    return array.sort((a, b) => {
        if (
            a > b ||
            a == null ||
            a == undefined
        )
            return order == "asc" ? 1 : -1;
        if (
            a < b ||
            b == null ||
            a == undefined
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
export const getTeamCode = (teamId) => {
    const team = SRQueues.find(t => t.teamID == teamId);
    if (team)
        return team.code;
    else return '';
}
export const TeamType = {
    Helpdesk: 1,
    Escalations: 2,
    SmallProjects: 4,
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


export function entityMapToArray(allIds, idEntityMap) {
    return allIds.map(id => idEntityMap[id]);
}

export function fileToBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result);
        reader.onerror = error => reject(error);
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
    return new Intl.NumberFormat('en-GB', {
        maximumFractionDigits: 2,
        style: 'currency',
        currency: 'GBP'
    }).format(number);
    // return `??${(+number).toFixed(2)}`
}

export function dateFormatExcludeNull(date, fromFormat = "YYYY-MM-DD", toFormat = "DD/MM/YYYY") {
    if (date === undefined || date === null) {
        return "";
    }
    return moment(date, fromFormat).format(toFormat);
}

export function equal(obj1, obj2) {
    return JSON.stringify(obj1) === JSON.stringify(obj2);
}

//-------------------start similarity
let words = [];

async function loadReservedWords() {
    if (words.length == 0) {
        const api = new APIKeywordMatchingIgnores();
        let res = await api.getWords();
        words = res.map((w) => w.word);
    }
}

function removeReservedWords(s) {
    if (s) {
        for (let i = 0; i < words.length; i++)
            s = s.replaceAll(" " + words[i] + " ", " ");
    }
    return s;
}

export async function similarity(s1, s2, useDefaultDictionary = true) {
    if (s1.length == 0)
        return 0;
    if (useDefaultDictionary) {
        await loadReservedWords();

    }
    s1 = removeReservedWords(s1 ? s1.toLowerCase() : null);
    s2 = removeReservedWords(s2 ? s2.toLowerCase() : null);

    // get s1 words
    const s1Words = stripHtml(s1).split(" ");
    const s2Words = stripHtml(s2).split(" ");
    let totalSim = 0;
    for (let i = 0; i < s1Words.length; i++) {
        let max = 0;
        for (let j = 0; j < s2Words.length; j++) {
            const sim = similarityWord(s1Words[i], s2Words[j]);

            if (sim > max) max = sim;
        }
        totalSim += parseFloat(max);

    }
    return totalSim / s1Words.length;
}

//-------------end similarity
function stripHtml(html) {
    let tmp = document.createElement("DIV");
    tmp.innerHTML = html;
    let txt = tmp.textContent || tmp.innerText || "";
    //txt.replace("\n","");
    return txt;
}

function similarityWord(s1, s2) {
    var longer = s1;
    var shorter = s2;
    if (s1.length < s2.length) {
        longer = s2;
        shorter = s1;
    }
    var longerLength = longer.length;
    if (longerLength == 0) {
        return 1.0;
    }
    return (longerLength - editDistance(longer, shorter)) / parseFloat(longerLength);
}

function editDistance(s1, s2) {
    s1 = s1.toLowerCase();
    s2 = s2.toLowerCase();

    var costs = [];
    for (var i = 0; i <= s1.length; i++) {
        var lastValue = i;
        for (var j = 0; j <= s2.length; j++) {
            if (i == 0)
                costs[j] = j;
            else {
                if (j > 0) {
                    var newValue = costs[j - 1];
                    if (s1.charAt(i - 1) != s2.charAt(j - 1))
                        newValue = Math.min(Math.min(newValue, lastValue),
                            costs[j]) + 1;
                    costs[j - 1] = lastValue;
                    lastValue = newValue;
                }
            }
        }
        if (i > 0)
            costs[s2.length] = lastValue;
    }
    return costs[s2.length];
}

export function bigger(values) {
    if (values.length > 0) {
        let max = values[0];
        for (let i = 0; i < values.length; i++) {
            if (values[i] > max) max = values[i];
        }
        return max;
    }
    return 0;
}
export function isNumeric(str) {
    if(typeof str == "number") return true;
    if (typeof str != "string" ) return false // we only process strings!  
    if(str.startsWith("??"))
      str=str.slice(1);
    return !isNaN(str) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
           !isNaN(parseFloat(str)) // ...and ensure strings of whitespace fail
  }
  export function replaceQuotes(str){      
    str= str.replaceAll('&#39;', '\'');        
    str= str.replaceAll('&#039;', '\'');
    str= str.replaceAll('&quot;', '\"');   
    str= str.replaceAll('&amp;', '\&');   
    return str;
  }
  export function getFileExt(fileName="")
  {
    const indx=fileName.lastIndexOf(".");    
    return fileName.substr(indx+1);
  }
  /**
   * @param fileSize in bytes
   */
  export function getFileSize(fileSize)
  {
    if(fileSize<1024)
    return `${fileSize} B`;
    else if(fileSize>=1024 && fileSize<1024*1024)
    return `${(fileSize/1024).toFixed(2)} KB`;
    else if(fileSize>=1024*1024 && fileSize<1024*1024*1024)
    return `${(fileSize/(1024*1024)).toFixed(2)} MB`;
  }


export function getUpdatedColumns(source,target)
  {
    if(source==null)
      return target;
    let updated={};
    for(var i in target){
      //console.log(typeof target[i]);
      if(typeof target[i]!="object" && target[i]!=source[i])
        updated[i]=target[i];
      else if(typeof target[i]=="object"&&source[i]!=undefined&&
      Object.keys(getUpdatedColumns(target[i],source[i])).length>0)
        updated[i]=target[i];
    }
    return updated;
  }
export const Pages={
    Customer:1,
    Sites:2,
    PortalDocuments:3
}
export const ColorsBig = [
    "rgb(242,234,94)", "rgb(62,31,132)", "rgb(153,112,127)", "rgb(81,34,165)", "rgb(133,76,93)", "rgb(114,8,41)", "rgb(235,179,149)", "rgb(168,97,103)", "rgb(47,147,51)", "rgb(11,130,44)", "rgb(190,208,244)", "rgb(187,59,48)", "rgb(209,137,215)", "rgb(169,87,182)", "rgb(48,11,22)", "rgb(252,229,185)", "rgb(153,140,250)", "rgb(252,218,103)", "rgb(80,90,87)", "rgb(166,231,210)", "rgb(221,157,11)", "rgb(152,39,248)", "rgb(59,126,25)", "rgb(165,219,24)", "rgb(26,90,131)", "rgb(198,64,21)", "rgb(204,240,107)", "rgb(120,8,87)", "rgb(127,102,18)", "rgb(33,34,123)", "rgb(251,30,23)", "rgb(68,122,155)", "rgb(164,175,166)", "rgb(37,3,160)", "rgb(85,16,223)", "rgb(232,251,163)", "rgb(133,12,217)", "rgb(107,113,86)", "rgb(213,246,36)", "rgb(225,145,134)", "rgb(91,209,241)", "rgb(56,100,154)", "rgb(183,94,70)", "rgb(162,70,55)", "rgb(240,82,232)"
];
export function sumBy(arr,key){
    if(!arr||arr.length==0)
    return 0;
    return arr.reduce((a,b)=>a+b[key],0)

}