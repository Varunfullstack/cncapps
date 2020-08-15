/**
 *
 * @param {object} o
 * @param {path} p
 */
export function get(o, p) {
  return p.split(".").reduce((a, v) => a[v], o);
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
    value = value === null ? "" : value;
    value.replace(value, "\n");
    value.replace(value, "\r");
    return value;
  };
  if (items.length > 0) {
    if (header.length === 0) header = Object.keys(items[0]);
    let csv = items.map((row) =>
      header
        .map((fieldName) => JSON.stringify(row[fieldName], replacer))
        .join(",")
    );
    csv.unshift(header.join(","));
    csv = csv.join("\r\n");
    const file = new Blob([csv], { type: "text/plain;charset=utf-8" });
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
export function distinct(array,propertyName)
{
  const d=(value,index,self)=>{
    return self.map(a=>a[propertyName]).indexOf(value[propertyName])===index;
  }
  return array.filter(d);
}
export const Colors={
  AMBER : '#FFF5B3',
  RED : '#F8A5B6',
  GREEN : '#BDF8BA',    
  BLUE : '#b2daff',    
  PURPLE : '#dcbdff',
  ORANGE : '#FFE6AB',
}