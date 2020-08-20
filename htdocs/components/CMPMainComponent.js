export default class MainComponent extends React.Component{
    isSDManger(user)
    {
        return user.isSDManger;
    }
    redirectPost(url, data) {
        var form = document.createElement('form');
        document.body.appendChild(form);
        form.method = 'post';
        form.action = url;
        for (var name in data) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = data[name];
            form.appendChild(input);
        }
        form.submit();
    }
    openPopup(url)
    {
        window.open(
            url,
            "reason",
            "scrollbars=yes,resizable=yes,height=550,width=500,copyhistory=no, menubar=0"
          ); 
    }
}